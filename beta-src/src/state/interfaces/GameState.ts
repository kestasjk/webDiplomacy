import GameDataResponse from "./GameData";
import GameOverviewResponse from "./GameOverviewResponse";
import GameStatusResponse from "./GameStatusResponse";

export type ApiStatus = "idle" | "loading" | "succeeded" | "failed";

export interface GameState {
  apiStatus: ApiStatus;
  data: GameDataResponse;
  error: string | null | undefined;
  overview: GameOverviewResponse;
  status: GameStatusResponse;
}
