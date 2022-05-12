import * as React from "react";
import { Box, Stack } from "@mui/material";
import { GameState } from "../../state/interfaces/GameState";
import WDDeleteTracker from "./WDDeleteTracker";
import WDDeleteReminder from "./WDDeleteReminder";

interface WDNotificationPanelProps {
  notifications: GameState["notifications"];
}

const WDNotificationPanel: React.FC<WDNotificationPanelProps> = function ({
  notifications,
}): React.ReactElement {
  return (
    <Stack>
      <Box>
        <WDDeleteTracker notifications={notifications} />
      </Box>
      <Box>
        <WDDeleteReminder notifications={notifications} />
      </Box>
    </Stack>
  );
};

export default WDNotificationPanel;
