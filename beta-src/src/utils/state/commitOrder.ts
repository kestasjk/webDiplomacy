import updateOrdersMeta from "./updateOrdersMeta";
import resetOrder from "./resetOrder";

/* eslint-disable no-param-reassign */
export default function commitOrder(state): void {
  console.log("commitOrder");
  const { order } = state;
  updateOrdersMeta(state, {
    [order.orderID]: {
      saved: false,
      update: order,
    },
  });
  resetOrder(state);
}
