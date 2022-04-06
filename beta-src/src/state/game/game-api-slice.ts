import { createSlice, createAsyncThunk, current } from "@reduxjs/toolkit";
import { v4 as uuidv4 } from "uuid";
import ApiRoute from "../../enums/ApiRoute";
import { getGameApiRequest, submitOrders } from "../../utils/api";
import GameDataResponse from "../interfaces/GameDataResponse";
import GameErrorResponse from "../interfaces/GameErrorResponse";
import GameOverviewResponse from "../interfaces/GameOverviewResponse";
import GameCommands from "../interfaces/GameCommands";
import { ApiStatus } from "../interfaces/GameState";
import GameStatusResponse from "../interfaces/GameStatusResponse";
import { RootState } from "../store";
import initialState from "./initial-state";
import { IOrderData } from "../../models/Interfaces";
import Territory from "../../enums/map/variants/classic/Territory";
import OrdersMeta from "../interfaces/SavedOrders";
import TerritoryMap from "../../data/map/variants/classic/TerritoryMap";
import countryMap from "../../data/map/variants/classic/CountryMap";

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
 * the createSlice block of code below.
 */

/* eslint-disable no-param-reassign */
const gameApiSlice = createSlice({
  name: "game",
  initialState,
  reducers: {
    markOrdersAsSaved(state, orderIds) {
      const { data } = current(state);
      if ("currentOrders" in data.data && data.data.currentOrders) {
        data.data.currentOrders.forEach((order) => {
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
    processUnitClick(state, data) {
      const {
        order,
        commands: { unitCommands },
      } = current(state);
      const { unitID, inProgress } = order;
      if (unitID === data.payload.unitID) {
        const newUnitCommands =
          new Map(unitCommands[data.payload.unitID]) || new Map();
        newUnitCommands.set(uuidv4(), {
          command: "CANCEL",
        });
        state.commands.unitCommands[data.payload.unitID] = newUnitCommands;
        state.order.inProgress = false;
        state.order.unitID = "";
        state.order.onTerritory = 0;
      } else if (!inProgress) {
        state.order.inProgress = true;
        state.order.unitID = data.payload.unitID;
        state.order.onTerritory = data.payload.onTerritory;
      }
    },
    processTerritoryClick(state, clickData) {
      const {
        order,
        commands: { unitCommands, territoryCommands },
        data,
      } = current(state);
      if (order.inProgress) {
        if (Territory[order.onTerritory] === clickData.payload.name) {
          const newTerritoryCommands =
            new Map(territoryCommands[clickData.payload.name]) || new Map();
          newTerritoryCommands.set(uuidv4(), {
            command: "HOLD",
          });
          const newUnitCommands =
            new Map(unitCommands[state.order.unitID]) || new Map();
          newUnitCommands.set(uuidv4(), {
            command: "HOLD",
          });
          state.commands.territoryCommands[clickData.payload.name] =
            newTerritoryCommands;
          state.commands.unitCommands[state.order.unitID] = newUnitCommands;
          if ("currentOrders" in data.data) {
            const { currentOrders } = data.data;
            const orderToUpdate = currentOrders?.find((o) => {
              return o.unitID === state.order.unitID;
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
          state.order.inProgress = false;
          state.order.unitID = "";
          state.order.onTerritory = 0;
        }
      }
    },
    deleteCommand(state, data) {
      const {
        commands: { territoryCommands, unitCommands },
      } = current(state);
      switch (data.payload.type) {
        case "territory": {
          const commandSet = new Map(territoryCommands[data.payload.name]);
          const deleteKey = data.payload.command;
          if (commandSet && commandSet.has(deleteKey)) {
            const newCommandSet = new Map(commandSet);
            newCommandSet.delete(deleteKey);
            state.commands.territoryCommands[data.payload.name] = newCommandSet;
          }
          break;
        }
        case "unit": {
          const commandSet = new Map(unitCommands[data.payload.id]);
          const deleteKey = data.payload.command;
          if (commandSet && commandSet.has(deleteKey)) {
            const newCommandSet = new Map(commandSet);
            newCommandSet.delete(deleteKey);
            state.commands.unitCommands[data.payload.id] = newCommandSet;
          }
          break;
        }
        default:
          break;
      }
    },
    highlightMapTerritories(state) {
      const {
        data,
        overview: { members },
        commands: { territoryCommands },
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
          const newTerritoryCommands =
            new Map(territoryCommands[terrEnum]) || new Map();
          newTerritoryCommands.set(uuidv4(), {
            command: "CAPTURED",
            data: {
              country: countryMap[country],
            },
          });
          state.commands.territoryCommands[terrEnum] = newTerritoryCommands;
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

export default gameApiSlice.reducer;
