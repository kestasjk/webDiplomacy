import { current } from "@reduxjs/toolkit";
import TerritoryMap from "../../../../data/map/variants/classic/TerritoryMap";
import BuildUnit from "../../../../enums/BuildUnit";
import Territory from "../../../../enums/map/variants/classic/Territory";
import { GameCommand } from "../../../../state/interfaces/GameCommands";
import GameDataResponse from "../../../../state/interfaces/GameDataResponse";
import { GameState } from "../../../../state/interfaces/GameState";
import { UnitSlotNames } from "../../../../types/map/UnitSlotName";
import highlightMapTerritoriesBasedOnStatuses from "../../../map/highlightMapTerritoriesBasedOnStatuses";
import getOrderStates from "../../getOrderStates";
import processConvoy from "../../processConvoy";
import resetOrder from "../../resetOrder";
import setCommand from "../../setCommand";
import startNewOrder from "../../startNewOrder";
import updateOrdersMeta from "../../updateOrdersMeta";

/* eslint-disable no-param-reassign */
export default function processMapClick(state, clickData) {
  const {
    data: { data },
    order,
    ordersMeta,
    overview,
    territoriesMeta,
  }: {
    data: { data: GameDataResponse["data"] };
    order: GameState["order"];
    ordersMeta: GameState["ordersMeta"];
    overview: GameState["overview"];
    territoriesMeta: GameState["territoriesMeta"];
  } = current(state);
  const {
    inProgress,
    method,
    orderID,
    onTerritory,
    subsequentClicks,
    toTerritory,
    type,
    unitID,
  } = order;
  const {
    user: { member },
    phase,
  } = overview;
  const { currentOrders, contextVars } = data;
  if (contextVars?.context?.orderStatus) {
    const orderStates = getOrderStates(contextVars?.context?.orderStatus);
    if (orderStates.Ready) {
      return;
    }
  }
  const {
    payload: { clickObject, evt, name: territoryName },
  } = clickData;
  const truthyToTerritory = toTerritory !== undefined && toTerritory !== null;
  const truthyOnTerritory = onTerritory !== undefined && onTerritory !== null;
  if (inProgress && method === "click") {
    const currOrderUnitID = unitID;
    if (
      truthyOnTerritory &&
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
                type: "Hold",
                toTerrID: null,
              },
            },
          });
        }
      }
      state.order.type = "hold";
    } else if (type === "convoy" && !truthyToTerritory) {
      state.order.toTerritory = Number(Territory[territoryName]);
      processConvoy(state);
    } else if (
      truthyOnTerritory &&
      (type === "hold" || type === "move" || type === "convoy")
    ) {
      highlightMapTerritoriesBasedOnStatuses(state);
      resetOrder(state);
    } else if (truthyToTerritory && type === "build") {
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
      truthyOnTerritory &&
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
          const nc = territoriesMeta[Territory.SAINT_PETERSBURG_NORTH_COAST];
          const sc = territoriesMeta[Territory.SAINT_PETERSBURG_SOUTH_COAST];
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
}