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
import getOrdersMeta from "../../utils/map/getOrdersMeta";
import getUnits from "../../utils/map/getUnits";
import UnitType from "../../types/UnitType";
import drawSupportMoveOrders from "../../utils/map/drawSupportMoveOrders";
import drawMoveOrders from "../../utils/map/drawMoveOrders";
import generateMaps from "../../utils/state/generateMaps";
import removeAllArrows from "../../utils/map/removeAllArrows";
import drawSupportHoldOrders from "../../utils/map/drawSupportHoldOrders";

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

const resetOrder = (state) => {
  const {
    order: { unitID, type },
  } = current(state);
  if (type !== "hold") {
    const command: GameCommand = {
      command: "NONE",
    };
    setCommand(state, command, "unitCommands", unitID);
  }
  state.order.inProgress = false;
  state.order.unitID = "";
  state.order.orderID = "";
  state.order.onTerritory = 0;
  state.order.toTerritory = 0;
  delete state.order.type;
};

const getDataForOrder = (
  state,
  { method, onTerritory, orderID, toTerritory, type, unitID }: OrderState,
): OrderState => {
  const {
    data: { data: gameData },
  } = current(state);
  const newOrder: OrderState = {
    inProgress: true,
    method,
    onTerritory,
    orderID:
      orderID ||
      gameData.currentOrders.find((order) => order.unitID === unitID)?.id,
    subsequentClicks: [],
    toTerritory,
    unitID,
  };
  if (type) {
    newOrder.type = type;
  }
  return newOrder;
};

const startNewOrder = (state, action: NewOrderPayload) => {
  const {
    order: { unitID: prevUnitID },
  } = current(state);
  if (prevUnitID) {
    const command: GameCommand = {
      command: "NONE",
    };
    setCommand(state, command, "unitCommands", prevUnitID);
  }
  delete state.order.type;
  const orderData = getDataForOrder(state, action.payload);
  state.order = orderData;
  const { unitID } = orderData;
  if (unitID) {
    const command: GameCommand = {
      command: "SELECTED",
    };
    setCommand(state, command, "unitCommands", unitID);
  }
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
            const commandTerritoryDestination =
              territoryMeta.territory ===
                Territory.SAINT_PETERSBURG_NORTH_COAST ||
              territoryMeta.territory === Territory.SAINT_PETERSBURG_SOUTH_COAST
                ? Territory[Territory.SAINT_PETERSBURG]
                : Territory[territoryMeta.territory];
            setCommand(
              state,
              command,
              "territoryCommands",
              commandTerritoryDestination,
            );
            command = {
              command: "MOVE",
            };
            setCommand(
              state,
              command,
              "territoryCommands",
              commandTerritoryDestination,
            );
          }
        }
      }
    });
  }
};

const updateUnitsDisbanding = (state) => {
  const {
    data: {
      data: { contextVars, currentOrders },
    },
    territoriesMeta,
    ordersMeta,
    overview: { phase },
  }: {
    data;
    territoriesMeta: TerritoriesMeta;
    ordersMeta;
    overview: {
      phase: GameOverviewResponse["phase"];
      members: GameOverviewResponse["members"];
    };
  } = current(state);
  if (currentOrders && contextVars && territoriesMeta) {
    if (phase === "Retreats") {
      const userDisbandingUnits = currentOrders.filter(
        (o) =>
          ordersMeta[o.id].update.type === "Disband" || o.type === "Disband",
      );

      if (userDisbandingUnits) {
        userDisbandingUnits.forEach(({ id, unitID }) => {
          if (ordersMeta[id].saved) {
            const command: GameCommand = {
              command: "DISBAND",
            };
            setCommand(state, command, "unitCommands", unitID);

            highlightMapTerritoriesBasedOnStatuses(state);
          }
        });
      }
    }
  }
};

const drawOrders = (state) => {
  const {
    data: { data },
    maps,
    ordersMeta,
  } = current(state);
  removeAllArrows();
  drawMoveOrders(data, ordersMeta);
  drawSupportMoveOrders(data, maps, ordersMeta);
  drawSupportHoldOrders(data, ordersMeta);
  drawBuilds(state);
  updateUnitsDisbanding(state);
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

const gameApiSlice = createSlice({
  name: "game",
  initialState,
  reducers: {
    resetOrder,
    updateOrdersMeta(state, action: UpdateOrdersMetaAction) {
      updateOrdersMeta(state, action.payload);
    },
    updateTerritoriesMeta(state, action) {
      state.territoriesMeta = action.payload;
    },
    processUnitDoubleClick(state, clickData) {
      const {
        data: {
          data: { contextVars },
        },
        ownUnits,
      } = current(state);
      if (!ownUnits.includes(clickData.payload.unitID)) {
        return;
      }
      if (contextVars?.context?.orderStatus) {
        const orderStates = getOrderStates(contextVars?.context?.orderStatus);
        if (orderStates.Ready) {
          return;
        }
      }
      startNewOrder(state, clickData);
    },
    processUnitClick(state, clickData) {
      const {
        data: {
          data: { contextVars },
        },
        order: {
          inProgress,
          method,
          onTerritory,
          orderID,
          toTerritory,
          type,
          unitID,
        },
        ownUnits,
      } = current(state);
      if (contextVars?.context?.orderStatus) {
        const orderStates = getOrderStates(contextVars?.context?.orderStatus);
        if (orderStates.Ready) {
          return;
        }
      }
      if (inProgress) {
        if (unitID === clickData.payload.unitID) {
          resetOrder(state);
        } else if (
          (type === "hold" || type === "move") &&
          onTerritory !== null
        ) {
          highlightMapTerritoriesBasedOnStatuses(state);
        } else if (
          method === "dblClick" &&
          unitID !== clickData.payload.unitID
        ) {
          state.order.subsequentClicks.push({
            ...{
              inProgress: true,
              orderID,
              toTerritory: null,
            },
            ...clickData.payload,
          });
        } else if (ownUnits.includes(clickData.payload.unitID)) {
          startNewOrder(state, clickData);
        }
      } else if (ownUnits.includes(clickData.payload.unitID)) {
        startNewOrder(state, clickData);
      }
    },
    processMapClick(state, clickData) {
      const {
        data: {
          data: { currentOrders, contextVars },
        },
        order: {
          inProgress,
          method,
          orderID,
          onTerritory,
          subsequentClicks,
          toTerritory,
          type,
          unitID,
        },
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
      if (inProgress && method === "click") {
        const currOrderUnitID = unitID;
        if (
          onTerritory !== null &&
          Territory[onTerritory] === territoryName &&
          !type
        ) {
          let command: GameCommand = {
            command: "HOLD",
          };
          setCommand(state, command, "territoryCommands", territoryName);
          setCommand(state, command, "unitCommands", currOrderUnitID);
          command = {
            command: "REMOVE_ARROW",
            data: {
              orderID,
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
                    type: phase === "Retreats" ? "Disband" : "Hold",
                    toTerrID: null,
                  },
                },
              });
            }
          }
          state.order.type = phase === "Retreats" ? "disband" : "hold";
        } else if (onTerritory !== null && type === "hold") {
          highlightMapTerritoriesBasedOnStatuses(state);
          resetOrder(state);
        } else if (toTerritory !== null && type === "move") {
          highlightMapTerritoriesBasedOnStatuses(state);
          resetOrder(state);
        } else if (toTerritory !== null && type === "build") {
          setCommand(
            state,
            {
              command: "REMOVE_BUILD",
            },
            "territoryCommands",
            Territory[toTerritory],
          );
          resetOrder(state);
        } else if (
          clickObject === "territory" &&
          onTerritory !== null &&
          Territory[onTerritory] !== territoryName &&
          !type &&
          inProgress
        ) {
          const { allowedBorderCrossings } = ordersMeta[orderID];
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
              [orderID]: {
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
      } else if (inProgress && method === "dblClick") {
        if (subsequentClicks.length) {
          // user is trying to do a support move or a support hold
          const unitSupporting = ordersMeta[orderID];
          const unitBeingSupported = subsequentClicks[0];
          const terrEnum = Number(Territory[territoryName]);
          if (terrEnum === unitBeingSupported.onTerritory) {
            // attemping support hold
            const match = unitSupporting.supportHoldChoices?.find(
              ({ unitID: uID }) => uID === unitBeingSupported.unitID,
            );
            if (match) {
              // execute support hold
              updateOrdersMeta(state, {
                [orderID]: {
                  saved: false,
                  update: {
                    type: "Support hold",
                    toTerrID: match.id,
                  },
                },
              });
              resetOrder(state);
              return;
            }
          } else {
            // attempting support move
            const supportMoveMatch = unitSupporting.supportMoveChoices?.find(
              ({ supportMoveTo: { name } }) =>
                TerritoryMap[name].territory === terrEnum,
            );
            if (supportMoveMatch && supportMoveMatch.supportMoveFrom.length) {
              const match = supportMoveMatch.supportMoveFrom.find(
                ({ unitID: uID }) => uID === unitBeingSupported.unitID,
              );
              if (match) {
                // execute support move
                updateOrdersMeta(state, {
                  [orderID]: {
                    saved: false,
                    update: {
                      type: "Support move",
                      toTerrID: supportMoveMatch.supportMoveTo.id,
                      fromTerrID: match.id,
                    },
                  },
                });
                resetOrder(state);
                return;
              }
            }
          }
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

          const stp = territoriesMeta[Territory.SAINT_PETERSBURG];
          const stpnc = territoriesMeta[Territory.SAINT_PETERSBURG_NORTH_COAST];
          const stpsc = territoriesMeta[Territory.SAINT_PETERSBURG_SOUTH_COAST];

          const specialIds = {};
          if (stp) {
            specialIds[stp.id] = [stpnc?.id, stpsc?.id];
          }

          const affectedTerritoryIds = specialIds[webDipTerritoryID]
            ? [...[webDipTerritoryID], ...specialIds[webDipTerritoryID]]
            : [webDipTerritoryID];

          const existingBuildOrder = Object.entries(ordersMeta).find(
            ([, { update }]) =>
              update ? affectedTerritoryIds.includes(update.toTerrID) : false,
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

          if (availableOrder && !territoryHasUnit && !inProgress) {
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
                build: [
                  {
                    availableOrder,
                    canBuild,
                    toTerrID: territoryMeta.id,
                    unitSlotName: "main",
                  },
                ],
              },
            };
            if (territoryMeta.territory === Territory.SAINT_PETERSBURG) {
              const nc =
                territoriesMeta[Territory.SAINT_PETERSBURG_NORTH_COAST];
              const sc =
                territoriesMeta[Territory.SAINT_PETERSBURG_SOUTH_COAST];
              nc &&
                command.data?.build?.push({
                  availableOrder,
                  canBuild: BuildUnit.Fleet,
                  toTerrID: nc.id,
                  unitSlotName: "nc",
                });
              sc &&
                command.data?.build?.push({
                  availableOrder,
                  canBuild: BuildUnit.Fleet,
                  toTerrID: sc.id,
                  unitSlotName: "sc",
                });
            }
            setCommand(state, command, "territoryCommands", territoryName);
            startNewOrder(state, {
              payload: {
                inProgress: true,
                method: "click",
                orderID: availableOrder,
                onTerritory: null,
                subsequentClicks: [],
                toTerritory: territoryMeta.territory,
                type: "build",
                unitID: "",
              },
            });
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
    updateUnitsDisbanding,
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
        state.maps = generateMaps(data);
        data.currentOrders?.forEach(({ unitID }) => {
          state.ownUnits.push(unitID);
        });
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
            mappedTerritory.parent
              ? Territory[mappedTerritory.parent]
              : Territory[mappedTerritory.territory],
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

          updateUnitsDisbanding(state);
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
