import * as React from "react";
import { Box } from "@mui/material";
import { GameState } from "../../state/interfaces/GameState";

const textLabelStyle = {
  borderRadius: 0,
  fontWeight: 400,
  minWidth: 0,
  p: "0 0 5px 0",
};

interface WDDeleteTrackerProps {
  notifications: GameState["notifications"];
}

const WDDeleteTracker: React.FC<WDDeleteTrackerProps> = function ({
  notifications,
}): React.ReactElement {
  return <Box sx={notifications[0].style}>{notifications[0].message}</Box>;
};

export default WDDeleteTracker;
