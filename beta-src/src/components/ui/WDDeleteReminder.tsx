import * as React from "react";
import { Box } from "@mui/material";
import { GameState } from "../../state/interfaces/GameState";

const textLabelStyle = {
  borderRadius: 0,
  fontWeight: 400,
  minWidth: 0,
  p: "0 0 5px 0",
};

interface WDDeleteReminderProps {
  notifications: GameState["notifications"];
}

const WDDeleteReminder: React.FC<WDDeleteReminderProps> = function ({
  notifications,
}): React.ReactElement {
  return <Box sx={notifications[1].style}>{notifications[1].message}</Box>;
};

export default WDDeleteReminder;
