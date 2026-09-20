/**
 * Where this board gets a game from: the game's public JSON files, which the web server serves as static files,
 * and the game/playercontext API route for what depends on who is looking (doc/gamedata/02-spec.md). Nothing else
 * is read from the server.
 *
 * The rest of the board was written against the older API routes (game/overview, game/data, game/status and
 * game/getmessages), and its state and components still use the shapes those routes returned. The adapt*
 * functions below build those shapes, including their string IDs and "Yes"/"No" flags, so that this file is the
 * only place which knows both formats.
 */
import axios from "axios";
import { MemberData, OrderStatus } from "../../interfaces";
import GameDataResponse from "../../state/interfaces/GameDataResponse";
import GameMessages, {
  GameMessage,
  MessageStatus,
} from "../../state/interfaces/GameMessages";
import GameOverviewResponse from "../../state/interfaces/GameOverviewResponse";
import GameStatusResponse from "../../state/interfaces/GameStatusResponse";
import PlayerActiveGames from "../../state/interfaces/PlayerActiveGames";
import {
  IOrderData,
  IPhaseDataHistorical,
  IProvinceStatus,
  ITerritory,
  IUnit,
} from "../../models/Interfaces";

/* The new formats */

export type GameFileName = "game" | "status" | "history" | "messages";
export const gameFileNames: GameFileName[] = [
  "game",
  "status",
  "history",
  "messages",
];

interface FileRef {
  url: string | null;
  version: string | null;
}

export interface PlayerContext {
  schema: number;
  game: {
    gameID: number;
    realGameID: number;
    variantID: number;
    name: string;
    turn: number;
    phase: string;
    gameOver: string;
    processTime: number | null;
    processStatus: string;
    pressType: string;
    potType: string;
    phaseMinutes: number;
  };
  files: { variant: FileRef } & { [file in GameFileName]: FileRef };
  viewer: {
    userID: number;
    isMember: boolean;
    isModerator: boolean;
    isDirector: boolean;
    isSandboxOwner: boolean;
    isTempBanned?: boolean;
  };
  member: null | {
    countryID: number;
    memberID: number;
    status: string;
    orderStatus: string[];
    votes: string[];
    missedPhases: number;
    excusedMissedTurns: number;
    bet: number;
    newMessagesFrom: number[];
    mutedCountryIDs: number[];
    ordersPending: boolean;
    lastMessageTime: number | null;
  };
  sseAuth: string | null;
  orders: null | {
    context: string;
    contextKey: string;
    orders: {
      id: number;
      countryID: number;
      unitID: number | null;
      unitType: string | null;
      terrID: number | null;
      type: string;
      toTerrID: number | null;
      fromTerrID: number | null;
      viaConvoy: boolean;
    }[];
  };
  messages: null | { messages: NewMessage[]; cursor: number };
}

interface NewMessage {
  id: number;
  turn: number;
  phase: string;
  timeSent: number;
  fromCountryID: number;
  toCountryID: number;
  message: string;
}

interface VariantFile {
  variantID: number;
  name: string;
  fullName: string;
  countries: { countryID: number; name: string }[];
  supplyCenterCount: number;
  supplyCenterTarget: number;
  territories: {
    id: number;
    name: string;
    type: string;
    supply: boolean;
    homeCountryID: number | null;
    coast: string;
    coastParentID: number;
    borders: { id: number; army: boolean; fleet: boolean }[];
    coastalBorders: { id: number; army: boolean; fleet: boolean }[];
  }[];
}

interface GameFile {
  version: string;
  name: string;
  variant: { id: number; name: string; url: string; version: string };
  turn: number;
  phase: string;
  turnText: string;
  gameOver: string;
  processTime: number | null;
  processStatus: string;
  pauseTimeRemaining: number | null;
  phaseMinutes: number;
  phaseMinutesRB: number;
  startTime: number | null;
  pot: number;
  potType: string;
  minimumBet: number | null;
  pressType: string;
  anon: boolean;
  drawType: string;
  missingPlayerPolicy: string;
  playerTypes: string;
  excusedMissedTurns: number;
  isSandbox: boolean;
  identitiesHidden: boolean;
  members: {
    countryID: number;
    country: string | null;
    status: string;
    supplyCenterNo: number;
    unitNo: number;
    bet: number;
    pointsWon: number | null;
    user: null | { userID: number; username: string };
    missedPhases: number | null;
    excusedMissedTurns: number | null;
    lastSeen: number | string;
  }[];
  units: {
    id: number;
    countryID: number;
    type: string;
    terrID: number;
    retreating: boolean;
  }[];
  territories: {
    terrID: number;
    ownerCountryID: number | null;
    unitID: number | null;
    retreatingUnitID: number | null;
    standoff: boolean;
    occupiedFromTerrID: number | null;
  }[];
}

interface StatusFile {
  version: string;
  members: {
    countryID: number;
    orderStatus: string[] | null;
    votes: string[];
  }[];
}

interface HistoryFile {
  version: string;
  phases: {
    turn: number;
    phase: string;
    units: {
      countryID: number;
      type: string;
      terrID: number;
      retreating: boolean;
    }[];
    centers: { terrID: number; countryID: number }[];
    orders: {
      countryID: number;
      terrID: number | null;
      unitType: string | null;
      type: string;
      toTerrID: number | null;
      fromTerrID: number | null;
      viaConvoy: boolean;
      success: boolean;
      dislodged: boolean;
    }[];
  }[];
}

interface MessagesFile {
  version: string;
  messages: NewMessage[];
}

/* Loading */

// The site root, which the URLs of the files are relative to; this board is served from a folder within it
const siteRoot = "../";

const http = axios.create();

/**
 * The versions of the game's files, as last heard from either game/playercontext or a files event from the SSE
 * server. A file is fetched again when its version here differs from the version that was loaded.
 */
const versions: { [file: string]: string } = {};
let fileURLs: PlayerContext["files"] | null = null;
const loadedFiles: { [file: string]: { version: string; contents: unknown } } =
  {};

let context: PlayerContext | null = null;
let contextGameID = "";
let contextStale = true;
let contextRequest: Promise<PlayerContext> | null = null;
// The first context is asked for with the player's messages as well as their orders, so that loading a game is
// one request. These are those messages, until loadMessages() has used them.
let firstMessages: PlayerContext["messages"] = null;

/**
 * The versions this board has loaded, for telling the SSE server when connecting, so that it can say which
 * files changed while this board wasn't listening.
 */
export function loadedVersions(): string {
  return gameFileNames
    .filter((file) => loadedFiles[file])
    .map((file) => `${file}:${loadedFiles[file].version}`)
    .join(",");
}

/**
 * Note the file versions in a files event from the SSE server.
 * @returns the files whose versions are news to this board
 */
export function noteFileVersions(changed: {
  [file: string]: string;
}): GameFileName[] {
  const news: GameFileName[] = [];
  gameFileNames.forEach((file) => {
    if (changed[file] && versions[file] !== changed[file]) {
      versions[file] = changed[file];
      news.push(file);
    }
  });
  // A new game.json means the game was processed or otherwise changed, so the player's orders, their order status
  // and the token for saving orders will have changed with it
  if (news.includes("game")) contextStale = true;
  return news;
}

/**
 * Anything this player does which changes their own votes or order status makes the context held here out of date.
 */
export function invalidateContext(): void {
  contextStale = true;
}

function sbToken(): string | null {
  return new URLSearchParams(window.location.search).get("sbToken");
}

async function requestContext(
  gameID: string,
  params: { [key: string]: string },
): Promise<PlayerContext> {
  const query = new URLSearchParams({ gameID, ...params });
  const token = sbToken();
  if (token) query.set("sbToken", token);
  const { data } = await http.get(
    `${siteRoot}api.php?route=game/playercontext&${query.toString()}`,
    { timeout: 60000 },
  );
  const newContext = data as PlayerContext;
  fileURLs = newContext.files;
  gameFileNames.forEach((file) => {
    const { version } = newContext.files[file];
    if (version) versions[file] = version;
  });
  return newContext;
}

/**
 * The player's context with their orders, fetched again only if something has happened which changes it. Several
 * callers asking at once share one request.
 */
export async function getContext(gameID: string): Promise<PlayerContext> {
  if (context && !contextStale && contextGameID === gameID) return context;
  if (!contextRequest) {
    const isFirst = context === null;
    contextRequest = requestContext(
      gameID,
      isFirst ? { orders: "1", messages: "1" } : { orders: "1" },
    )
      .then((newContext) => {
        if (isFirst) firstMessages = newContext.messages;
        context = newContext;
        contextGameID = gameID;
        contextStale = false;
        return newContext;
      })
      .finally(() => {
        contextRequest = null;
      });
  }
  return contextRequest;
}

async function getFile<T>(file: GameFileName | "variant"): Promise<T> {
  if (!fileURLs) throw new Error("No game context loaded");
  const ref = fileURLs[file];
  const version = (file === "variant" ? ref.version : versions[file]) || "";
  if (!ref.url) throw new Error(`The game has no ${file} file`);

  const loaded = loadedFiles[file];
  if (loaded && loaded.version === version) return loaded.contents as T;

  const { data } = await http.get(`${siteRoot}${ref.url}?v=${version}`, {
    timeout: 60000,
  });
  // The file says which version it is, which is newer than the one asked for if it was rewritten in between
  const contents = data as { version?: string };
  loadedFiles[file] = { version: contents.version || version, contents };
  if (file !== "variant" && contents.version) versions[file] = contents.version;
  return data as T;
}

/* Adapting to the shapes the board's state uses */

const yesNo = (value: boolean): string => (value ? "Yes" : "No");
const idString = (id: number | null): string | null =>
  id === null ? null : id.toString();

function orderStatusFlags(orderStatus: string[] | null): OrderStatus {
  return {
    Completed: !!orderStatus?.includes("Completed"),
    None: !!orderStatus?.includes("None"),
    Ready: !!orderStatus?.includes("Ready"),
    Saved: !!orderStatus?.includes("Saved"),
    Hidden: orderStatus === null,
  };
}

const scoringNames = {
  "Winner-takes-all": "Draw-Size Scoring",
  "Points-per-supply-center": "Survivors-Win Scoring",
  "Sum-of-squares": "Sum-of-Squares Scoring",
  Unranked: "Unranked",
};

/**
 * The summary of a game's settings which the game panel shows, as PHP's Game::getAlternatives() gives it
 */
function alternatives(game: GameFile): string {
  const parts = [game.variant.name];
  const pressTypes = {
    NoPress: "No messaging",
    RulebookPress: "Rulebook press",
    PublicPressOnly: "Public messaging only",
  };
  if (pressTypes[game.pressType]) parts.push(pressTypes[game.pressType]);
  if (game.playerTypes === "Mixed") parts.push("Fill with Bots");
  if (game.playerTypes === "MemberVsBots") parts.push("Bot Game");
  if (game.anon) parts.push("Anonymous players");
  parts.push(scoringNames[game.potType] || game.potType);
  if (game.drawType === "draw-votes-hidden") parts.push("Hidden draw votes");
  if (game.missingPlayerPolicy === "Wait") parts.push("Wait for orders");
  if (game.isSandbox) parts.push("Sandbox game");
  return parts.join(", ");
}

export async function loadOverview(
  gameID: string,
): Promise<GameOverviewResponse> {
  const ctx = await getContext(gameID);
  const [game, status, variant] = await Promise.all([
    getFile<GameFile>("game"),
    getFile<StatusFile>("status"),
    getFile<VariantFile>("variant"),
  ]);

  const members: MemberData[] = game.members.map((member) => {
    const memberStatus = status.members.find(
      (m) => m.countryID === member.countryID,
    );
    return {
      bet: member.bet,
      country: member.country || "",
      countryID: member.countryID,
      excusedMissedTurns: member.excusedMissedTurns || 0,
      missedPhases: member.missedPhases || 0,
      newMessagesFrom: [],
      online: false,
      orderStatus: orderStatusFlags(
        memberStatus ? memberStatus.orderStatus : null,
      ),
      pointsWon: member.pointsWon,
      status: member.status,
      supplyCenterNo: member.supplyCenterNo,
      timeLoggedIn: typeof member.lastSeen === "number" ? member.lastSeen : 0,
      unitNo: member.unitNo,
      userID: member.user ? member.user.userID : -1,
      username: member.user ? member.user.username : "",
      votes: memberStatus ? memberStatus.votes : [],
    } as MemberData;
  });

  let user: GameOverviewResponse["user"];
  if (ctx.member) {
    const own = ctx.member;
    const publicMember = members.find((m) => m.countryID === own.countryID);
    user = {
      member: {
        ...(publicMember as MemberData),
        countryID: own.countryID,
        status: own.status,
        bet: own.bet,
        excusedMissedTurns: own.excusedMissedTurns,
        missedPhases: own.missedPhases,
        newMessagesFrom: own.newMessagesFrom,
        orderStatus: orderStatusFlags(own.orderStatus),
        votes: own.votes,
        userID: ctx.viewer.userID,
      } as MemberData,
      sseAuth: ctx.sseAuth,
    };
  }

  const [season, year] = game.turnText.split(",").map((part) => part.trim());

  return {
    alternatives: alternatives(game),
    anon: yesNo(game.anon),
    drawType: game.drawType,
    excusedMissedTurns: game.excusedMissedTurns,
    gameID: ctx.game.gameID,
    gameOver: game.gameOver,
    members,
    minimumBet: game.minimumBet || 0,
    name: game.name,
    pauseTimeRemaining: game.pauseTimeRemaining,
    phase: game.phase,
    phaseMinutes: game.phaseMinutes,
    phaseMinutesRB: game.phaseMinutesRB,
    playerTypes: game.playerTypes,
    pot: game.pot,
    potType: game.potType,
    pressType: game.pressType,
    processStatus: game.processStatus,
    processTime: game.processTime,
    season,
    startTime: game.startTime || 0,
    turn: game.turn,
    user,
    variant: {
      id: variant.variantID,
      name: variant.name,
      fullName: variant.fullName,
      countries: variant.countries.map((country) => country.name),
      supplyCenterCount: variant.supplyCenterCount,
      supplyCenterTarget: variant.supplyCenterTarget,
    },
    variantID: game.variant.id,
    year: parseInt(year, 10) || 1901,
    isTempBanned: !!ctx.viewer.isTempBanned,
  } as unknown as GameOverviewResponse;
}

export async function loadData(gameID: string): Promise<GameDataResponse> {
  const ctx = await getContext(gameID);
  const [game, variant] = await Promise.all([
    getFile<GameFile>("game"),
    getFile<VariantFile>("variant"),
  ]);

  const territories: { [id: string]: ITerritory } = {};
  variant.territories.forEach((territory) => {
    const border = (b: { id: number; army: boolean; fleet: boolean }) => ({
      id: b.id.toString(),
      a: b.army,
      f: b.fleet,
    });
    territories[territory.id] = {
      id: territory.id.toString(),
      name: territory.name,
      type: territory.type,
      supply: yesNo(territory.supply),
      countryID: (territory.homeCountryID || 0).toString(),
      coast: territory.coast,
      coastParentID: territory.coastParentID.toString(),
      Borders: territory.borders.map(border),
      CoastalBorders: territory.coastalBorders.map(border),
    };
  });

  const units: { [id: string]: IUnit } = {};
  game.units.forEach((unit) => {
    units[unit.id] = {
      id: unit.id.toString(),
      countryID: unit.countryID.toString(),
      type: unit.type,
      terrID: unit.terrID.toString(),
    };
  });

  const territoryStatuses: IProvinceStatus[] = game.territories.map(
    (territory) => ({
      id: territory.terrID.toString(),
      ownerCountryID: idString(territory.ownerCountryID),
      unitID: idString(territory.unitID),
      standoff: territory.standoff,
      occupiedFromTerrID: idString(territory.occupiedFromTerrID),
    }),
  );

  const data: GameDataResponse["data"] = {
    territories,
    units,
    territoryStatuses,
    turn: game.turn,
    phase: game.phase,
    isSandboxMode: game.isSandbox,
  };

  // The orders belong to the phase the context was read in; if the game has moved on since, they will come with
  // the context that the new game.json makes this board ask for
  if (
    ctx.orders &&
    ctx.game.turn === game.turn &&
    ctx.game.phase === game.phase
  ) {
    data.contextVars = {
      context: JSON.parse(ctx.orders.context),
      contextKey: ctx.orders.contextKey,
      contextString: ctx.orders.context,
    };
    data.currentOrders = ctx.orders.orders.map((order) => {
      // The order entry code can't show a half-entered order, which the server may hold if saving one failed
      // part-way, so such an order is shown as a hold
      let { type } = order;
      let { toTerrID, fromTerrID } = order;
      if (
        ((type === "Move" || type === "Support hold") && toTerrID === null) ||
        ((type === "Support move" || type === "Convoy") &&
          (toTerrID === null || fromTerrID === null))
      ) {
        type = "Hold";
        toTerrID = null;
        fromTerrID = null;
      }
      return {
        id: order.id.toString(),
        unitID: idString(order.unitID),
        type,
        toTerrID: idString(toTerrID),
        fromTerrID: idString(fromTerrID),
        viaConvoy: yesNo(order.viaConvoy),
        countryID: order.countryID,
        error: null,
        status: "",
      } as unknown as IOrderData;
    });
  }

  return { msg: "", referenceCode: "", success: true, data };
}

export async function loadStatus(gameID: string): Promise<GameStatusResponse> {
  const ctx = await getContext(gameID);
  const [game, history] = await Promise.all([
    getFile<GameFile>("game"),
    getFile<HistoryFile>("history"),
  ]);

  // While the game is running the last phase is the one being played, with no orders
  const phases: IPhaseDataHistorical[] = history.phases.map((phase) => ({
    turn: phase.turn,
    phase: phase.phase,
    centers: phase.centers,
    units: phase.units.map((unit) => ({
      unitType: unit.type,
      retreating: yesNo(unit.retreating),
      terrID: unit.terrID,
      countryID: unit.countryID,
    })),
    orders: phase.orders.map((order) => ({
      countryID: order.countryID,
      terrID: order.terrID || 0,
      unitType: order.unitType || "",
      type: order.type,
      toTerrID: order.toTerrID || 0,
      fromTerrID: order.fromTerrID || 0,
      viaConvoy: yesNo(order.viaConvoy),
      success: yesNo(order.success),
      dislodged: yesNo(order.dislodged),
      turn: phase.turn,
      phase: phase.phase,
    })),
  }));

  return {
    gameID: ctx.game.gameID,
    countryID: ctx.member ? ctx.member.countryID : null,
    variantID: game.variant.id,
    potType: game.potType,
    turn: game.turn,
    phase: game.phase,
    gameOver: game.gameOver,
    pressType: game.pressType,
    phases,
    standoffs: [],
    occupiedFrom: [],
    orderStatus: ctx.member ? ctx.member.orderStatus.join(",") : "",
    status: ctx.member ? ctx.member.status : "",
  } as unknown as GameStatusResponse;
}

function adaptMessage(message: NewMessage): GameMessage {
  return {
    fromCountryID: message.fromCountryID,
    toCountryID: message.toCountryID,
    message: message.message,
    timeSent: message.timeSent,
    turn: message.turn,
    phaseMarker: message.phase,
    status: MessageStatus.READ,
  };
}

/**
 * The messages to everyone, from the game's messages.json, and the player's own messages sent since sinceTime, from
 * their context. The time in the result is what to give as sinceTime next time.
 */
export async function loadMessages(
  gameID: string,
  sinceTime: string | undefined,
): Promise<GameMessages> {
  let ctx: PlayerContext;
  if ((!sinceTime || sinceTime === "0") && firstMessages) {
    // Loading the game: the messages came with the first context
    ctx = { ...(await getContext(gameID)), messages: firstMessages };
    firstMessages = null;
  } else {
    const params: { [key: string]: string } = { messages: "1" };
    if (sinceTime && sinceTime !== "0") params.messagesSince = sinceTime;
    ctx = await requestContext(gameID, params);
  }

  let publicMessages: NewMessage[] = [];
  if (ctx.files.messages.url) {
    publicMessages = (await getFile<MessagesFile>("messages")).messages;
  }
  const ownMessages = ctx.messages ? ctx.messages.messages : [];

  return {
    messages: publicMessages.concat(ownMessages).map(adaptMessage),
    newMessagesFrom: ctx.member ? ctx.member.newMessagesFrom : [],
    time: ctx.messages ? ctx.messages.cursor : Math.floor(Date.now() / 1000),
  } as GameMessages;
}

export async function loadActiveGames(): Promise<{ games: PlayerActiveGames }> {
  const { data } = await http.get(
    `${siteRoot}api.php?route=game/playercontext`,
    { timeout: 60000 },
  );
  return {
    games: data.games.map((game) => ({
      gameID: game.gameID,
      countryID: game.countryID,
      orderStatus: game.orderStatus.join(","),
      pressType: game.pressType,
      newMessagesFrom: game.newMessagesFrom,
      unitNo: 0,
      name: game.name,
      turn: game.turn,
      phase: game.phase,
      processTime: game.processTime,
      phaseMinutes: 0,
      alternatives: "",
    })),
  };
}
