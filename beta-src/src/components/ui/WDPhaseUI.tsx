import * as React from "react";
import {
  gameOverview,
  gameStatus,
  gameViewedPhase,
} from "../../state/game/game-api-slice";
import { useAppSelector } from "../../state/hooks";
import {
  getGamePhaseSeasonYear,
  getHistoricalPhaseSeasonYear,
} from "../../utils/state/getPhaseSeasonYear";
import WDCountdownPill from "./WDCountdownPill";
import WDPillScroller from "./WDPillScroller";
import { abbrMap } from "../../enums/Country";

const getCurPhaseMinutes = function (phaseMinutes, phaseMinutesRB, phase) {
  if (phaseMinutesRB !== -1 && (phase === "Retreats" || phase === "Builds")) {
    return phaseMinutesRB;
  }
  return phaseMinutes;
};

const WDPhaseUI: React.FC = function (): React.ReactElement {
  const {
    phaseMinutes,
    phaseMinutesRB,
    processTime,
    phase,
    season,
    year,
    processStatus,
    pauseTimeRemaining,
    user,
  } = useAppSelector(gameOverview);
  const gameStatusData = useAppSelector(gameStatus);
  const { viewedPhaseIdx } = useAppSelector(gameViewedPhase);

  const phaseSeconds =
    getCurPhaseMinutes(phaseMinutes, phaseMinutesRB, phase) * 60;

  const {
    phase: gamePhase,
    season: gameSeason,
    year: gameYear,
  } = getGamePhaseSeasonYear(phase, season, year);
  let {
    phase: viewedPhase,
    season: viewedSeason,
    year: viewedYear,
  } = getHistoricalPhaseSeasonYear(gameStatusData, viewedPhaseIdx);

  // On the very last phase of a finished game, webdip API might give an
  // entirely erroneous year/season/phase. So instead, trust the one in the
  // overview.
  if (viewedPhaseIdx === gameStatusData.phases.length - 1) {
    viewedPhase = gamePhase;
    viewedSeason = gameSeason;
    viewedYear = gameYear;
  }

  const gameIsFinished = gamePhase === "Finished";
  const seconds = Math.floor(new Date().getTime() / 1000);

  return (
    <div className="flex flex-col space-y-2">
      {user && (
        <div
          className={`py-1 px-2 rounded-md w-fit font-medium select-none text-white bg-${user.member.country.toLocaleLowerCase()}-main`}
        >
          {/* title={`Currently playing as ${user.member.country}`} */}
          {abbrMap[user.member.country]}
          <div className="hidden bg-france-main bg-austria-main bg-england-main bg-germany-main bg-russia-main bg-italy-main bg-turkey-main" />
        </div>
      )}
      <WDPillScroller
        country={user?.member.country || ""}
        viewedSeason={viewedSeason}
        viewedYear={viewedYear}
      />
      {(processTime || pauseTimeRemaining) && !gameIsFinished && (
        <WDCountdownPill
          endTime={processTime || (pauseTimeRemaining || 0) + seconds}
          phaseTime={phaseSeconds}
          viewedPhase={viewedPhase}
          viewedSeason={viewedSeason}
          viewedYear={viewedYear}
          gamePhase={gamePhase}
          gameSeason={gameSeason}
          gameYear={gameYear}
          isPaused={processStatus === "Paused" || false}
        />
      )}
    </div>
  );
};

export default WDPhaseUI;

// bgcolor: theme.palette[user.member.country]?.light,
