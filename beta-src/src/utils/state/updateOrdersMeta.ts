import { EditOrderMeta } from "../../state/interfaces/SavedOrders";
import drawOrders from "../map/drawOrders";

/* eslint-disable no-param-reassign */
export default function updateOrdersMeta(state, updates: EditOrderMeta): void {
  console.log("updateOrdersMeta");
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
