import {
  fetchGameStatus,
  loadGameData,
} from "../../../../../state/game/game-api-slice";
import { useAppDispatch } from "../../../../../state/hooks";
import GameOverviewResponse from "../../../../../state/interfaces/GameOverviewResponse";
import { GameState } from "../../../../../state/interfaces/GameState";
import memberActivityFrequencyMultiplier from "../../../memberActivityFrequencyMultiplier";
import fetchGameStatusFulfilled from "../fetchGameStatus/fulfilled";

/* eslint-disable no-param-reassign */
export default function fetchGameOverviewFulfilled(
  state: GameState,
  action,
): void {
  state.apiStatus = "succeeded";
  state.activity.makeNewCall = false;
  const response: GameOverviewResponse = action.payload;
  const { processTime, members, user, gameID } = response;

  state.overview = action.payload;

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
    state.activity.processTime = processTime;
  }
}
