import * as React from "react";
import { Stack } from "@mui/material";
import WDCheckmarkIcon from "../svgr-components/WDCheckmarkIcon";
import WDButton from "./WDButton";
import VoteType from "../../types/Vote";
import Vote from "../../enums/Vote";

interface voteProps {
  voteState: VoteType;
  toggleVote: (vote: Vote) => void;
}

const WDCommandButtons: React.FC<voteProps> = function ({
  voteState,
  toggleVote,
}): React.ReactElement {
  const { draw, pause, cancel } = voteState;

  return (
    <Stack direction="row" spacing={2} alignItems="center">
      <WDButton
        color={draw ? "secondary" : "primary"}
        onClick={() => toggleVote(Vote.DRAW)}
        startIcon={draw ? <WDCheckmarkIcon /> : ""}
      >
        Draw
      </WDButton>
      <WDButton
        color={pause ? "secondary" : "primary"}
        onClick={() => toggleVote(Vote.PAUSE)}
        startIcon={pause ? <WDCheckmarkIcon /> : ""}
      >
        Pause
      </WDButton>
      <WDButton
        color={cancel ? "secondary" : "primary"}
        onClick={() => toggleVote(Vote.CANCEL)}
        startIcon={cancel ? <WDCheckmarkIcon /> : ""}
      >
        Cancel
      </WDButton>
    </Stack>
  );
};

export default WDCommandButtons;
