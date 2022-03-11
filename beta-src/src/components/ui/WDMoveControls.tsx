import * as React from "react";

import { Stack } from "@mui/material";
import WDButton from "./WDButton";
import MoveStatus from "../../types/MoveStatus";
import Move from "../../enums/Move";

interface WDMoveControlsProps {
  gameState: MoveStatus;
  setState: (move: Move) => void;
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
        onClick={() => setState(Move.SAVE)}
      >
        Save
      </WDButton>
      <WDButton color="primary" onClick={() => setState(Move.READY)}>
        {ready ? "Unready" : "Ready"}
      </WDButton>
    </Stack>
  );
};

export default WDMoveControls;
