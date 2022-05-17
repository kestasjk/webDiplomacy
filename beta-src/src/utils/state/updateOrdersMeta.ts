import { EditOrderMeta } from "../../state/interfaces/SavedOrders";
import drawOrders from "../map/drawOrders";
import writeNotifications from "../map/writeNotifications";

/* eslint-disable no-param-reassign */
export default function updateOrdersMeta(state, updates: EditOrderMeta): void {
  Object.entries(updates).forEach(([orderID, update]) => {
    state.ordersMeta[orderID] = {
      ...state.ordersMeta[orderID],
      ...update,
    };
  });
  writeNotifications(state);
  drawOrders(state);
  const entries = Object.entries(updates);
  if (entries.length) {
    entries.forEach(([orderID, update]) => {
      state.ordersMeta[orderID] = {
        ...state.ordersMeta[orderID],
        ...update,
      };
    });
    drawOrders(state);
  }
}
