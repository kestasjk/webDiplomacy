import * as React from "react";
import { Stack } from "@mui/material";
import { useAppSelector } from "../../state/hooks";
import { gameNotifications } from "../../state/game/game-api-slice";
import WDDeletePanel from "./WDDeletePanel";

const WDNotificationContainer: React.FC = function (): React.ReactElement {
  const notifications = useAppSelector(gameNotifications);
  // Notifications[0] will have a value if there player is in build phase and needs to destroy units
  // Currently, indexes 0 and 1 are being used for delete ui purposes but index 1 may or may not be
  // in use depending on the current state of orders
  const { 0: deleteTracker } = notifications;
  return (
    <Stack direction="column">
      {deleteTracker ? <WDDeletePanel notifications={notifications} /> : null}
    </Stack>
  );
};

export default WDNotificationContainer;
