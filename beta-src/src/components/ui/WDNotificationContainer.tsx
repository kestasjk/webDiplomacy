import * as React from "react";
import { Stack } from "@mui/material";
import { useAppSelector } from "../../state/hooks";
import { gameNotifications } from "../../state/game/game-api-slice";
import WDDeletePanel from "./WDDeletePanel";

const WDNotificationContainer: React.FC = function (): React.ReactElement {
  const notifications = useAppSelector(gameNotifications);
  if (notifications.length !== 0) {
    return (
      <Stack direction="column">
        <WDDeletePanel notifications={notifications} />
      </Stack>
    );
  }
  // eslint-disable-next-line react/jsx-no-useless-fragment
  return <></>;
};

export default WDNotificationContainer;
