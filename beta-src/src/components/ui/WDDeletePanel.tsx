import * as React from "react";
import { Box, Stack } from "@mui/material";
import { GameState } from "../../state/interfaces/GameState";
import WDDeleteTracker from "./WDDeleteTracker";
import WDDeleteReminder from "./WDDeleteReminder";

interface WDDeletePanelProps {
  notifications: GameState["notifications"];
}

const WDDeletePanel: React.FC<WDDeletePanelProps> = function ({
  notifications,
}): React.ReactElement {
  return (
    <Stack direction="column">
      <Box>
        <WDDeleteTracker notifications={notifications} />
      </Box>
      <Box>
        <WDDeleteReminder notifications={notifications} />
      </Box>
    </Stack>
  );
};

export default WDDeletePanel;
