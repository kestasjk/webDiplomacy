import {
  getContext,
  invalidateContext,
  loadedVersions,
  noteFileVersions,
} from "../utils/api/gameSource";
import { store } from "../state/store";

/*
Instant notification from the server to this board when something about the game changes. PHP publishes events
to Redis and a small node.js server (sse-server/server.js) forwards them to clients as server-sent events.

This board reads a game from its public JSON files and the player's context (utils/api/gameSource.ts), and the
events it acts on say which of those changed:
- a "files" event on the game's files channel (private-game<id>-files) lists the files which were rewritten, with
  their new versions
- a "message" event on the player's own channel says a message for them has arrived
The older "processed" and "set-vote" events on the game's channel are acted on too, for when the change was made
by a site running older code, which publishes no files event.
*/

function sseDebugLog(msg: string) {
  // eslint-disable-next-line no-console
  console.log(`[SSE] ${msg}`);
}

// The single event source shared by all event listeners
let eventSource: EventSource;
let gameID = 0; // Set when the first channel is subscribed to
let started = false;
let nextReconnectTime = new Date(); // If nothing is heard from the server by this time the connection is restarted
nextReconnectTime.setSeconds(nextReconnectTime.getSeconds() + 30);
let isEventSourceReconnecting = false; // Set while a reconnection is in progress, so the watchdog doesn't stack reconnections
// The single reconnect watchdog timer; kept module-level so reconnects replace it rather than adding another
let reconnectWatchdogTimer: ReturnType<typeof setInterval> | undefined;
type EventCallback = (...args: unknown[]) => void;
const eventCallbacks: { [key: string]: EventCallback[] } = {};

// Tokens for the SSE server are accepted for a day
const TOKEN_MAX_AGE_SECONDS = 23 * 60 * 60;

// The token is "hash_timestamp", the timestamp being the server's time when it was made
function tokenTime(auth: string): number {
  return parseInt(auth.split("_")[1], 10);
}

function call(event: string, ...args: unknown[]) {
  if (eventCallbacks[event]) {
    eventCallbacks[event].forEach((callback) => callback(...args));
  }
}

function resetReconnectTime() {
  const newReconnectTime = new Date();
  newReconnectTime.setSeconds(newReconnectTime.getSeconds() + 30);
  nextReconnectTime = newReconnectTime;
}

// Events published while the connection was down are lost (the server keeps no history). When the connection
// opens the SSE server compares what this board has with the game and sends the events which were missed, then a
// catchup event. This refetches everything instead, for when it can't tell what was missed (a resync request) or
// no catchup event came.
function refetchAfterGap(reason: string) {
  sseDebugLog(`Refetching game state after ${reason}`);
  invalidateContext();
  call("overview", reason);
  call("message");
}

function start() {
  let hasConnectedBefore = false;
  // Set if a connection fails before opening, as the token may have been refused
  let tokenRefused = false;
  let catchupTimer: ReturnType<typeof setTimeout> | undefined;

  // The player's context carries a token for the SSE server: for their country's channel if they are playing, or
  // else for the game's channel alone. The context is only fetched again for a token if the one it has is old
  // (a tab left open) or was refused.
  const getConnection = async (): Promise<{
    auth: string;
    countryID: number;
    channelGameID: number;
  }> => {
    let context = await getContext(gameID.toString());
    const tooOld =
      context.sseAuth &&
      Math.abs(Date.now() / 1000 - tokenTime(context.sseAuth)) >
        TOKEN_MAX_AGE_SECONDS;
    if (context.sseAuth && (tooOld || tokenRefused)) {
      invalidateContext();
      context = await getContext(gameID.toString());
    }
    if (!context.sseAuth) throw new Error("This server has no SSE server");
    return {
      auth: context.sseAuth,
      countryID: context.member ? context.member.countryID : 0,
      channelGameID: context.game.realGameID,
    };
  };

  const reconnect = () => {
    getConnection()
      .then(({ auth, countryID, channelGameID }) => {
        const isReconnection = hasConnectedBefore;
        let hasOpened = false;
        // What this board has: the versions of the game's files, and the time up to which it has the player's messages
        const since = store.getState().game.messages.time || tokenTime(auth);
        eventSource = new EventSource(
          `/events?channelList=private-game${channelGameID},private-game${channelGameID}-files,private-game${channelGameID}-country${countryID}` +
            `&auth=${auth}&have=${encodeURIComponent(
              loadedVersions(),
            )}&since=${since}`,
        );
        eventSource.onopen = () => {
          sseDebugLog("Connected to SSE server");
          hasOpened = true;
          tokenRefused = false;
          // The connection is up again, so let the watchdog fire for the next timeout,
          // and give the server the full timeout period before that can happen:
          isEventSourceReconnecting = false;
          resetReconnectTime();
          call("connected");
          call("pusher:subscription_succeeded");
          // The SSE server should now send the events this board missed, then a catchup event. The first
          // connection comes just after the board loaded the game, so without one only reconnections refetch.
          if (catchupTimer) clearTimeout(catchupTimer);
          catchupTimer = setTimeout(() => {
            if (isReconnection) refetchAfterGap("reconnecting");
          }, 5000);
          hasConnectedBefore = true;
        };
        eventSource.onerror = (e) => {
          sseDebugLog("Connection error or closed. Will attempt reconnection.");
          eventSource.close();
          if (!hasOpened) tokenRefused = true;
          nextReconnectTime = new Date(); // Trigger a reconnection
          call("pusher:subscription_error", e);
        };
        // Periodically check if we need to reconnect. Each reconnection replaces the
        // previous watchdog so long-lived tabs don't accumulate timers:
        if (reconnectWatchdogTimer) clearInterval(reconnectWatchdogTimer);
        reconnectWatchdogTimer = setInterval(() => {
          if (!isEventSourceReconnecting && new Date() >= nextReconnectTime) {
            sseDebugLog("Nothing received from server in time. Reconnecting");
            eventSource.close();
            isEventSourceReconnecting = true; // Ensure this timer won't keep reconnecting
            reconnect();
            call("pusher:subscription_error", "SSE timeout, reconnecting");
          }
        }, 17000);
        eventSource.onmessage = (e) => {
          try {
            const data = JSON.parse(e.data);
            resetReconnectTime();

            if (data.channel === "resync") {
              // The SSE server can't tell what was missed: a key it checks wasn't set, or it lost its Redis connection
              refetchAfterGap("a resync request");
              return;
            }
            if (data.channel === "catchup") {
              // Whatever was missed has been sent as files and message events
              if (catchupTimer) clearTimeout(catchupTimer);
              return;
            }
            if (data.channel === "ping") {
              call("pusher:pong");
              return;
            }

            const event = JSON.parse(data.message);
            if (event.event === "files") {
              const changed = noteFileVersions(event.data);
              sseDebugLog(`Files changed: ${changed.join(", ") || "none new"}`);
              if (changed.some((file) => file !== "messages")) {
                call("overview", "files");
              }
              if (changed.includes("messages")) call("message");
            } else if (event.event === "message") {
              sseDebugLog("New game message received");
              call("message");
            } else if (
              event.event === "overview" &&
              (event.data === "processed" || event.data === "set-vote")
            ) {
              // Published by all versions of the site. A site running older code publishes no files event, and
              // doesn't rewrite the files; asking for the player's context again has the server rewrite any that
              // are out of date, and tells this board their versions.
              sseDebugLog(`Game ${event.data}`);
              invalidateContext();
              call("overview", event.data);
            }
          } catch {
            sseDebugLog(`Raw message: ${e.data}`);
          }
        };
      })
      .catch((error) => {
        // eslint-disable-next-line no-console
        console.error("Failed to start the SSE connection:", error);
        // Allow the watchdog to attempt another reconnection:
        isEventSourceReconnecting = false;
        call("pusher:subscription_error", error);
      });
  };

  reconnect();
}

const client = {
  // For connection-wide events, like connected
  connection: {
    bind: (event: string, callback: EventCallback) => {
      eventCallbacks[event] = eventCallbacks[event] || [];
      eventCallbacks[event].push(callback);
    },
  },
  // The channel name only says which game; one connection carries the game's channel and the player's own
  subscribe: (channelName: string) => {
    sseDebugLog(`Subscribing to channel: ${channelName}`);
    const newGameID = parseInt(
      channelName.split("-")[1].replace("game", ""),
      10,
    );
    if (!started && newGameID > 0) {
      gameID = newGameID;
      started = true;
      start();
    }
    return {
      // overview, message, pusher:pong, pusher:subscription_succeeded, pusher:subscription_error
      bind: (event: string, callback: EventCallback) => {
        eventCallbacks[event] = eventCallbacks[event] || [];
        eventCallbacks[event].push(callback);
      },
    };
  },
};

export default client;
