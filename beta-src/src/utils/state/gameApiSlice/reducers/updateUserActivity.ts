import { current } from "@reduxjs/toolkit";

/* eslint-disable no-param-reassign */
export default function updateUserActivity(state, action): void {
  const {
    activity: { lastCall, frequency },
  } = current(state);
  const { lastActive } = action.payload;

  if (lastActive >= lastCall + frequency) {
    state.activity.lastCall = lastActive;
    state.activity.makeNewCall = true;
  } else {
    state.activity.makeNewCall = false;
  }
  state.activity.lastActive = lastActive;
}
