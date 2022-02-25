import * as React from "react";
import { Box, ButtonGroup } from "@mui/material";
import WDScrollButton from "./WDScrollButton";
import { gameStateProps } from "../../interfaces/PhaseScroll";
import { ScrollButtonState } from "../../enums/UIState";

const WDPillScroller: React.FC<gameStateProps> = function ({
  disabled = false,
  gameState,
  onChangeSeason,
}): React.ReactElement {
  return (
    <Box
      sx={{ alignItems: "center", display: "flex" }}
      style={{ filter: "drop-shadow(0px 8px 9px black)" }}
    >
      <ButtonGroup>
        <WDScrollButton
          direction={ScrollButtonState.BACK}
          disabled={disabled}
        />
        <Box
          sx={{
            alignItems: "center",
            bgcolor: "white",
            display: "flex",
            fontWeight: "bold",
            padding: "5px",
            textTransform: "uppercase",
          }}
        >
          {gameState.currentSeason}
        </Box>
        <WDScrollButton
          direction={ScrollButtonState.FORWARD}
          disabled={disabled}
        />
      </ButtonGroup>
    </Box>
  );
};

export default WDPillScroller;
