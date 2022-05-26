import { current } from "@reduxjs/toolkit";
import OrderState from "../../state/interfaces/OrderState";

export default function getDataForOrder(
  state,
  { fromTerrID, orderID, toTerrID, type, unitID }: OrderState,
): OrderState {
  const {
    data: { data: gameData },
  } = current(state);
  const newOrder: OrderState = {
    inProgress: true,
    fromTerrID,
    orderID:
      orderID ||
      gameData.currentOrders.find((order) => order.unitID === unitID)?.id,
    toTerrID,
    unitID,
    type,
    viaConvoy: "",
  };
  return newOrder;
}
