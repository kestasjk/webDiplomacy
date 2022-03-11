import * as React from "react";
import { Stack } from "@mui/material";
import WDCheckmarkIcon from "../svgr-components/WDCheckmarkIcon";
import WDButton from "./WDButton";
import VoteType from "../../types/Vote";
import Vote from "../../enums/Vote";

/**
 * Define the voteState props passed to the function.
 * Define the setVoteState props passed to the function.
 */
interface voteProps {
  voteState: VoteType;
  setState: (vote: Vote) => void;
}

const WDCommandButtons: React.FC<voteProps> = function ({
  voteState,
  setState,
}) {
  const { draw, pause, cancel } = voteState;

  return (
    <Stack direction="row" spacing={2} alignItems="center">
      <WDButton
        color={draw ? "secondary" : "primary"}
        onClick={() => setState(Vote.DRAW)}
        startIcon={draw ? <WDCheckmarkIcon /> : ""}
      >
        Draw
      </WDButton>
      <WDButton
        color={pause ? "secondary" : "primary"}
        onClick={() => setState(Vote.PAUSE)}
        startIcon={pause ? <WDCheckmarkIcon /> : ""}
      >
        Pause
      </WDButton>
      <WDButton
        color={cancel ? "secondary" : "primary"}
        onClick={() => setState(Vote.CANCEL)}
        startIcon={cancel ? <WDCheckmarkIcon /> : ""}
      >
        Cancel
      </WDButton>
    </Stack>
  );
};

export default WDCommandButtons;
