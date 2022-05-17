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
  // The 0 and 1 index are for delete notifications. Future notification panels will
  // need to use whichever indexes are used in their respective write____Notifications.ts
  const { 0: deleteTracker, 1: deleteReminder } = notifications;
  return (
    <Stack direction="column">
      <WDNotification notification={deleteTracker} />
      <WDNotification notification={deleteReminder} />
    </Stack>
  );
};

export default WDDeletePanel;
