import { EditOrder } from "../../state/interfaces/SavedOrders";
import commitOrder from "./commitOrder";
import UIState from "../../enums/UIState";

/* eslint-disable no-param-reassign */
export default function updateOrder(state, update: EditOrder): void {
  console.log("updateOrder");
  console.log({ update });
  state.order = { ...state.order, ...update };

  // decide whether to commit
  const { order } = state;
  console.log({ orderAfter: order });
  if (order.type === "hold") {
    // agh get rid of this!!!

    state.unitState[order.unitID] = UIState.HOLD;
    commitOrder(state);
  }
}
