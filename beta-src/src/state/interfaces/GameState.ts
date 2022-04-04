import GameOverviewResponse from "./GameOverviewResponse";
import GameStatusResponse from "./GameStatusResponse";
import OrderState from "./OrderState";

export type ApiStatus = "idle" | "loading" | "succeeded" | "failed";

export interface GameState {
  apiStatus: ApiStatus;
  error: string | null | undefined;
  order: OrderState;
  overview: GameOverviewResponse;
  status: GameStatusResponse;
}
