import { IPhaseDataHistorical } from "../../models/Interfaces";

interface GameStatusResponse {
  gameID: number;
  countryID: number;
  variantID: number;
  potType: string;
  turn: number;
  phase: string;
  gameOver: string;
  pressType: string;
  phases: IPhaseDataHistorical[];
  standoffs: string[];
  occupiedFrom: string[];
  // need to get votes from gameOverview because status
  // is only updated once per phase
  // votes: string | null;
  orderStatus: string;
  status: string;
}

export default GameStatusResponse;
