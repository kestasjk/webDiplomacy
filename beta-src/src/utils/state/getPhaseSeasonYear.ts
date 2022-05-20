import Season from "../../enums/Season";
import GameStatusResponse from "../../state/interfaces/GameStatusResponse";
import { GamePhaseType } from "../../models/enums";

export function getGamePhaseSeasonYear(
  webdipPhase: string,
  webdipSeason: string,
  webdipYear: number,
): [string, Season, number] {
  const season =
    webdipPhase === GamePhaseType.Builds
      ? Season.WINTER
      : (webdipSeason as Season);
  return [webdipPhase, season, webdipYear];
}

export function getHistoricalPhaseSeasonYear(
  gameStatus: GameStatusResponse,
  phaseIdx: number,
): [string, Season, number] {
  if (phaseIdx < 0 || phaseIdx >= gameStatus.phases.length) {
    return [GamePhaseType.Diplomacy, Season.SPRING, 1901];
  }

  const phaseData = gameStatus.phases[phaseIdx];
  const year = Math.floor(phaseData.turn / 2) + 1901;

  let season;
  if (phaseData.phase === GamePhaseType.Builds) season = Season.WINTER;
  else if (phaseData.turn % 2 === 0) season = Season.SPRING;
  else season = Season.AUTUMN;

  const { phase } = phaseData;
  return [phase, season, year];
}
