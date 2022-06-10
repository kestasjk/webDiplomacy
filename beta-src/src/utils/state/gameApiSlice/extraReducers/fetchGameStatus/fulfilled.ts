import { current } from "@reduxjs/toolkit";
import GameDataResponse from "../../../../../state/interfaces/GameDataResponse";
import GameOverviewResponse from "../../../../../state/interfaces/GameOverviewResponse";
import { GameState } from "../../../../../state/interfaces/GameState";
import { handleGetSucceeded, handleGetFailed } from "../handleFulfillReject";

/* eslint-disable no-param-reassign */
export default function fetchGameStatusFulfilled(
  state: GameState,
  action,
): void {
  if (!action.payload) {
    handleGetFailed(state, action);
    return;
  }
  // console.log("fetchGameStatusFulfilled");
  handleGetSucceeded(state);
  // If this is the initial update, then jump to the most recent state upon load
  if (state.status.phases.length <= 0 && action.payload.phases.length > 0) {
    state.viewedPhaseState.viewedPhaseIdx = action.payload.phases.length - 1;
    state.viewedPhaseState.latestPhaseViewed = action.payload.phases.length - 1;
  }

  state.status = action.payload;
  const {
    data: { data },
    overview: { members },
  }: {
    data: { data: GameDataResponse["data"] };
    overview: {
      members: GameOverviewResponse["members"];
    };
  } = current(state);
}
