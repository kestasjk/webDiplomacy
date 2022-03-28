import GameDataResponse from "./GameDataResponse";
import GameErrorResponse from "./GameErrorResponse";
import GameOverviewResponse from "./GameOverviewResponse";
import GameStatusResponse from "./GameStatusResponse";

export type ApiStatus = "idle" | "loading" | "succeeded" | "failed";

export interface GameState {
  apiStatus: ApiStatus;
  data: GameDataResponse;
  error: GameErrorResponse;
  overview: GameOverviewResponse;
  status: GameStatusResponse;
}
