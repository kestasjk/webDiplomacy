import * as React from "react";
import {
  gameAlert,
  gameApiSliceActions,
} from "../../state/game/game-api-slice";
import { useAppDispatch, useAppSelector } from "../../state/hooks";

const WDAlertModal: React.FC = function () {
  const alert = useAppSelector(gameAlert);

  const dispatch = useAppDispatch();
  const hideAlert = () => {
    dispatch(gameApiSliceActions.hideAlert(null));
  };

  React.useEffect(() => {
    // We could fade it on a timer, or just force the user
    // to click it to make it go away.
    const timer = setTimeout(() => {
      hideAlert();
    }, 5000);
    return () => clearTimeout(timer);
  }, [alert]);

  return (
    // eslint-disable-next-line jsx-a11y/click-events-have-key-events, jsx-a11y/no-static-element-interactions
    <div
      className={`absolute top-1/2 md:top-2 -translate-y-1/2 md:-translate-y-0 left-[50%] -translate-x-1/2 p-3 bg-red-300 rounded-lg bg-opacity-80 text-lg font-semibold text-center z-30 ${
        alert.visible ? "fade-in" : "fade-out"
      }`}
      onClick={hideAlert}
    >
      {alert.message}
    </div>
  );
};

export default WDAlertModal;
