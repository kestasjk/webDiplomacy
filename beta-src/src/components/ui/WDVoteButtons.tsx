import * as React from "react";
import { Stack } from "@mui/material";
import WDButton from "./WDButton";
import Vote from "../../enums/Vote";
import VoteType from "../../types/Vote";
import getDevice from "../../utils/getDevice";
import useViewport from "../../hooks/useViewport";
import Device from "../../enums/Device";
import WDCheckmarkIcon from "./icons/WDCheckmarkIcon";

interface voteProps {
  voteState: VoteType;
  toggleVote: (vote: Vote) => void;
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
  const [viewport] = useViewport();
  const device = getDevice(viewport);
  const mobileLandscapeLayout =
    device === Device.MOBILE_LANDSCAPE ||
    device === Device.MOBILE_LG_LANDSCAPE ||
    device === Device.MOBILE;
  const padding = mobileLandscapeLayout ? "10px 10px" : "10px 18px";
  const spacing = mobileLandscapeLayout ? 1 : 2;
  const commandButtons = Object.entries(voteState).map(([vote, status]) => {
    return (
      <WDButton
        key={vote}
        sx={{ p: padding }}
        color={status ? "secondary" : "primary"}
        onClick={() => toggleVote(Vote[vote])}
        startIcon={status ? <WDCheckmarkIcon /> : ""}
      >
        {voteLabel[vote]}
      </WDButton>
    );
  });

  return (
    <Stack direction="row" spacing={spacing} alignItems="center">
      {commandButtons}
    </Stack>
  );
};

export default WDVoteButtons;
