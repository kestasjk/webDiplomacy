import GameOverviewResponse from "../../../../../state/interfaces/GameOverviewResponse";
import { GameState } from "../../../../../state/interfaces/GameState";
import getPhaseKey from "../../../getPhaseKey";
import { handleGetSucceeded, handleGetFailed } from "../handleSucceededFailed";

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
  // probably we only need to look at processTime, not phase key. But I'm leaving in the
  // phase key to be safe (since this is the historical way we did it).
  if (
    oldPhaseKey !== newPhaseKey ||
    state.overview.processTime !== response.processTime
  ) {
    state.needsGameData = true;
  }
  state.overview = response;
}
