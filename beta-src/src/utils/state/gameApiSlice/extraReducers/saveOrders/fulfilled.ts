import SavedOrdersConfirmation from "../../../../../interfaces/state/SavedOrdersConfirmation";
import getOrderStates from "../../../getOrderStates";

/* eslint-disable no-param-reassign */
export default function saveOrdersFulfilled(state, action): void {
  if (action.payload) {
    const { orders, newContext, newContextKey }: SavedOrdersConfirmation =
      action.payload;
    if (newContext && newContextKey) {
      state.data.data.contextVars = {
        context: newContext,
        contextKey: newContextKey,
      };
      const orderStates = getOrderStates(newContext.orderStatus);
      state.overview.user.member.orderStatus = {
        Completed: orderStates.Completed,
        Ready: orderStates.Ready,
        None: orderStates.None,
        Saved: orderStates.Saved,
      };
    }
    // console.log({ returnOrders: orders });
    Object.entries(orders).forEach(([id, value]) => {
      if (value.status === "Complete") {
        state.ordersMeta[id].saved = true;
      }
    });
  }
}
