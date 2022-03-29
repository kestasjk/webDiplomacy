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
      {timerDisplayState ? (
        <Box
          sx={{
            position: "relative",
            top: -5,
          }}
        >
          <WDCountdownPill endTime={endTime} phaseTime={phaseTime} />
        </Box>
      ) : (
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
