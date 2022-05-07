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
      data: { currentOrders, territories },
    },
    order,
    ordersMeta,
    maps,
  } = current(state);
  const lastFleetInChain =
    order.subsequentClicks[order.subsequentClicks.length - 1];
  console.log({ order });
  if (lastFleetInChain) {
    const { convoyToChoices } = ordersMeta[lastFleetInChain.orderID];
    const toTerritory = maps.enumToTerritory[order.toTerritory];
    const fromTerritory = maps.enumToTerritory[order.onTerritory];
    console.log({
      convoyToChoices,
      toTerritory,
      lastFleetInChain,
      fromTerritory,
    });
    if (convoyToChoices.includes(toTerritory)) {
      console.log("can convoy here");
      const orderUnit = board.findUnitByID(lastFleetInChain.unitID);
      const fleetOrder = currentOrders?.find(
        (o) => o.unitID === lastFleetInChain.unitID,
      );
      console.log({
        orderUnit,
        fleetOrder,
      });
      if (fleetOrder) {
        const convoyOrder = new OrderClass(board, fleetOrder, orderUnit);
        const againstTerritory = board.findTerritoryByID(toTerritory);
        const convoyFrom = convoyOrder.getConvoyFromChoices(againstTerritory);
        console.log({
          againstTerritory,
          convoyOrder,
          convoyFrom,
        });
        const convoyArmy = convoyFrom.find((c) => c.id === order.unitID);
        if (convoyArmy) {
          console.log({ convoyArmy });
          console.log({
            b1: board.findTerritoryByID(fromTerritory),
            b2: againstTerritory,
            b3: convoyOrder.unit.Territory,
          });
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

              console.log({ foundClick });

              if (!foundClick) {
                console.log(`user did not click: ${terrID}`);
              } else {
                const unitOrderID = maps.unitToOrder[foundClick.unitID];
                console.log(`user clicked: ${terrID}`);
                const unitIsArmy =
                  board.findUnitByID(foundClick.unitID).type === "Army";
                console.log({ unitIsArmy });
                if (unitIsArmy) {
                  console.log("unit is army");
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
                  console.log("unit is not army");
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

            console.log({ updates });
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
            console.log({ convoyPath, clickedUnitsTerritories });
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
