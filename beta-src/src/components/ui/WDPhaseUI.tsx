import * as React from "react";
import { useState, useEffect } from "react";
import { Box } from "@mui/material";
import { gameOverview } from "../../state/game/game-api-slice";
import { useAppSelector } from "../../state/hooks";
import convertPhaseMin from "../../utils/convertPhaseMin";
import Season from "../../enums/Season";
import UIState from "../../enums/UIState";
import WDCountdownPill from "./WDCountdownPill";
import WDGamePhaseIcon from "../svgr-components/WDGamePhaseIcon";
import WDPillScroller from "./WDPillScroller";

const WDPhaseUI: React.FC = function (): React.ReactElement {
  const [currentSeason, setCurrentSeason] = useState<Season>(Season.SPRING);
  const [currentYear, setCurrentYear] = useState<number>(1901);
  const [timerDisplayState, setTimerDisplayState] = useState(true);

  const { phaseMinutes, season, year } = useAppSelector(gameOverview);

  const phaseTime = phaseMinutes * 60; 
  const endTime = convertPhaseMin(phaseMinutes);

  const showPillTimer = () => {
    setTimerDisplayState(!timerDisplayState);
  };

  useEffect(() => {
    setCurrentSeason(season as Season);
    setCurrentYear(year);
  }, [season, year]);

  return (
    <Box sx={{ display: "flex" }}>
      <WDGamePhaseIcon
        icon={timerDisplayState ? currentSeason : UIState.ACTIVE}
        onClick={showPillTimer}
        year={currentYear}
      />
      {timerDisplayState ? (
        <WDCountdownPill endTime={endTime} phaseTime={phaseTime} />
      ) : (
        <WDPillScroller season={currentSeason} year={currentYear} />
      )}
    </Box>
  );
};

export default WDPhaseUI;
