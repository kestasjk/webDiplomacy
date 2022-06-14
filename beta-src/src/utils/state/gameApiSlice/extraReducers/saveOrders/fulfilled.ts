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
import {
  handlePostSucceeded,
  handlePostFailed,
} from "../handleSucceededFailed";

/* eslint-disable no-param-reassign */
export function saveOrdersPending(state: GameState, action): void {
  const queryParams = action.meta.arg as OrderSubmission;
  state.savingOrdersInProgress = queryParams.userIntent;
}

export function saveOrdersCommon(state: GameState, action): void {
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
      let alertMessage;
      if (notice) {
        alertMessage = `Error saving orders: ${notice}`;
      } else {
        alertMessage = `Unknown error saving orders, server indicated that API call was invalid`;
      }
      handlePostFailed(state, alertMessage);
    } else {
      handlePostSucceeded(state);
    }
  } else {
    const alertMessage = `Unknown error saving orders, server indicated that API call was invalid`;
    handlePostFailed(state, alertMessage);
  }
}

export function saveOrdersFulfilled(state: GameState, action): void {
  saveOrdersCommon(state, action);
}

export function saveOrdersRejected(state: GameState, action): void {
  saveOrdersCommon(state, action);
}
