import { BuildCommand, FlyoutCommand } from "./GameCommands";
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
import ViewedPhaseState from "./ViewedPhaseState";
import { LegalOrders } from "../../utils/state/gameApiSlice/extraReducers/fetchGameData/precomputeLegalOrders";

export type ApiStatus = "idle" | "loading" | "succeeded" | "failed";

// FIXME: nasty to have dependencies to component state in here
type UnitState = { [key: string]: UIState };

export interface GameState {
  activity: UserActivity;
  apiStatus: ApiStatus;
  data: GameDataResponse; // Directly from API
  error: GameErrorResponse;
  maps: GameStateMaps; // Computed as a function of GameDataResponse
  overview: GameOverviewResponse; // Directly from API
  ordersMeta: OrdersMeta; // Stateful, tracks user order input
  ownUnits: string[]; // Computed as a function of GameDataResponse
  territoriesMeta: TerritoriesMeta;
  viewedPhaseState: ViewedPhaseState; // Stateful, tracks what phase the user views.
  status: GameStatusResponse; // Directly from API
  messages: GameMessages;
  outstandingMessageRequests: number; // Stateful, restricts querying api for messages
  order: OrderState;
  legalOrders: LegalOrders; // Computed as a function of GameOverviewResponse, GameDataResponse, GameStateMaps
}
