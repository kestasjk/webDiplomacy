import GameOverviewResponse from "../../../../../state/interfaces/GameOverviewResponse";
import { GameState } from "../../../../../state/interfaces/GameState";
import getPhaseKey from "../../../getPhaseKey";

/* eslint-disable no-param-reassign */
export default function fetchGameOverviewFulfilled(
  state: GameState,
  action,
): void {
  console.log("fetchGameOverview succeeded");
  console.log({ action });
  state.apiStatus = "succeeded";
  state.outstandingOverviewRequests = false;
  const response: GameOverviewResponse = action.payload;
  if (response) {
    const oldPhaseKey = getPhaseKey(state.overview);
    const newPhaseKey = getPhaseKey(response);
    console.log({ oldPhaseKey, newPhaseKey });
    if (oldPhaseKey !== newPhaseKey) {
      state.needsGameData = true;
    }

  const oldPhaseKey = getPhaseKey(state.overview, "<BAD OVERVIEW_KEY>");
  const newPhaseKey = getPhaseKey(response, "<BAD OVERVIEW_KEY>");
  // console.log({ oldPhaseKey, newPhaseKey });
  if (oldPhaseKey !== newPhaseKey) {
    state.needsGameData = true;
    state.overview = response;
  }
}
