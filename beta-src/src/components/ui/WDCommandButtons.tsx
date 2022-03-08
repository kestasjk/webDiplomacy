import * as React from "react";
import { useState } from "react";
import { Stack } from "@mui/material";
import WDCheckmarkIcon from "../svgr-components/WDCheckmarkIcon";
import WDButton from "./WDButton";

/**
 * Define the vote props passed to the function.
 */
interface voteProps {
  voteDraw: boolean;
  votePause: boolean;
  voteCancel: boolean;
}

const WDCommandButtons: React.FC<voteProps> = function ({
  voteCancel,
  voteDraw,
  votePause,
}) {
  const [draw, setDraw] = useState(voteDraw);
  const [pause, setPause] = useState(votePause);
  const [cancel, setCancel] = useState(voteCancel);

  return (
    <Stack direction="row" spacing={2} alignItems="center">
      <WDButton
        color={draw ? "secondary" : "primary"}
        onClick={() => {
          setDraw(!draw);
        }}
        startIcon={draw ? <WDCheckmarkIcon /> : ""}
      >
        Draw
      </WDButton>
      <WDButton
        color={pause ? "secondary" : "primary"}
        onClick={() => {
          setPause(!pause);
        }}
        startIcon={pause ? <WDCheckmarkIcon /> : ""}
      >
        Pause
      </WDButton>
      <WDButton
        color={cancel ? "secondary" : "primary"}
        onClick={() => {
          setCancel(!cancel);
        }}
        startIcon={cancel ? <WDCheckmarkIcon /> : ""}
      >
        Cancel
      </WDButton>
    </Stack>
  );
};

export default WDCommandButtons;
