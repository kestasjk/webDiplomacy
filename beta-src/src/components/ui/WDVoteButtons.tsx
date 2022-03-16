import * as React from "react";
import { Stack } from "@mui/material";
import WDCheckmarkIcon from "../svgr-components/WDCheckmarkIcon";
import WDButton from "./WDButton";
import VoteType from "../../types/Vote";

interface voteProps {
  voteState: VoteType;
  toggleVote: (vote: string) => void;
}

const voteLabel = {
  draw: "Draw",
  pause: "Pause",
  cancel: "Cancel",
};

const WDVoteButtons: React.FC<voteProps> = function ({
  voteState,
  toggleVote,
}): React.ReactElement {
  const commandButtons = Object.entries(voteState).map(([vote, status]) => {
    return (
      <WDButton
        key={vote}
        color={status ? "secondary" : "primary"}
        onClick={() => toggleVote(vote)}
        startIcon={status ? <WDCheckmarkIcon /> : ""}
      >
        {voteLabel[vote]}
      </WDButton>
    );
  });

  return (
    <Stack direction="row" spacing={2} alignItems="center">
      {commandButtons}
    </Stack>
  );
};

export default WDVoteButtons;
