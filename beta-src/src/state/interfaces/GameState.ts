import GameOverviewResponse from "./GameOverviewResponse";
import GameStatusResponse from "./GameStatusResponse";

export type ApiStatus = "idle" | "loading" | "succeeded" | "failed";

export interface GameState {
  apiStatus: ApiStatus;
  error: string | null | undefined;
  overview: GameOverviewResponse;
  status: GameStatusResponse;
}
