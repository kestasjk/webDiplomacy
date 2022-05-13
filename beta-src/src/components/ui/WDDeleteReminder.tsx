import * as React from "react";
import { Box } from "@mui/material";
import { GameState } from "../../state/interfaces/GameState";

interface WDDeleteReminderProps {
  notifications: GameState["notifications"];
}

const WDDeleteReminder: React.FC<WDDeleteReminderProps> = function ({
  notifications,
}): React.ReactElement {
  if (notifications[1] !== undefined) {
    return <Box sx={notifications[1].style}>{notifications[1].message}</Box>;
  }
  // eslint-disable-next-line react/jsx-no-useless-fragment
  return <></>;
};

export default WDDeleteReminder;
