import GameOverviewResponse from "../../../../../state/interfaces/GameOverviewResponse";
import { GameState } from "../../../../../state/interfaces/GameState";
import getPhaseKey from "../../../getPhaseKey";
import { handleGetSucceeded, handleGetFailed } from "../handleFulfillReject";

/* eslint-disable no-param-reassign */
export default function fetchGameOverviewFulfilled(
  state: GameState,
  action,
): void {
  state.outstandingOverviewRequests = false;
  if (!action.payload) {
    handleGetFailed(state, action);
    return;
  }
  handleGetSucceeded(state);

  const response: GameOverviewResponse = action.payload;
  const oldPhaseKey = getPhaseKey(state.overview, "<BAD OVERVIEW_KEY>");
  const newPhaseKey = getPhaseKey(response, "<BAD OVERVIEW_KEY>");
  // console.log({ oldPhaseKey, newPhaseKey });
  if (oldPhaseKey !== newPhaseKey) {
    state.needsGameData = true;
  }
  state.overview = response;
}
