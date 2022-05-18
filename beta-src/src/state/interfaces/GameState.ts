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
import { Unit } from "../../utils/map/getUnits";
import UIState from "../../enums/UIState";

export type ApiStatus = "idle" | "loading" | "succeeded" | "failed";

type UnitState = { [key: string]: UIState };

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
  units: Unit[];
  unitState: UnitState; // map from unit ID to icon state
  territoriesMeta: TerritoriesMeta;
  commands: GameCommands;
  status: GameStatusResponse;
  messages: GameMessages;
}
