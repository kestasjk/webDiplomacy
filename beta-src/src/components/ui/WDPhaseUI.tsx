import * as React from "react";
import { Box } from "@mui/material";
import {
  gameOrdersMeta,
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

const getCurPhaseMinutes = function (phaseMinutes, phaseMinutesRB, phase) {
  if (phaseMinutesRB !== -1 && (phase === "Retreats" || phase === "Builds")) {
    return phaseMinutesRB;
  }
  return phaseMinutes;
};

const WDPhaseUI: React.FC = function (): React.ReactElement {
  const { phaseMinutes, phaseMinutesRB, processTime, phase, season, year } =
    useAppSelector(gameOverview);
  const gameStatusData = useAppSelector(gameStatus);
  const { viewedPhaseIdx, latestPhaseViewed } = useAppSelector(gameViewedPhase);

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
  const ordersMeta = useAppSelector(gameOrdersMeta);
  const ordersMetaValues = Object.values(ordersMeta);
  const ordersLength = ordersMetaValues.length;
  const ordersSaved = ordersMetaValues.reduce(
    (acc, meta) => acc + +meta.saved,
    0,
  );
  const animateForwardGlow =
    latestPhaseViewed < gameStatusData.phases.length - 1 ||
    ordersSaved !== ordersLength;

  // On the very last phase of a finished game, webdip API might give an
  // entirely erroneous year/season/phase. So instead, trust the one in the
  // overview.
  if (viewedPhaseIdx === gameStatusData.phases.length - 1) {
    viewedPhase = gamePhase;
    viewedSeason = gameSeason;
    viewedYear = gameYear;
  }

  const gameIsFinished = gamePhase === "Finished";

  return (
    <Box
      sx={{
        display: "flex",
        flexWrap: "wrap",
        webkitFlexWrap: "wrap",
        alignItems: "center",
        height: 40,
        marginTop: "3px",
        marginRight: "55px", // for right side icons
        pointerEvents: "none", // this box is invisible and used for layout alone, it shouldn't mask out clicks behind it
      }}
    >
      <WDPillScroller
        backwardDisabled={viewedPhaseIdx === 0}
        forwardDisabled={viewedPhaseIdx === gameStatusData.phases.length - 1}
        animateForwardGlow={animateForwardGlow}
        viewedPhase={viewedPhase}
        viewedSeason={viewedSeason}
        viewedYear={viewedYear}
      />
      {processTime && !gameIsFinished && (
        <WDCountdownPill
          endTime={processTime}
          phaseTime={phaseSeconds}
          viewedPhase={viewedPhase}
          viewedSeason={viewedSeason}
          viewedYear={viewedYear}
          gamePhase={gamePhase}
          gameSeason={gameSeason}
          gameYear={gameYear}
        />
      )}
    </Box>
  );
};

export default WDPhaseUI;
