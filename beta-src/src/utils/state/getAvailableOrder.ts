import { current } from "@reduxjs/toolkit";

export default function getAvailableOrder(state) {
  const { currentOrders, ordersMeta } = current(state);
  let availableOrder;
  for (let i = 0; i < currentOrders.length; i += 1) {
    const { id } = currentOrders[i];
    const orderMeta = ordersMeta[id];
    if (!orderMeta.update || !orderMeta.update?.toTerrID) {
      availableOrder = id;
      break;
    }
  }
  return availableOrder;
}
