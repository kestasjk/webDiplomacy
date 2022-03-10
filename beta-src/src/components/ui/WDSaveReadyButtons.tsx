import * as React from "react";

import { Stack } from "@mui/material";
import WDButton from "./WDButton";
import SaveStatus from "../../types/saveStatus";

interface saveReadyProps {
  gameState: SaveStatus;
  setGameState: React.Dispatch<React.SetStateAction<SaveStatus>>;
}
const WDSaveReadyButtons: React.FC<saveReadyProps> = function ({
  gameState,
  setGameState,
}) {
  const { save, ready } = gameState;

  return (
    <Stack direction="row" spacing={2} alignItems="center">
      <WDButton
        color="primary"
        disabled={ready}
        onClick={() => {
          setGameState((preState) => ({
            ...preState,
            save: !save,
          }));
        }}
      >
        Save
      </WDButton>
      <WDButton
        color="primary"
        onClick={() => {
          setGameState((preState) => ({
            ...preState,
            ready: !ready,
          }));
        }}
      >
        {ready ? "Unready" : "Ready"}
      </WDButton>
    </Stack>
  );
};

export default WDSaveReadyButtons;
