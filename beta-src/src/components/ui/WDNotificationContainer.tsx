import * as React from "react";
import { Stack } from "@mui/material";
import { useAppSelector } from "../../state/hooks";
import {
  gameNotifications,
  gameOrdersMeta,
} from "../../state/game/game-api-slice";
import WDDeletePanel from "./WDDeletePanel";
import GameOverviewResponse from "../../state/interfaces/GameOverviewResponse";

interface WDNotificationContainerProps {
  phase: GameOverviewResponse["phase"];
}

const WDNotificationContainer: React.FC<WDNotificationContainerProps> =
  function ({ phase }): React.ReactElement {
    const notifications = useAppSelector(gameNotifications);
    const ordersMeta = useAppSelector(gameOrdersMeta);
    let type;
    Object.values(ordersMeta).forEach(({ update }) => {
      if (update?.type === "Destroy") type = "Destroy";
    });
    // Calls WDDeletePanel when the type is Destroy and the phase is builds
    // Can use the above check for future build notifications and any phase
    // that might have more than one type. This along with phase should be
    // sufficient check to render the correct panel.
    return (
      <Stack direction="column">
        {phase === "Builds" && type === "Destroy" ? (
          <WDDeletePanel notifications={notifications} />
        ) : null}
      </Stack>
    );
  };

export default WDNotificationContainer;
