import { current } from "@reduxjs/toolkit";
import Territory from "../../enums/map/variants/classic/Territory";
import OrderClass from "../../models/OrderClass";
import { GameCommand } from "../../state/interfaces/GameCommands";
import updateOrdersMeta from "./updateOrdersMeta";
import setCommand from "./setCommand";

/* eslint-disable no-param-reassign */
export default function processConvoy(state): void {
  const {
    board,
    data: {
      data: { currentOrders },
    },
    order,
    ordersMeta,
    maps,
  } = current(state);
  const lastFleetInChain =
    order.subsequentClicks[order.subsequentClicks.length - 1];
  if (lastFleetInChain) {
    const { convoyToChoices } = ordersMeta[lastFleetInChain.order];
    const toTerritory = maps.enumToTerritory[order.toTerritory];
    const fromTerritory = maps.enumToTerritory[order.onTerritory];

    if (convoyToChoices.includes(toTerritory)) {
      const orderUnit = board.findUnitByID(lastFleetInChain.unitID);
      const fleetOrder = currentOrders?.find(
        (o) => o.unitID === lastFleetInChain.unitID,
      );
      if (fleetOrder) {
        const convoyOrder = new OrderClass(board, fleetOrder, orderUnit);
        const againstTerritory = board.findTerritoryByID(toTerritory);
        const convoyFrom = convoyOrder.getConvoyFromChoices(againstTerritory);
        const convoyArmy = convoyFrom.find((c) => c.id === order.unitID);
        if (convoyArmy) {
          const convoyPath = convoyArmy.ConvoyGroup.pathArmyToCoastWithFleet(
            board.findTerritoryByID(fromTerritory),
            againstTerritory,
            convoyOrder.unit.Territory,
          );
          if (convoyPath) {
            const clickedUnitsTerritories = order.subsequentClicks.map(
              (click) => {
                return maps.enumToTerritory[click.onTerritory];
              },
            );
            clickedUnitsTerritories.push(
              maps.enumToTerritory[order.onTerritory],
            );
            const updates = {};
            convoyPath.forEach((terrID) => {
              let foundClick =
                maps.enumToTerritory[order.onTerritory] === terrID
                  ? order
                  : false;
              if (!foundClick) {
                foundClick = order.subsequentClicks.find((click) => {
                  return maps.enumToTerritory[click.onTerritory] === terrID;
                });
              }
              if (!foundClick) {
                // user did not click this unit
              } else {
                const unitOrderID = maps.unitToOrder[foundClick.unitID];
                const unitIsArmy =
                  board.findUnitByID(foundClick.unitID).type === "Army";
                if (unitIsArmy) {
                  updates[unitOrderID] = {
                    saved: false,
                    update: {
                      convoyPath,
                      toTerrID: toTerritory,
                      type: "Move",
                      viaConvoy: "Yes",
                    },
                  };
                } else {
                  updates[unitOrderID] = {
                    saved: false,
                    update: {
                      convoyPath,
                      toTerrID: toTerritory,
                      fromTerrID: fromTerritory,
                      type: "Convoy",
                    },
                  };
                }
              }
            });
            updateOrdersMeta(state, updates);
            const command: GameCommand = {
              command: "MOVE",
            };
            setCommand(
              state,
              command,
              "territoryCommands",
              Territory[order.toTerritory],
            );
          }
        }
      }
      return;
    }
  }
  const command: GameCommand = {
    command: "INVALID_CLICK",
  };
  setCommand(state, command, "territoryCommands", Territory[order.toTerritory]);
}
