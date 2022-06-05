import { EditOrderMeta } from "../../state/interfaces/SavedOrders";

/* eslint-disable no-param-reassign */
export default function updateOrdersMeta(state, updates: EditOrderMeta): void {
  const entries = Object.entries(updates);
  if (entries.length) {
    entries.forEach(([orderID, update]) => {
      state.ordersMeta[orderID] = {
        ...state.ordersMeta[orderID],
        ...update,
      };
    });
  }
}
