import BoardClass from "../../models/BoardClass";
import GameCommands from "./GameCommands";
import GameDataResponse from "./GameDataResponse";
import GameErrorResponse from "./GameErrorResponse";
import GameOverviewResponse from "./GameOverviewResponse";
import GameStateMaps from "./GameStateMaps";
import GameStatusResponse from "./GameStatusResponse";
import GameMessages from "./GameMessages";
import OrderState from "./OrderState";
import OrdersMeta from "./SavedOrders";
import TerritoriesMeta from "./TerritoriesState";
import UserActivity from "./UserActivity";
import { IUnit } from "../../models/Interfaces";

export type ApiStatus = "idle" | "loading" | "succeeded" | "failed";

export interface GameState {
  activity: UserActivity;
  apiStatus: ApiStatus;
  board: BoardClass | undefined;
  data: GameDataResponse;
  error: GameErrorResponse;
  maps: GameStateMaps;
  overview: GameOverviewResponse;
  order: OrderState;
  ordersMeta: OrdersMeta;
  ownUnits: string[];
  units: IUnit[];
  territoriesMeta: TerritoriesMeta;
  commands: GameCommands;
  status: GameStatusResponse;
  messages: GameMessages;
}
