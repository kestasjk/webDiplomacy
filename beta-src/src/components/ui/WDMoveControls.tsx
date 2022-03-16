import * as React from "react";
import { Stack } from "@mui/material";
import WDButton from "./WDButton";
import MoveStatus from "../../types/MoveStatus";
import Move from "../../enums/Move";

interface WDMoveControlsProps {
  gameState: MoveStatus;
  toggleState: (move: Move) => void;
}

const WDMoveControls: React.FC<WDMoveControlsProps> = function ({
  gameState: { ready },
  toggleState,
}): React.ReactElement {
  return (
    <Stack alignItems="center" direction="row" spacing={2}>
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
