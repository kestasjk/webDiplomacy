import * as React from "react";
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
  const { viewedPhaseIdx, latestPhaseViewed } = useAppSelector(gameViewedPhase);

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
  const animateForwardGlow =
    latestPhaseViewed < gameStatusData.phases.length - 1;

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
