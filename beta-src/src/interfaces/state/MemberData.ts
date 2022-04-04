import IntegerRange from "../../types/IntegerRange";

export interface MemberData {
  bet: IntegerRange<5, 96>;
  country: string;
  countryID: number;
  excusedMissedTurns: IntegerRange<0, 5>;
  missedPhases: number;
  newMessagesFrom: number[];
  online: boolean;
  orderStatus: unknown;
  status: string;
  supplyCenterNo: number;
  timeLoggedIn: number;
  unitNo: number;
  userID: number;
  username: string;
  votes: unknown;
}
