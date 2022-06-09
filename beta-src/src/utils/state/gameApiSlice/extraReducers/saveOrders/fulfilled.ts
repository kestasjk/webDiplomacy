import SavedOrdersConfirmation from "../../../../../interfaces/state/SavedOrdersConfirmation";
import {
  fetchGameOverview,
  gameApiSliceActions,
} from "../../../../../state/game/game-api-slice";
import { useAppDispatch } from "../../../../../state/hooks";
import { setAlert } from "../../../../../state/interfaces/GameAlert";
import getOrderStates from "../../../getOrderStates";
import { GameState } from "../../../../../state/interfaces/GameState";
import OrderSubmission from "../../../../../interfaces/state/OrderSubmission";

/* eslint-disable no-param-reassign */
export function saveOrdersPending(state: GameState, action): void {
  const queryParams = action.meta.arg as OrderSubmission;
  state.savingOrdersInProgress = queryParams.userIntent;
}

export function saveRejectOrdersCommon(state: GameState, action): void {
  state.savingOrdersInProgress = null;
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
      if (state.overview.user) {
        state.overview.user.member.orderStatus = {
          Completed: orderStates.Completed,
          Ready: orderStates.Ready,
          None: orderStates.None,
          Saved: orderStates.Saved,
          Hidden: false,
        };
      }
    }
    // console.log({ returnOrders: orders });
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
      // In any error case saving orders, try reloading everything so that we can
      // attempt to resync with the server again.
      state.needsGameOverview = true;
      state.needsGameData = true;
    }
  } else {
    setAlert(
      state.alert,
      `Unknown error saving orders, server indicated that API call was invalid`,
    );
    // In any error case, try reloading everything so that we can attempt to resync
    // with the server again.
    state.needsGameOverview = true;
    state.needsGameData = true;
  }
}

export function saveOrdersFulfilled(state: GameState, action): void {
  state.apiStatus = "succeeded";
  saveRejectOrdersCommon(state, action);
}

export function saveOrdersRejected(state, action): void {
  state.apiStatus = "failed";
  saveRejectOrdersCommon(state, action);
}
