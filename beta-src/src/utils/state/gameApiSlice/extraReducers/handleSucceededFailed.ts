import { setAlert } from "../../../../state/interfaces/GameAlert";
import { GameState } from "../../../../state/interfaces/GameState";

/* eslint-disable no-param-reassign */
export function handlePostFailed(
  state: GameState,
  alertMessage?: string,
): void {
  state.apiStatus = "failed";
  // Show the user the message if there is one
  if (alertMessage) {
    setAlert(state.alert, alertMessage);
  }
  // In any error case where we attempted to modify the state of something
  // on the server, try reloading everything so that we can
  // attempt to resync with the server again.
  state.needsGameOverview = true;
  state.needsGameData = true;
}
export function handlePostSucceeded(state: GameState): void {
  state.apiStatus = "succeeded";
  // Also zero out the failure count on a successful post
  state.numConsecutiveGetFailures = 0;
}

export function handleGetFailed(state: GameState, action): void {
  state.apiStatus = "failed";
  if (action.error) {
    state.error = action.error.message;
  }
  // Count the number of consecutive failures, if it is too many, then
  // alert the user of network troubles
  state.numConsecutiveGetFailures += 1;
  if (state.numConsecutiveGetFailures > 3) {
    // Show the user the message
    setAlert(
      state.alert,
      "Cannot connect to server - you may need to reload page or internet is down",
    );
  }
}

export function handleGetSucceeded(state: GameState): void {
  state.apiStatus = "succeeded";
  state.numConsecutiveGetFailures = 0;
}
