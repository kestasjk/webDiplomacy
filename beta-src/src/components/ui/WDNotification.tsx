import * as React from "react";
import GameNotification from "../../state/interfaces/GameNotification";

interface WDNotificationProps {
  notification: GameNotification;
}

const WDNotification: React.FC<WDNotificationProps> = function ({
  notification,
}): React.ReactElement {
  return (
    <div className="">
      {notification && (
        <div style={notification.style}>{notification.message}</div>
      )}
    </div>
  );
};

export default WDNotification;
