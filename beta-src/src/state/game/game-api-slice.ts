import { createSlice, createAsyncThunk, current } from "@reduxjs/toolkit";
import { v4 as uuidv4 } from "uuid";
import ApiRoute from "../../enums/ApiRoute";
import { getGameApiRequest, QueryParams, submitOrders } from "../../utils/api";
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
import Territory from "../../enums/map/variants/classic/Territory";
import OrdersMeta, { EditOrderMeta } from "../interfaces/SavedOrders";
import TerritoryMap from "../../data/map/variants/classic/TerritoryMap";
import countryMap from "../../data/map/variants/classic/CountryMap";
import OrderState from "../interfaces/OrderState";
import UpdateOrder from "../../interfaces/state/UpdateOrder";
import TerritoriesMeta, { TerritoryMeta } from "../interfaces/TerritoriesState";
import BuildUnit from "../../enums/BuildUnit";
import BuildUnitMap, { BuildUnitTypeMap } from "../../data/BuildUnit";
import UIState from "../../enums/UIState";
import { UnitSlotNames } from "../../types/map/UnitSlotName";
import getOrderStates from "../../utils/state/getOrderStates";
import ContextVar from "../../interfaces/state/ContextVar";
import drawCurrentMoveOrders from "../../utils/map/drawCurrentMoveOrders";
import getOrdersMeta from "../../utils/map/getOrdersMeta";
import getUnits from "../../utils/map/getUnits";
import UnitType from "../../types/UnitType";

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
  queryParams?: QueryParams;
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
  newContext?: ContextVar["context"];
  newContextKey?: ContextVar["contextKey"];
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

interface DispatchCommandAction {
  type: string;
  payload: {
    command: GameCommand;
    container: GameCommandType;
    identifier: string;
  };
}

export const saveOrders = createAsyncThunk(
  "game/submitOrders",
  async (data: OrderSubmission) => {
    const formData = new FormData();
    formData.set("orderUpdates", JSON.stringify(data.orderUpdates));
    formData.set("context", data.context);
    formData.set("contextKey", data.contextKey);
    const response = await submitOrders(formData, data.queryParams);
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

const drawOrders = (state) => {
  const {
    data: { data },
    ordersMeta,
  } = current(state);
  drawCurrentMoveOrders(data, ordersMeta);
};

const updateOrdersMeta = (state, updates: EditOrderMeta) => {
  Object.entries(updates).forEach(([orderID, update]) => {
    state.ordersMeta[orderID] = {
      ...state.ordersMeta[orderID],
      ...update,
    };
  });
  drawOrders(state);
};

const highlightMapTerritoriesBasedOnStatuses = (state) => {
  const {
    territoriesMeta,
    overview: { members },
  }: {
    territoriesMeta: TerritoriesMeta;
    overview: {
      members: GameOverviewResponse["members"];
    };
  } = current(state);
  if (Object.keys(territoriesMeta).length) {
    const membersMap = {};
    members.forEach((member) => {
      membersMap[member.countryID] = member.country;
    });
    Object.values(territoriesMeta).forEach((terr: TerritoryMeta) => {
      const { ownerCountryID, territory } = terr;
      const country = ownerCountryID ? membersMap[ownerCountryID] : undefined;
      if (territory) {
        const command: GameCommand = {
          command: "CAPTURED",
          data: { country: country ? countryMap[country] : "none" },
        };
        setCommand(state, command, "territoryCommands", Territory[territory]);
      }
    });
  }
};

const drawBuilds = (state) => {
  const {
    ordersMeta,
    territoriesMeta,
    overview: { members, phase },
  }: {
    ordersMeta: OrdersMeta;
    territoriesMeta: TerritoriesMeta;
    overview: {
      members: GameOverviewResponse["members"];
      phase: GameOverviewResponse["phase"];
    };
  } = current(state);
  if (phase === "Builds") {
    Object.values(ordersMeta).forEach(({ update }) => {
      if (update) {
        const { toTerrID, type } = update;
        const territoryMeta = Object.values(territoriesMeta).find(
          ({ id }) => id === toTerrID,
        );
        if (territoryMeta) {
          const buildType = BuildUnitMap[type];
          const mappedTerritory = TerritoryMap[territoryMeta.name];
          const memberCountry = members.find(
            (member) => member.countryID.toString() === territoryMeta.countryID,
          );
          if (memberCountry) {
            let command: GameCommand = {
              command: "SET_UNIT",
              data: {
                setUnit: {
                  componentType: "Icon",
                  country: countryMap[memberCountry?.country],
                  iconState: UIState.BUILD,
                  unitSlotName: mappedTerritory.unitSlotName,
                  unitType: BuildUnitTypeMap[buildType],
                },
              },
            };
            setCommand(
              state,
              command,
              "territoryCommands",
              Territory[territoryMeta.territory],
            );
            command = {
              command: "MOVE",
            };
            setCommand(
              state,
              command,
              "territoryCommands",
              Territory[territoryMeta.territory],
            );
          }
        }
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
      drawBuilds(state);
    },
    updateTerritoriesMeta(state, action) {
      state.territoriesMeta = action.payload;
    },
    processUnitClick(state, clickData) {
      const {
        order,
        data: {
          data: { contextVars },
        },
      } = current(state);
      if (contextVars?.context?.orderStatus) {
        const orderStates = getOrderStates(contextVars?.context?.orderStatus);
        if (orderStates.Ready) {
          return;
        }
      }
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
        data: {
          data: { currentOrders, contextVars },
        },
        order,
        ordersMeta,
        overview: {
          user: { member },
          phase,
        },
        territoriesMeta,
      } = current(state);
      if (contextVars?.context?.orderStatus) {
        const orderStates = getOrderStates(contextVars?.context?.orderStatus);
        if (orderStates.Ready) {
          return;
        }
      }
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
          if (currentOrders) {
            const orderToUpdate = currentOrders.find(
              (o) => o.unitID === currOrderUnitID,
            );
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
            highlightMapTerritoriesBasedOnStatuses(state);
            const command: GameCommand = {
              command: "MOVE",
            };
            setCommand(state, command, "territoryCommands", territoryName);
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
            state.order.toTerritory = TerritoryMap[canMove.name].territory;
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
      } else if (
        clickObject === "territory" &&
        phase === "Builds" &&
        currentOrders
      ) {
        const territoryMeta = territoriesMeta[Territory[territoryName]];
        if (territoryMeta) {
          const {
            coast: territoryCoast,
            countryID,
            id: webDipTerritoryID,
            supply,
            type: territoryType,
          } = territoryMeta;

          if (member.countryID.toString() !== countryID || !supply) {
            return;
          }

          const existingBuildOrder = Object.entries(ordersMeta).find(
            ([, { update }]) =>
              update ? update.toTerrID === webDipTerritoryID : false,
          );

          if (existingBuildOrder) {
            const [id] = existingBuildOrder;
            let command: GameCommand = {
              command: "REMOVE_BUILD",
              data: {
                removeBuild: { orderID: id },
              },
            };
            setCommand(state, command, "territoryCommands", territoryName);

            UnitSlotNames.forEach((slot) => {
              command = {
                command: "SET_UNIT",
                data: {
                  setUnit: {
                    unitSlotName: slot,
                  },
                },
              };
              setCommand(state, command, "territoryCommands", territoryName);
            });

            updateOrdersMeta(state, {
              [id]: {
                saved: false,
                update: {
                  type: "Wait",
                  toTerrID: null,
                },
              },
            });
            return;
          }

          const territoryHasUnit = !!territoryMeta.unitID;

          let availableOrder;
          for (let i = 0; i < currentOrders.length; i += 1) {
            const { id } = currentOrders[i];
            const orderMeta = ordersMeta[id];
            if (!orderMeta.update || !orderMeta.update?.toTerrID) {
              availableOrder = id;
              break;
            }
          }

          if (availableOrder && !territoryHasUnit) {
            let canBuild = 0;
            if (territoryCoast === "Parent" || territoryCoast === "No") {
              canBuild += BuildUnit.Army;
            }
            if (territoryType !== "Land" && territoryCoast !== "Parent") {
              canBuild += BuildUnit.Fleet;
            }
            const command: GameCommand = {
              command: "BUILD",
              data: {
                build: {
                  availableOrder,
                  canBuild,
                  toTerrID: territoryMeta.id,
                },
              },
            };
            setCommand(state, command, "territoryCommands", territoryName);
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
    drawBuilds,
    dispatchCommand(state, action: DispatchCommandAction) {
      const { command, container, identifier } = action.payload;
      setCommand(state, command, container, identifier);
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
        const {
          data: { data },
          overview: { members, phase },
        } = current(state);
        const unitsToDraw = getUnits(data, members);
        unitsToDraw.forEach(({ country, mappedTerritory, unit }) => {
          const command: GameCommand = {
            command: "SET_UNIT",
            data: {
              setUnit: {
                componentType: "Game",
                country,
                mappedTerritory,
                unit,
                unitType: unit.type as UnitType,
                unitSlotName: mappedTerritory.unitSlotName,
              },
            },
          };
          setCommand(
            state,
            command,
            "territoryCommands",
            Territory[mappedTerritory.territory],
          );
        });

        updateOrdersMeta(state, getOrdersMeta(data, phase));
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
          const { orders, newContext, newContextKey } = action.payload;
          if (newContext && newContextKey) {
            state.data.data.contextVars = {
              context: newContext,
              contextKey: newContextKey,
            };
          }
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
