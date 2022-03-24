import * as React from "react";
import { useState, useEffect } from "react";
import { Box } from "@mui/material";
import {
  fetchGameOverview,
  gameOverview,
} from "../../state/game/game-api-slice";
import { useAppDispatch, useAppSelector } from "../../state/hooks";
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
  const dispatch = useAppDispatch();

  let currentGameID;
  const phaseTime = phaseMinutes * 60;
  const endTime = Math.floor(Date.now() / 1000) + phaseMinutes * 60;

  const showPillTimer = () => {
    setTimerDisplayState(!timerDisplayState);
  };

  useEffect(() => {
    const urlParams = new URLSearchParams(window.location.search);
    currentGameID = urlParams.get("gameID");
  }, []);

  useEffect(() => {
    dispatch(fetchGameOverview({ gameID: currentGameID as string }));
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
