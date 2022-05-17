import { current } from "@reduxjs/toolkit";

/* eslint-disable no-param-reassign */
export default function updateUserActivity(state, action): void {
  const {
    activity: { season, year, processTime, lastCall, frequency },
    overview: { season: newSeason, year: newYear, processTime: newProcessTime },
  } = current(state);
  const { lastActive } = action.payload;
  if (
    lastCall === 0 ||
    season !== newSeason ||
    year !== newYear ||
    processTime !== newProcessTime
  ) {
    state.activity.season = newSeason;
    state.activity.year = newYear;
    state.activity.processTime = newProcessTime;
  }
  if (lastActive >= lastCall + frequency) {
    state.activity.lastCall = lastActive;
    state.activity.makeNewCall = true;
  } else {
    state.activity.makeNewCall = false;
  }
  state.activity.lastActive = lastActive;
}
