import { MemberData } from "../../interfaces";
import IntegerRange from "../../types/IntegerRange";

export type Member = {
  country: string;
  countryID: number;
  id: number;
  online: boolean;
  userID: number;
};

// Whether someone not in the game may join it, or take over a country in civil disorder (game/playercontext's join)
export type JoinOptions = {
  canJoin: boolean;
  cantJoinReason: string | null;
  bet: number;
  needsInviteCode: boolean;
  minimumReliabilityRating: number;
  civilDisorderCountryIDs: number[];
};

// Whether a member may leave the game before it starts (game/playercontext's leave)
export type LeaveOptions = {
  canLeave: boolean;
  cantLeaveReason: string | null;
};

interface GameOverviewResponse {
  alternatives: string;
  anon: string;
  drawType: string;
  excusedMissedTurns: IntegerRange<0, 5>;
  gameID: number;
  gameOver: string;
  members: MemberData[];
  minimumBet: number;
  name: string;
  pauseTimeRemaining: number | null | undefined;
  phase: string;
  phaseMinutes: number;
  phaseMinutesRB: number;
  playerTypes: string;
  pot: IntegerRange<35, 666>;
  potType: string;
  pressType: string;
  processStatus: string;
  processTime: number | null | undefined;
  season: string;
  startTime: number;
  turn: number;
  user?: {
    member: MemberData;
    // Token for connecting to the SSE server as this member; null if there is no SSE server
    sseAuth?: string | null;
  };
  variant: {
    id: number;
    mapID: number;
    name: string;
    fullName: string;
    description: string;
    author: string;
    countries: string[];
    variantClasses: {
      drawMap: string;
      adjudicatorPreGame: string;
    };
    codeVersion: number | null | undefined;
    cacheVersion: number | null | undefined;
    coastParentIDByChildID: {
      [key: string]: number;
    };
    coastChildIDsByParentID: {
      [key: string]: number[];
    };
    terrIDByName: string | null | undefined;
    supplyCenterCount: number;
    supplyCenterTarget: number;
  };
  variantID: number;
  year: number;
  isTempBanned: boolean;
  // Null when there is nothing to offer: see the PHP PlayerContext::join() and leave()
  join: JoinOptions | null;
  leave: LeaveOptions | null;
}

export default GameOverviewResponse;
