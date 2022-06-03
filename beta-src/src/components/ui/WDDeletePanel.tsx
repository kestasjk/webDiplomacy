import * as React from "react";
import { Stack } from "@mui/material";
import { GameState } from "../../state/interfaces/GameState";
import WDNotification from "./WDNotification";
import GameNotification from "../../state/interfaces/GameNotification";

interface WDDeletePanelProps {
  notifications: GameNotification[];
}

const WDDeletePanel: React.FC<WDDeletePanelProps> = function ({
  notifications,
}): React.ReactElement {
  const [deleteTracker, deleteReminder] = notifications;
  return (
    <Stack direction="column">
      <WDNotification notification={deleteTracker} />
      <WDNotification notification={deleteReminder} />
    </Stack>
  );
};

export default WDDeletePanel;
