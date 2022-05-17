import SavedOrdersConfirmation from "../../../../../interfaces/state/SavedOrdersConfirmation";
import updateUnitsRetreat from "../../../../map/updateUnitsRetreat";

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
    }

    Object.entries(orders).forEach(([id, value]) => {
      if (value.status === "Complete") {
        state.ordersMeta[id].saved = true;
      }
    });
  }

  updateUnitsRetreat(state);
}
