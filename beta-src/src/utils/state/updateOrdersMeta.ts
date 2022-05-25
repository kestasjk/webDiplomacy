import { EditOrderMeta } from "../../state/interfaces/SavedOrders";

/* eslint-disable no-param-reassign */
export default function updateOrdersMeta(state, updates: EditOrderMeta): void {
  console.log("updateOrdersMeta");
  console.log({ updates });
  const entries = Object.entries(updates);
  if (entries.length) {
    entries.forEach(([orderID, update]) => {
      console.log({ orderID, order: state.ordersMeta[orderID], update });

      state.ordersMeta[orderID] = {
        ...state.ordersMeta[orderID],
        ...update,
      };
    });
  }
}
