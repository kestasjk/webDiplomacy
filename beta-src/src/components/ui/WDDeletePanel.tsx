import * as React from "react";
import { Box, Stack } from "@mui/material";
import { GameState } from "../../state/interfaces/GameState";
import WDNotification from "./WDNotification";

interface WDDeletePanelProps {
  notifications: GameState["notifications"];
}

const WDDeletePanel: React.FC<WDDeletePanelProps> = function ({
  notifications,
}): React.ReactElement {
  return (
    <Stack direction="column">
      <Box>
        <WDNotification notification={notifications[0]} />
      </Box>
      <Box>
        <WDNotification notification={notifications[1]} />
      </Box>
    </Stack>
  );
};

export default WDDeletePanel;
