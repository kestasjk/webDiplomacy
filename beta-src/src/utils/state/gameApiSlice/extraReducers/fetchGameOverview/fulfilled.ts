/* eslint-disable no-param-reassign */
export default function fetchGameOverviewFulfilled(state, action): void {
  state.apiStatus = "succeeded";
  state.overview = action.payload;
  state.activity.makeNewCall = false;
  const { processTime, members } = action.payload;
  if (processTime) {
    const membersPlaying = members.filter(({ status }) => status === "Playing");
    // eslint-disable-next-line no-bitwise
    const now = (Date.now() / 1000) | 0;
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
    if (membersOnline) {
      let percentageOnline = membersOnline / membersPlayingLength;
      if (percentageOnline === 1) {
        percentageOnline = 0.9;
      }
      frequency -= percentageOnline * frequency;
    }
    if (membersReady) {
      let percentageReady = membersReady / membersPlayingLength;
      if (percentageReady === 1) {
        percentageReady = 0.9;
      }
      frequency -= percentageReady * frequency;
    }
    if (frequency < 10) {
      frequency = 10;
    }
    state.activity.frequency = frequency;
  }
}
