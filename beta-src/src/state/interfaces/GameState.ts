import GameCommands from "./GameCommands";
import GameDataResponse from "./GameDataResponse";
import GameErrorResponse from "./GameErrorResponse";
import GameOverviewResponse from "./GameOverviewResponse";
import GameStatusResponse from "./GameStatusResponse";
import OrderState from "./OrderState";
import OrdersMeta from "./SavedOrders";
import TerritoriesMeta from "./TerritoriesState";

export type ApiStatus = "idle" | "loading" | "succeeded" | "failed";

export interface GameState {
  apiStatus: ApiStatus;
  data: GameDataResponse;
  error: GameErrorResponse;
  overview: GameOverviewResponse;
  order: OrderState;
  ordersMeta: OrdersMeta;
  ownUnits: string[];
  territoriesMeta: TerritoriesMeta;
  commands: GameCommands;
  status: GameStatusResponse;
}
