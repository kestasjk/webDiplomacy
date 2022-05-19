import * as React from "react";
import { Stack } from "@mui/material";
import { useAppSelector } from "../../state/hooks";
import {
  gameNotifications,
  gameOverview,
} from "../../state/game/game-api-slice";
import WDDeletePanel from "./WDDeletePanel";
import GameOverviewResponse from "../../state/interfaces/GameOverviewResponse";

interface WDNotificationContainerProps {
  phase: GameOverviewResponse["phase"];
}

const WDNotificationContainer: React.FC<WDNotificationContainerProps> =
  function ({ phase }): React.ReactElement {
    const notifications = useAppSelector(gameNotifications);
    const {
      user: {
        member: { supplyCenterNo, unitNo },
      },
    }: {
      // definition
      user: {
        member: {
          supplyCenterNo: GameOverviewResponse["user"]["member"]["supplyCenterNo"];
          unitNo: GameOverviewResponse["user"]["member"]["unitNo"];
        };
      };
    } = useAppSelector(gameOverview);
    // Calls WDDeletePanel when the type is Destroy and the phase is builds
    // Can use the above check for future build notifications and any phase
    // that might have more than one type. This along with phase should be
    // sufficient check to render the correct panel.
    return (
      <Stack direction="column">
        {phase === "Builds" && supplyCenterNo < unitNo ? (
          <WDDeletePanel notifications={notifications} />
        ) : null}
      </Stack>
    );
  };

export default WDNotificationContainer;
