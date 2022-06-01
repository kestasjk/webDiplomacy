import SavedOrdersConfirmation from "../../../../../interfaces/state/SavedOrdersConfirmation";
import { setAlert } from "../../../../../state/interfaces/GameAlert";
import getOrderStates from "../../../getOrderStates";

/* eslint-disable no-param-reassign */
export default function saveOrdersFulfilled(state, action): void {
  console.log("saveOrders fulfilled");
  if (action.payload) {
    const {
      invalid,
      notice,
      orders,
      newContext,
      newContextKey,
    }: SavedOrdersConfirmation = action.payload;
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
    console.log({ returnOrders: orders });
    Object.entries(orders).forEach(([id, value]) => {
      if (value.status === "Complete") {
        state.ordersMeta[id].saved = true;
      }
    });

    // Report any errors
    if (invalid) {
      if (notice) {
        setAlert(state.alert, `Error saving orders: ${notice}`);
      } else {
        setAlert(
          state.alert,
          `Unknown error saving orders, server indicated that API call was invalid`,
        );
      }
    }
  }
}
