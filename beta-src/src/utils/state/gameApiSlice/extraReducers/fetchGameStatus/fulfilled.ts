import { current } from "@reduxjs/toolkit";
import GameDataResponse from "../../../../../state/interfaces/GameDataResponse";
import GameOverviewResponse from "../../../../../state/interfaces/GameOverviewResponse";
import { GameState } from "../../../../../state/interfaces/GameState";

/* eslint-disable no-param-reassign */
export default function fetchGameStatusFulfilled(
  state: GameState,
  action,
): void {
  // console.log("fetchGameStatusFulfilled");
  state.apiStatus = "succeeded";
  // If the user is scrolled to the current phase, make the viewed
  // phase track the current phase
  if (state.viewedPhaseState.viewedPhaseIdx >= state.status.phases.length - 1) {
    state.viewedPhaseState.viewedPhaseIdx = action.payload.phases.length - 1;
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
