import * as React from "react";
import { Box } from "@mui/material";
import GameNotification from "../../state/interfaces/GameNotification";

interface WDNotificationProps {
  notification: GameNotification;
}

const WDNotification: React.FC<WDNotificationProps> = function ({
  notification,
}): React.ReactElement {
  return (
    <Box>
      {notification && (
        <Box sx={notification.style}>{notification.message}</Box>
      )}
    </Box>
  );
};

export default WDNotification;
