import * as React from "react";
import { Stack } from "@mui/material";
import WDCheckmarkIcon from "../svgr-components/WDCheckmarkIcon";
import WDButton from "./WDButton";
import VoteType from "../../types/Vote";
import voteStateArray from "../../utils/voteStateArray";

interface voteProps {
  voteState: VoteType;
  toggleVote: (vote: string) => void;
}

const WDCommandButtons: React.FC<voteProps> = function ({
  voteState,
  toggleVote,
}): React.ReactElement {
  const voteArray = voteStateArray(voteState);

  const commendButtons = voteArray.map((singleVote) => {
    const { vote, status } = singleVote;
    const displayName = vote[0].toUpperCase() + vote.slice(1);
    return (
      <WDButton
        key={vote}
        color={status ? "secondary" : "primary"}
        onClick={() => toggleVote(vote)}
        startIcon={status ? <WDCheckmarkIcon /> : ""}
      >
        {displayName}
      </WDButton>
    );
  });

  return (
    <Stack direction="row" spacing={2} alignItems="center">
      {commendButtons}
    </Stack>
  );
};

export default WDCommandButtons;
