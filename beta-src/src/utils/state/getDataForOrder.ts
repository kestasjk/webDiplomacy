import { current } from "@reduxjs/toolkit";
import OrderState from "../../state/interfaces/OrderState";

export default function getDataForOrder(
  state,
  { method, onTerritory, orderID, toTerritory, type, unitID }: OrderState,
): OrderState {
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
}
