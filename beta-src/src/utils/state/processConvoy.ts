import { current } from "@reduxjs/toolkit";
import Territory from "../../enums/map/variants/classic/Territory";
import OrderClass from "../../models/OrderClass";
import updateOrdersMeta from "./updateOrdersMeta";
import invalidClick from "../map/invalidClick";
import updateOrder from "./updateOrder";
import { GameState } from "../../state/interfaces/GameState";

/* eslint-disable no-param-reassign */
export default function processConvoy(state: GameState, evt): boolean {
  const {
    board,
    data: {
      data: { currentOrders },
    },
    order,
    maps,
  } = current(state);
  // eslint-disable-next-line no-debugger

  const { fromTerrID, toTerrID } = order;
  if (!board) throw Error();
  const orderUnit = board.findUnitByID(order.unitID);
  const unitTerrID = maps.unitToTerrID[order.unitID];
  const againstTerritory = board.findTerritoryByID(toTerrID);
  let convoyPath;
  if (order.type === "Convoy") {
    const fleetOrder = currentOrders?.find((o) => o.unitID === order.unitID);
    const convoyOrder = new OrderClass(board, fleetOrder!, orderUnit!);
    const convoyArmy = board.units.find((c) => c.terrID === order.fromTerrID);
    const fromTerritory = board.findTerritoryByID(fromTerrID);
    console.log({
      fromTerritory,
      againstTerritory,
      unitTerr: convoyOrder.unit.Territory,
    });
    convoyPath = convoyArmy?.ConvoyGroup?.pathArmyToCoastWithFleet(
      fromTerritory,
      againstTerritory,
      convoyOrder.unit.Territory,
    );
  } else if (order.type === "Move") {
    const convoyArmy = board.units.find((c) => c.id === order.unitID);
    convoyPath = convoyArmy?.ConvoyGroup.pathArmyToCoast(
      board.findTerritoryByID(unitTerrID),
      againstTerritory,
    );
  } else {
    throw Error();
  }

  if (convoyPath) {
    updateOrder(state, {
      convoyPath,
    });
  }
  return !!convoyPath;
}
