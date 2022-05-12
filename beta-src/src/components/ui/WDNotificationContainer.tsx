import * as React from "react";
import { Stack } from "@mui/material";
// import { useAppSelector } from "../../state/hooks";
// import { gameNotifications } from "../../state/game/game-api-slice";
import WDNotificationPanel from "./WDNotificationPanel";
import { GameState } from "../../state/interfaces/GameState";

interface WDNotificationContainerProps {
  notifications: GameState["notifications"];
}

const WDNotificationContainer: React.FC<WDNotificationContainerProps> =
  function ({ notifications }): React.ReactElement {
    // const notifications = useAppSelector(gameNotifications);
    return (
      <Stack direction="column">
        <WDNotificationPanel notifications={notifications} />
      </Stack>
    );
  };

export default WDNotificationContainer;
