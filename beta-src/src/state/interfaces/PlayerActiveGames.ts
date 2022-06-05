import { OrderStatus } from "../../interfaces";

export interface PlayerGame {
  gameID: number;
  countryID: number;
  orderStatus: string; // e.g. "Ready,Completed". Not converted to OrderStatus
  name: string;
  turn: number;
  phase: string;
}

type PlayerActiveGames = PlayerGame[];

export default PlayerActiveGames;
