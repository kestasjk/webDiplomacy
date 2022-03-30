import * as React from "react";
import { Stack } from "@mui/material";
import WDButton from "./WDButton";
import MoveStatus from "../../types/MoveStatus";
import Move from "../../enums/Move";
import useViewport from "../../hooks/useViewport";
import getDevice from "../../utils/getDevice";
import Device from "../../enums/Device";

interface WDMoveControlsProps {
  gameState: MoveStatus;
  toggleState: (move: Move) => void;
}

const WDMoveControls: React.FC<WDMoveControlsProps> = function ({
  gameState: { ready },
  toggleState,
}): React.ReactElement {
  const [viewport] = useViewport();
  const device = getDevice(viewport);
  let isMobile: boolean;
  switch (device) {
    case Device.MOBILE:
    case Device.MOBILE_LG:
    case Device.MOBILE_LANDSCAPE:
    case Device.MOBILE_LG_LANDSCAPE:
      isMobile = true;
      break;
    default:
      isMobile = false;
      break;
  }
  return (
    <Stack
      alignItems="center"
      direction={isMobile ? "column" : "row"}
      spacing={2}
    >
      <WDButton
        color="primary"
        disabled={ready}
        onClick={() => toggleState(Move.SAVE)}
      >
        Save
      </WDButton>
      <WDButton color="primary" onClick={() => toggleState(Move.READY)}>
        {ready ? "Unready" : "Ready"}
      </WDButton>
    </Stack>
  );
};

export default WDMoveControls;
