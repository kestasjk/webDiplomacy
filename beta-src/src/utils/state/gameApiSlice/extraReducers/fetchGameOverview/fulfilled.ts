import getCurrentUnixTimestamp from "../../../../getCurrentUnixTimestamp";
import memberActivityFrequencyMultiplier from "../../../memberActivityFrequencyMultiplier";

/* eslint-disable no-param-reassign */
export default function fetchGameOverviewFulfilled(state, action): void {
  state.apiStatus = "succeeded";
  state.overview = action.payload;
  state.activity.makeNewCall = false;
  const {
    processTime,
    members,
    phase,
    user: {
      member: { supplyCenterNo, unitNo },
    },
  } = action.payload;
  if (processTime) {
    const membersPlaying = members.filter(({ status }) => status === "Playing");
    const now = getCurrentUnixTimestamp();
    const tenMinutes = 600;
    const twoMinutes = 120;
    let frequency = twoMinutes;
    const timeLeft = processTime - now;
    if (timeLeft <= tenMinutes) {
      frequency = 30;
    }
    let membersReady = 0;
    let membersOnline = 0;
    membersPlaying.forEach(({ orderStatus, online }) => {
      membersReady += +orderStatus.Ready;
      membersOnline += +online;
    });
    const membersPlayingLength = membersPlaying.length;
    frequency = memberActivityFrequencyMultiplier(
      membersOnline,
      membersPlayingLength,
      frequency,
    );
    frequency = memberActivityFrequencyMultiplier(
      membersReady,
      membersPlayingLength,
      frequency,
    );
    if (frequency < 10) {
      frequency = 10;
    }
    state.activity.frequency = frequency;
  }
  if (phase === "Builds" && unitNo > supplyCenterNo) {
    state.mustDestroyUnitsBuildPhase = true;
  }
}
