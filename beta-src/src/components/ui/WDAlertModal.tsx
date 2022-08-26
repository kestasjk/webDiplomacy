import { Box } from "@mui/material";
import * as React from "react";
import {
  gameAlert,
  gameApiSliceActions,
} from "../../state/game/game-api-slice";
import { useAppDispatch, useAppSelector } from "../../state/hooks";

const alertStyle = {
  position: "absolute",
  top: "15px",
  left: "50%",
  transform: "translate(-50%, 0)",
  backgroundColor: "rgba(255,200,200,0.85)",
  p: "20px",
  borderRadius: "10px",
  fontSize: 18,
  fontWeight: 700,
};

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
    <Box
      className={alert.visible ? "fade-in" : "fade-out"}
      sx={alertStyle}
      onClick={hideAlert}
    >
      {alert.message}
    </Box>
  );
};

export default WDAlertModal;
