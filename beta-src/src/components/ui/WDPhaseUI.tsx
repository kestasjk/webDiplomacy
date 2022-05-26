import * as React from "react";
import { useState, useEffect } from "react";
import { Box } from "@mui/material";
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

const WDPhaseUI: React.FC = function (): React.ReactElement {
  const { phaseMinutes, processTime, phase, season, year } =
    useAppSelector(gameOverview);
  const gameStatusData = useAppSelector(gameStatus);
  const { viewedPhaseIdx } = useAppSelector(gameViewedPhase);

  const phaseSeconds = phaseMinutes * 60;
  const [gamePhase, gameSeason, gameYear] = getGamePhaseSeasonYear(
    phase,
    season,
    year,
  );
  const [viewedPhase, viewedSeason, viewedYear] = getHistoricalPhaseSeasonYear(
    gameStatusData,
    viewedPhaseIdx,
  );

  return (
    <Box
      sx={{
        display: "flex",
        alignItems: "center",
        height: 40,
        marginTop: "3px",
      }}
    >
      <WDPillScroller
        backwardDisabled={viewedPhaseIdx === 0}
        forwardDisabled={viewedPhaseIdx === gameStatusData.phases.length - 1}
        viewedPhase={viewedPhase}
        viewedSeason={viewedSeason}
        viewedYear={viewedYear}
      />
      {processTime && (
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
