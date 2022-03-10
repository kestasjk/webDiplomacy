import * as React from "react";

import { Stack } from "@mui/material";
import WDButton from "./WDButton";
import MoveStatus from "../../types/MoveStatus";

interface WDMoveControlsProps {
  gameState: MoveStatus;
  setState: (move: string) => void;
}
const WDMoveControls: React.FC<WDMoveControlsProps> = function ({
  gameState,
  setState,
}) {
  const { ready } = gameState;

  return (
    <Stack direction="row" spacing={2} alignItems="center">
      <WDButton
        color="primary"
        disabled={ready}
        onClick={() => setState("save")}
      >
        Save
      </WDButton>
      <WDButton color="primary" onClick={() => setState("ready")}>
        {ready ? "Unready" : "Ready"}
      </WDButton>
    </Stack>
  );
};

export default WDMoveControls;
