import * as React from "react";
import { useState, useEffect } from "react";
import { Box } from "@mui/material";
import { fetchGameOverview } from "../../state/game/game-api-slice";
import { useAppDispatch, useAppSelector } from "../../state/hooks";
import Season from "../../enums/Season";
import UIState from "../../enums/UIState";
import WDCountdownPill from "./WDCountdownPill";
import WDGamePhaseIcon from "../svgr-components/WDGamePhaseIcon";
import WDPillScroller from "./WDPillScroller";

const WDPhaseUI: React.FC = function (): React.ReactElement {
  const [season, setSeason] = useState<[Season, string]>([
    Season.SPRING,
    "1901",
  ]);
  const [timerDisplayState, setTimerDisplayState] = useState<boolean>(true);

  const dispatch = useAppDispatch();
  const currentSeason = useAppSelector((state) => state.game.overview.date);
  const phaseMinutes = useAppSelector(
    (state) => state.game.overview.phaseMinutes,
  );

  const phaseTime = phaseMinutes * 60;
  const endTime = Math.floor(Date.now() / 1000) + phaseMinutes * 60;

  const formatDate = (date) => {
    const splitDate = date.split(",");
    setSeason(splitDate);
  };
  const showPillTimer = () => {
    setTimerDisplayState(!timerDisplayState);
  };

  useEffect(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const currentGameID = urlParams.get("gameID");
    dispatch(fetchGameOverview({ gameID: currentGameID as string }));
    formatDate(currentSeason);
  }, [currentSeason]);

  return (
    <Box sx={{ display: "flex" }}>
      <WDGamePhaseIcon
        icon={timerDisplayState ? season[0] : UIState.ACTIVE}
        onClick={showPillTimer}
        year={season[1]}
      />
      {timerDisplayState ? (
        <WDCountdownPill endTime={endTime} phaseTime={phaseTime} />
      ) : (
        <WDPillScroller season={season} />
      )}
    </Box>
  );
};

export default WDPhaseUI;
