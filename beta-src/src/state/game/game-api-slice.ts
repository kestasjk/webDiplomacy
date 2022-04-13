import { createSlice, createAsyncThunk, current } from "@reduxjs/toolkit";
import { v4 as uuidv4 } from "uuid";
import ApiRoute from "../../enums/ApiRoute";
import { getGameApiRequest, submitOrders } from "../../utils/api";
import GameDataResponse from "../interfaces/GameDataResponse";
import GameErrorResponse from "../interfaces/GameErrorResponse";
import GameOverviewResponse from "../interfaces/GameOverviewResponse";
import GameCommands, {
  GameCommand,
  GameCommandType,
} from "../interfaces/GameCommands";
import { ApiStatus } from "../interfaces/GameState";
import GameStatusResponse from "../interfaces/GameStatusResponse";
import { RootState } from "../store";
import initialState from "./initial-state";
import { ITerritory } from "../../models/Interfaces";
import Territory from "../../enums/map/variants/classic/Territory";
import OrdersMeta, { EditOrderMeta } from "../interfaces/SavedOrders";
import TerritoryMap from "../../data/map/variants/classic/TerritoryMap";
import countryMap from "../../data/map/variants/classic/CountryMap";
import OrderState from "../interfaces/OrderState";
import UpdateOrder from "../../interfaces/state/UpdateOrder";

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
  orderUpdates: UpdateOrder[];
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

interface UpdateOrdersMetaAction {
  type: string;
  payload: EditOrderMeta;
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
  state.order.orderID = "";
  state.order.onTerritory = 0;
  state.order.toTerritory = 0;
  delete state.order.type;
};

const startNewOrder = (
  state,
  { payload: { unitID, onTerritory } }: NewOrderPayload,
) => {
  const {
    data: { data: gameData },
  } = current(state);
  const { currentOrders } = gameData;
  const orderForUnit = currentOrders.find((order) => {
    return order.unitID === unitID;
  });
  state.order.inProgress = true;
  state.order.unitID = unitID;
  state.order.orderID = orderForUnit.id;
  state.order.onTerritory = onTerritory;
  state.order.toTerritory = null;
  delete state.order.type;
};

const setCommand = (
  state,
  command: GameCommand,
  container: GameCommandType,
  id: string,
) => {
  const { commands } = current(state);
  const commandsContainer = commands[container];
  const newCommand = new Map(commandsContainer[id]) || new Map();
  newCommand.set(uuidv4(), command);
  state.commands[container][id] = newCommand;
};

const updateOrdersMeta = (state, updates: EditOrderMeta) => {
  Object.entries(updates).forEach(([orderID, update]) => {
    state.ordersMeta[orderID] = {
      ...state.ordersMeta[orderID],
      ...update,
    };
  });
};

const highlightMapTerritoriesBasedOnStatuses = (
  state,
  filter: Territory[] = [],
) => {
  const {
    data: { data: gameData },
    overview: { members },
  } = current(state);
  if ("territories" in gameData && gameData.territories) {
    const membersMap = {};
    members.forEach((member) => {
      membersMap[member.countryID] = member.country;
    });
    const t: ITerritory[] = Object.values(gameData.territories);
    t.forEach(({ countryID, name }) => {
      const country = membersMap[countryID];
      const mappedTerritory = TerritoryMap[name];
      if (filter.length && !filter.includes(mappedTerritory.territory)) {
        return;
      }
      const terrEnum = Territory[mappedTerritory.territory];
      if (terrEnum) {
        const command: GameCommand = {
          command: "CAPTURED",
          data: { country: country ? countryMap[country] : "none" },
        };
        setCommand(state, command, "territoryCommands", terrEnum);
      }
    });
  }
};

const gameApiSlice = createSlice({
  name: "game",
  initialState,
  reducers: {
    updateOrdersMeta(state, action: UpdateOrdersMetaAction) {
      updateOrdersMeta(state, action.payload);
    },
    processUnitClick(state, clickData) {
      const { order } = current(state);
      const { inProgress } = order;
      if (inProgress) {
        if (order.type === "hold" && order.onTerritory !== null) {
          highlightMapTerritoriesBasedOnStatuses(state);
        } else if (order.type === "move" && order.toTerritory !== null) {
          highlightMapTerritoriesBasedOnStatuses(state);
        }
      }
      if (inProgress && order.unitID === clickData.payload.unitID) {
        resetOrder(state);
      } else if (inProgress && order.unitID !== clickData.payload.unitID) {
        startNewOrder(state, clickData);
      } else {
        startNewOrder(state, clickData);
      }
    },
    processMapClick(state, clickData) {
      const {
        data: { data: gameData },
        order,
        ordersMeta,
      } = current(state);
      const {
        payload: { clickObject, evt, name: territoryName },
      } = clickData;
      if (order.inProgress) {
        const currOrderUnitID = order.unitID;
        if (
          order.onTerritory !== null &&
          Territory[order.onTerritory] === territoryName &&
          !order.type
        ) {
          let command: GameCommand = {
            command: "HOLD",
          };
          setCommand(state, command, "territoryCommands", territoryName);
          setCommand(state, command, "unitCommands", currOrderUnitID);
          command = {
            command: "REMOVE_ARROW",
            data: {
              orderID: order.orderID,
            },
          };
          setCommand(state, command, "mapCommands", "all");
          if ("currentOrders" in gameData) {
            const { currentOrders } = gameData;
            const orderToUpdate = currentOrders?.find((o) => {
              return o.unitID === currOrderUnitID;
            });
            if (orderToUpdate) {
              updateOrdersMeta(state, {
                [orderToUpdate.id]: {
                  saved: false,
                  update: {
                    type: "Hold",
                    toTerrID: null,
                  },
                },
              });
            }
          }
          state.order.type = "hold";
        } else if (order.onTerritory !== null && order.type === "hold") {
          highlightMapTerritoriesBasedOnStatuses(state);
          resetOrder(state);
        } else if (order.toTerritory !== null && order.type === "move") {
          highlightMapTerritoriesBasedOnStatuses(state);
          resetOrder(state);
        } else if (
          clickObject === "territory" &&
          order.onTerritory !== null &&
          Territory[order.onTerritory] !== territoryName &&
          !order.type &&
          order.inProgress
        ) {
          const { allowedBorderCrossings } = ordersMeta[order.orderID];
          const canMove = allowedBorderCrossings?.find((border) => {
            const mappedTerritory = TerritoryMap[border.name];
            return Territory[mappedTerritory.territory] === territoryName;
          });
          if (canMove) {
            const toTerritory = Number(Territory[territoryName]);
            let command: GameCommand = {
              command: "REMOVE_ARROW",
              data: {
                orderID: order.orderID,
              },
            };
            highlightMapTerritoriesBasedOnStatuses(state);
            setCommand(state, command, "mapCommands", "all");
            command = {
              command: "DRAW_ARROW",
              data: {
                orderID: order.orderID,
                arrow: {
                  from: order.onTerritory,
                  to: toTerritory,
                  type: "move",
                },
              },
            };
            setCommand(state, command, "mapCommands", "all");
            command = {
              command: "HOLD",
            };
            setCommand(state, command, "territoryCommands", territoryName);
            const update: EditOrderMeta = {};
            updateOrdersMeta(state, {
              [order.orderID]: {
                saved: false,
                update: {
                  type: "Move",
                  toTerrID: canMove.id,
                  viaConvoy: "No",
                },
              },
            });
            updateOrdersMeta(state, update);
            state.order.toTerritory = toTerritory;
            state.order.type = "move";
          } else {
            const command: GameCommand = {
              command: "INVALID_CLICK",
              data: {
                click: {
                  evt,
                  territoryName,
                },
              },
            };
            setCommand(state, command, "mapCommands", "all");
          }
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
      highlightMapTerritoriesBasedOnStatuses(state);
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
              state.ordersMeta[id].saved = true;
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
