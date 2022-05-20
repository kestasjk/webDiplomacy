import * as React from "react";
import { Stack } from "@mui/material";
import { useAppSelector } from "../../state/hooks";
import {
  gameNotifications,
  mustDestroyUnits,
} from "../../state/game/game-api-slice";
import WDDeletePanel from "./WDDeletePanel";

const WDNotificationContainer: React.FC = function (): React.ReactElement {
  const notifications = useAppSelector(gameNotifications);
  const destroyUnits = useAppSelector(mustDestroyUnits);
  // Calls WDDeletePanel when the type is Destroy and the phase is builds
  // Can use the above check for future build notifications and any phase
  // that might have more than one type. This along with phase should be
  // sufficient check to render the correct panel.
  return (
    <Stack direction="column">
      {destroyUnits && <WDDeletePanel notifications={notifications} />}
    </Stack>
  );
};

export default WDNotificationContainer;
