import { Box, Button, Stack } from "@mui/material";
import * as React from "react";
import GameOverviewResponse from "../../state/interfaces/GameOverviewResponse";
import formatPhaseForDisplay from "../../utils/formatPhaseForDisplay";

const centeredStyle = {
  position: "absolute",
  top: "50%",
  left: "50%",
  // justifyContent: "center",
  // alignItems: "center",
  transform: "translate(-50%, -50%)",
  backgroundColor: "rgba(255,255,255,1)",
  p: "10px",
  borderRadius: "5px",
};

const overlayStyle = {
  position: "absolute",
  left: 0,
  top: 0,
  height: "100%",
  width: "100%",
  backgroundColor: "rgba(52,52,52,0.6)",
};

interface WDGameProgressOverlayProps {
  overview: GameOverviewResponse;
  clickHandler: () => void;
}

const WDGameProgressOverlay: React.FC<WDGameProgressOverlayProps> = function ({
  overview,
  clickHandler,
}) {
  let innerElem;
  if (["Diplomacy", "Retreats", "Builds"].includes(overview.phase)) {
    innerElem = (
      <Stack direction="column" alignItems="center">
        <Box sx={{ m: "4px" }}>Game progressed to a new phase...</Box>
        <Button
          size="large"
          variant="contained"
          color="success"
          onClick={clickHandler}
        >
          View {overview.season} {overview.year}{" "}
          {formatPhaseForDisplay(overview.phase)}
        </Button>
      </Stack>
    );
  } else if (overview.phase === "Pre-game") {
    innerElem = (
      <Box>
        <b>Pre-game:</b> Game is waiting to start
      </Box>
    );
  } else if (overview.phase === "Error") {
    innerElem = <Box>Could not load game. You may need to join this game.</Box>;
  } else {
    innerElem = <Box>Game phase is {overview.phase}!</Box>;
  }
  return (
    <>
      <Box sx={overlayStyle} />
      <Box sx={centeredStyle}>{innerElem}</Box>
    </>
  );
};

export default WDGameProgressOverlay;
