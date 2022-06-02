import updateOrdersMeta from "./updateOrdersMeta";
import resetOrder from "./resetOrder";

/* eslint-disable no-param-reassign */
export default function commitOrder(state, order): void {
  // console.log({ order });
  // just be careful
  const o = { ...order };
  if (o.type === "Support") {
    o.type = o.fromTerrID === o.toTerrID ? "Support hold" : "Support move";
  }
  if (!o.viaConvoy) {
    o.viaConvoy = "No";
  }
  updateOrdersMeta(state, {
    [order.orderID]: {
      saved: false,
      update: o,
    },
  });
  resetOrder(state);
}
