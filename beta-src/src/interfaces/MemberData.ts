import Country from "../enums/Country";
import IntegerRange from "../types/IntegerRange";
import Vote from "../types/Vote";

export enum userStatus {
  PLAYING = "Playing",
  LEFT = "Left",
}

export interface MemberData {
  bet: IntegerRange<5, 96>;
  country: Country;
  countryID: number;
  excusedMissedTurns: number;
  missedPhases: number;
  newMessagesFrom: number[];
  online: boolean;
  orderStatus: {
    updated: boolean;
  };
  status: userStatus;
  supplyCenterNo: number;
  timeLoggedIn: Date;
  unitNo: number;
  userID: number;
  username: string;
  votes: Vote;
}
