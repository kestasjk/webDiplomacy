import * as React from "react";
import { Box } from "@mui/material";
import { GameState } from "../../state/interfaces/GameState";

interface WDDeleteTrackerProps {
  notifications: GameState["notifications"];
}

const WDDeleteTracker: React.FC<WDDeleteTrackerProps> = function ({
  notifications,
}): React.ReactElement {
  return <Box sx={notifications[0].style}>{notifications[0].message}</Box>;
};

export default WDDeleteTracker;
