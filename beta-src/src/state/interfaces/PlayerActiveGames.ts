import { OrderStatus } from "../../interfaces";

export interface PlayerGame {
  gameID: number;
  countryID: number;
  orderStatus: string; // e.g. "Ready,Completed". Not converted to OrderStatus
  newMessagesFrom: string[];
  unitNo: number;
  name: string;
  turn: number;
  phase: string;
  processTime: number;
  phaseMinutes: number;
  alternatives: string;
}

type PlayerActiveGames = PlayerGame[];

export default PlayerActiveGames;
