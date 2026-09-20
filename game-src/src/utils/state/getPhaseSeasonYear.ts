import Season from "../../enums/Season";
import GameStatusResponse from "../../state/interfaces/GameStatusResponse";
import { GamePhaseType } from "../../models/enums";
import { PhaseSeasonYear } from "../../interfaces/PhaseSeasonYear";

export function getGamePhaseSeasonYear(
  webdipPhase: string,
  webdipSeason: string,
  webdipYear: number,
): PhaseSeasonYear {
  const season =
    webdipPhase === GamePhaseType.Builds
      ? Season.WINTER
      : (webdipSeason as Season);
  return { phase: webdipPhase, season, year: webdipYear };
}

export function getPhaseSeasonYear(
  turn: number,
  phase: string,
): PhaseSeasonYear {
  const year = Math.floor(turn / 2) + 1901;
  let season: Season;
  if (phase === GamePhaseType.Builds) season = Season.WINTER;
  else if (turn % 2 === 0) season = Season.SPRING;
  else season = Season.AUTUMN;
  return { phase, season, year };
}

export function getHistoricalPhaseSeasonYear(
  gameStatus: GameStatusResponse,
  phaseIdx: number,
): PhaseSeasonYear {
  if (phaseIdx < 0 || phaseIdx >= gameStatus.phases.length) {
    return {
      phase: GamePhaseType.Diplomacy,
      season: Season.SPRING,
      year: 1901,
    };
  }
  const phaseData = gameStatus.phases[phaseIdx];
  return getPhaseSeasonYear(phaseData.turn, phaseData.phase);
}
