import { createSlice, createAsyncThunk, current } from "@reduxjs/toolkit";
import { v4 as uuidv4 } from "uuid";
import ApiRoute from "../../enums/ApiRoute";
import { getGameApiRequest, submitOrders } from "../../utils/api";
import GameDataResponse from "../interfaces/GameDataResponse";
import GameErrorResponse from "../interfaces/GameErrorResponse";
import GameOverviewResponse from "../interfaces/GameOverviewResponse";
import GameCommands, {
  GameCommand,
  GameContainerType,
} from "../interfaces/GameCommands";
import { ApiStatus } from "../interfaces/GameState";
import GameStatusResponse from "../interfaces/GameStatusResponse";
import { RootState } from "../store";
import initialState from "./initial-state";
import { IOrderData } from "../../models/Interfaces";
import Territory from "../../enums/map/variants/classic/Territory";
import OrdersMeta from "../interfaces/SavedOrders";
import TerritoryMap from "../../data/map/variants/classic/TerritoryMap";
import countryMap from "../../data/map/variants/classic/CountryMap";
import OrderState from "../interfaces/OrderState";
import GameCommandType from "../../types/state/GameCommandType";

export const fetchGameData = createAsyncThunk(
  ApiRoute.GAME_DATA,
  async (queryParams: { countryID?: string; gameID: string }) => {
    const { data } = await getGameApiRequest(ApiRoute.GAME_DATA, queryParams);
    return data as GameDataResponse;
  },
);

export const fetchGameOverview = createAsyncThunk(
  ApiRoute.GAME_OVERVIEW,
  async (queryParams: { gameID: string }) => {
    const {
      data: { data },
    } = await getGameApiRequest(ApiRoute.GAME_OVERVIEW, queryParams);
    return data as GameOverviewResponse;
  },
);

export const fetchGameStatus = createAsyncThunk(
  ApiRoute.GAME_STATUS,
  async (queryParams: { countryID: string; gameID: string }) => {
    const { data } = await getGameApiRequest(ApiRoute.GAME_STATUS, queryParams);
    return data as GameStatusResponse;
  },
);

interface OrderSubmission {
  orderUpdates: IOrderData[];
  context: string;
  contextKey: string;
}

interface SavedOrder {
  [key: string]: {
    changed: string;
    notice: string | null;
    status: string;
  };
}

interface SavedOrdersConfirmation {
  invalid: boolean;
  notice: string;
  orders: SavedOrder;
  statusIcon: string;
  statusText: string;
}

interface DeleteCommandPayload {
  payload: {
    command: string;
    id: string;
    type: GameCommandType;
  };
}

interface NewOrderPayload {
  payload: OrderState;
}

export const saveOrders = createAsyncThunk(
  "game/submitOrders",
  async (data: OrderSubmission) => {
    const formData = new FormData();
    formData.set("orderUpdates", JSON.stringify(data.orderUpdates));
    formData.set("context", data.context);
    formData.set("contextKey", data.contextKey);
    const response = await submitOrders(formData);
    const confirmation: string = response.headers["x-json"] || "";
    const parsed: SavedOrdersConfirmation = JSON.parse(
      confirmation.substring(1, confirmation.length - 1),
    );
    return parsed;
  },
);

/**
 * createSlice handles state changes properly without reassiging state, but
 * eslint does not know this. therefore, no-param-reassign is disabled for
 * the createSlice block of code below or functions therein.
 */

/* eslint-disable no-param-reassign */
const resetOrder = (state) => {
  state.order.inProgress = false;
  state.order.unitID = "";
  state.order.onTerritory = 0;
  delete state.order.type;
};

const startNewOrder = (
  state,
  { payload: { unitID, onTerritory } }: NewOrderPayload,
) => {
  state.order.inProgress = true;
  state.order.unitID = unitID;
  state.order.onTerritory = onTerritory;
  delete state.order.type;
};

const setCommand = (
  state,
  command: GameCommand,
  container: GameContainerType,
  id: string,
) => {
  const { commands } = current(state);
  const commandsContainer = commands[container];
  const newCommand = new Map(commandsContainer[id]) || new Map();
  newCommand.set(uuidv4(), command);
  state.commands[container][id] = newCommand;
};

const gameApiSlice = createSlice({
  name: "game",
  initialState,
  reducers: {
    markOrdersAsSaved(state, orderIds) {
      const {
        data: { data: gameData },
      } = current(state);
      if ("currentOrders" in gameData && gameData.currentOrders) {
        gameData.currentOrders.forEach((order) => {
          if (orderIds.payload.includes(order.id)) {
            if (!state.ordersMeta[order.id]) {
              state.ordersMeta[order.id] = {
                saved: true,
              };
            } else {
              state.ordersMeta[order.id].saved = true;
            }
          }
        });
      }
    },
    processUnitClick(state, clickData) {
      const { order, data: gameData } = current(state);
      const { inProgress } = order;
      if (inProgress) {
        if (order.type === "hold") {
          const holdOrderTerritory = Territory[state.order.onTerritory];
          const command: GameCommand = {
            command: "CAPTURED",
          };
          setCommand(state, command, "territoryCommands", holdOrderTerritory);
        }
      }
      if (inProgress && order.unitID === clickData.payload.unitID) {
        resetOrder(state);
      } else if (inProgress && order.unitID !== clickData.payload.unitID) {
        startNewOrder(state, clickData);
      } else if (
        !inProgress &&
        "territoryStatuses" in gameData.data &&
        "territories" in gameData.data &&
        "contextVars" in gameData.data &&
        "units" in gameData.data &&
        gameData.data.contextVars &&
        gameData.data.currentOrders
      ) {
        startNewOrder(state, clickData);
      }
    },
    processMapClick(state, clickData) {
      const { order, data } = current(state);
      if (order.inProgress) {
        const territoryName = clickData.payload.name;
        const currOrderUnitID = state.order.unitID;
        if (
          Territory[order.onTerritory] === territoryName &&
          !state.order.type
        ) {
          const holdCommand: GameCommand = {
            command: "HOLD",
          };
          setCommand(state, holdCommand, "territoryCommands", territoryName);
          setCommand(state, holdCommand, "unitCommands", currOrderUnitID);

          if ("currentOrders" in data.data) {
            const { currentOrders } = data.data;
            const orderToUpdate = currentOrders?.find((o) => {
              return o.unitID === currOrderUnitID;
            });
            if (orderToUpdate) {
              state.ordersMeta[orderToUpdate.id] = {
                saved: false,
                update: {
                  type: "Hold",
                  toTerrID: null,
                },
              };
            }
          }
          state.order.type = "hold";
        } else if (state.order.type === "hold") {
          const holdOrderTerritory = Territory[state.order.onTerritory];
          const command: GameCommand = {
            command: "CAPTURED",
          };
          setCommand(state, command, "territoryCommands", holdOrderTerritory);
          resetOrder(state);
        }
      }
    },
    deleteCommand(
      state,
      { payload: { type, command, id } }: DeleteCommandPayload,
    ) {
      const { commands } = current(state);
      const commandsType = commands[type];
      const commandSet = new Map(commandsType[id]);
      const deleteKey = command;
      if (commandSet && commandSet.has(deleteKey)) {
        const newCommandSet = new Map(commandSet);
        newCommandSet.delete(deleteKey);
        state.commands[type][id] = newCommandSet;
      }
    },
    highlightMapTerritories(state) {
      const {
        data,
        overview: { members },
      } = current(state);
      if (
        "territoryStatuses" in data.data &&
        "territories" in data.data &&
        data.data.territoryStatuses
      ) {
        const membersMap = {};
        const t = data.data.territories;
        const tS = data.data.territoryStatuses;
        members.forEach((member) => {
          membersMap[member.countryID] = member.country;
        });
        tS.forEach((status) => {
          const terr = t[status.id];
          if (!status.ownerCountryID) {
            return;
          }
          const country = membersMap[status.ownerCountryID];
          const mappedTerritory = TerritoryMap[terr.name];
          const terrEnum = Territory[mappedTerritory.territory];
          const command: GameCommand = {
            command: "CAPTURED",
            data: { country: countryMap[country] },
          };
          setCommand(state, command, "territoryCommands", terrEnum);
        });
      }
    },
  },
  extraReducers(builder) {
    builder
      // fetchGameData
      .addCase(fetchGameData.pending, (state) => {
        state.apiStatus = "loading";
      })
      .addCase(fetchGameData.fulfilled, (state, action) => {
        state.apiStatus = "succeeded";
        state.data = action.payload;
      })
      .addCase(fetchGameData.rejected, (state, action) => {
        state.apiStatus = "failed";
        state.error = action.error.message;
      })
      // fetchGameOverview
      .addCase(fetchGameOverview.pending, (state) => {
        state.apiStatus = "loading";
      })
      .addCase(fetchGameOverview.fulfilled, (state, action) => {
        state.apiStatus = "succeeded";
        state.overview = action.payload;
      })
      .addCase(fetchGameOverview.rejected, (state, action) => {
        state.apiStatus = "failed";
        state.error = action.error.message;
      })
      // fetchGameStatus
      .addCase(fetchGameStatus.pending, (state) => {
        state.apiStatus = "loading";
      })
      .addCase(fetchGameStatus.fulfilled, (state, action) => {
        state.apiStatus = "succeeded";
        state.status = action.payload;
      })
      .addCase(fetchGameStatus.rejected, (state, action) => {
        state.apiStatus = "failed";
        state.error = action.error.message;
      })
      // saveOrders
      .addCase(saveOrders.fulfilled, (state, action) => {
        if (action.payload) {
          const { orders } = action.payload;
          Object.entries(orders).forEach(([id, value]) => {
            if (value.status === "Complete") {
              state.ordersMeta[id] = {
                saved: true,
              };
            }
          });
        }
      });
  },
});
/* eslint-enable no-param-reassign */

export const gameApiSliceActions = gameApiSlice.actions;

export const gameApiStatus = ({ game: { apiStatus } }: RootState): ApiStatus =>
  apiStatus;
export const gameData = ({ game: { data } }: RootState): GameDataResponse =>
  data;
export const gameError = ({ game: { error } }: RootState): GameErrorResponse =>
  error;
export const gameOverview = ({
  game: { overview },
}: RootState): GameOverviewResponse => overview;
export const gameStatus = ({
  game: { status },
}: RootState): GameStatusResponse => status;
export const gameCommands = ({ game: { commands } }: RootState): GameCommands =>
  commands;
export const gameOrdersMeta = ({
  game: { ordersMeta },
}: RootState): OrdersMeta => ordersMeta;
export const gameOrder = ({ game: { order } }: RootState): OrderState => order;

export default gameApiSlice.reducer;
