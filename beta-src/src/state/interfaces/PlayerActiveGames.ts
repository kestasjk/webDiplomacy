import { OrderStatus } from "../../interfaces";

export interface PlayerGame {
  gameID: number;
  countryID: number;
  orderStatus: string; // e.g. "Ready,Completed". Not converted to OrderStatus
  newMessagesFrom: string[];
  supplyCenterNo: number;
  name: string;
  turn: number;
  phase: string;
  processTime: number;
  phaseMinutes: number;
}

type PlayerActiveGames = PlayerGame[];

export default PlayerActiveGames;
