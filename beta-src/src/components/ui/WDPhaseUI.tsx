import * as React from "react";
import { useState, useEffect } from "react";
import { Box } from "@mui/material";
import { gameOverview } from "../../state/game/game-api-slice";
import { useAppSelector } from "../../state/hooks";
import GameOverviewResponse from "../../state/interfaces/GameOverviewResponse";
import Season from "../../enums/Season";
import UIState from "../../enums/UIState";
import WDCountdownPill from "./WDCountdownPill";
import WDGamePhaseIcon from "../svgr-components/WDGamePhaseIcon";
import WDPillScroller from "./WDPillScroller";

const WDPhaseUI: React.FC = function (): React.ReactElement {
  const [currentSeason, setCurrentSeason] = useState<Season>(Season.SPRING);
  const [currentYear, setCurrentYear] = useState<number>(1901);
  const [currentProcessTime, setCurrentProcessTime] =
    useState<GameOverviewResponse["processTime"]>(null);
  const [timerDisplayState, setTimerDisplayState] = useState(true);

  const { phaseMinutes, processTime, season, year } =
    useAppSelector(gameOverview);

  const showPillTimer = () => {
    setTimerDisplayState(!timerDisplayState);
  };

  const phaseSeconds = phaseMinutes * 60;

  useEffect(() => {
    setCurrentSeason(season as Season);
    setCurrentYear(year);
    setCurrentProcessTime(processTime);
  }, [processTime, season, year]);

  return (
    <Box
      sx={{
        display: "flex",
        alignItems: "center",
        height: 65,
        marginTop: "8px",
      }}
    >
      <WDGamePhaseIcon
        icon={timerDisplayState ? currentSeason : UIState.ACTIVE}
        onClick={showPillTimer}
        year={currentYear}
      />
      {timerDisplayState && currentProcessTime && (
        <Box
          sx={{
            position: "relative",
            top: -5,
          }}
        >
          <WDCountdownPill
            endTime={currentProcessTime}
            phaseTime={phaseSeconds}
          />
        </Box>
      )}
      {!timerDisplayState && (
        <Box
          sx={{
            position: "relative",
            top: -3,
          }}
        >
          <WDPillScroller season={currentSeason} year={currentYear} />
        </Box>
      )}
    </Box>
  );
};

export default WDPhaseUI;
