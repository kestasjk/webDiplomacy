import * as React from "react";
import { Stack } from "@mui/material";
import { useAppSelector } from "../../state/hooks";
import { gameNotifications } from "../../state/game/game-api-slice";
import WDDeletePanel from "./WDDeletePanel";

const WDNotificationContainer: React.FC = function (): React.ReactElement {
  const notifications = useAppSelector(gameNotifications);
  return (
    <Stack direction="column">
      {/* Notifications[0] will have a value if there player is in build phase and needs to destroy units */}
      {notifications[0] ? (
        <WDDeletePanel notifications={notifications} />
      ) : null}
    </Stack>
  );
};

export default WDNotificationContainer;
