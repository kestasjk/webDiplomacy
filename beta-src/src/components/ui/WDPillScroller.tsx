import * as React from "react";
import { Box, ButtonGroup, useTheme } from "@mui/material";
import { useAppDispatch } from "../../state/hooks";
import WDScrollButton from "./WDScrollButton";
import ScrollButtonState from "../../enums/ScrollButton";
import Season from "../../enums/Season";
import { gameApiSliceActions } from "../../state/game/game-api-slice";
import WDGamePhaseIcon from "./icons/WDGamePhaseIcon";
import { formatPhaseForDisplay } from "../../utils/formatPhaseForDisplay";

interface WDPillScrollerProps {
  backwardDisabled: boolean;
  forwardDisabled: boolean;
  animateForwardGlow: boolean;
  viewedPhase: string;
  viewedSeason: Season;
  viewedYear: number;
}

const WDPillScroller: React.FC<WDPillScrollerProps> = function ({
  backwardDisabled,
  forwardDisabled,
  animateForwardGlow,
  viewedPhase,
  viewedSeason,
  viewedYear,
}): React.ReactElement {
  const theme = useTheme();
  const dispatch = useAppDispatch();
  return (
    <Box
      sx={{
        alignItems: "center",
        display: "flex",
        borderRadius: "22px",
        borderWidth: "0",
        filter: theme.palette.svg.filters.dropShadows[0],
        userSelect: "none",
      }}
    >
      <WDScrollButton
        className="WDScroll--Backward"
        direction={ScrollButtonState.BACKWARD}
        disabled={backwardDisabled}
        onClick={() => {
          dispatch(gameApiSliceActions.changeViewedPhaseIdxBy(-1));
        }}
      />
      <WDGamePhaseIcon
        icon={viewedSeason}
        year={viewedYear}
        phaseLabel={formatPhaseForDisplay(viewedPhase)}
      />
      <WDScrollButton
        className="WDScroll--Forward"
        direction={ScrollButtonState.FORWARD}
        disabled={forwardDisabled}
        doAnimateGlow={animateForwardGlow}
        onClick={() => {
          dispatch(gameApiSliceActions.changeViewedPhaseIdxBy(1));
        }}
      />
    </Box>
  );
};

export default WDPillScroller;
