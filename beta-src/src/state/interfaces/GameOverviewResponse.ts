import { MemberData } from "../../interfaces";
import IntegerRange from "../../types/IntegerRange";

export type Member = {
  country: string;
  countryID: number;
  id: number;
  online: boolean;
  userID: number;
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
  playerTypes: string;
  pot: IntegerRange<35, 666>;
  potType: string;
  processStatus: string;
  processTime: number | null | undefined;
  season: string;
  startTime: number;
  turn: number;
  user?: {
    member: MemberData;
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
}

export default GameOverviewResponse;
