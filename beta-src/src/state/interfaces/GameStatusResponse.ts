interface GameStatusResponse {
  gameID: number;
  countryID: number;
  variantID: number;
  potType: string;
  turn: number;
  phase: string;
  gameOver: string;
  pressType: string;
  phases: string[];
  standoffs: string[];
  occupiedFrom: string[];
  votes: string | null;
  orderStatus: string;
  status: string;
}

export default GameStatusResponse;
