import * as React from "react";
import { Stack } from "@mui/material";
import WDCheckmarkIcon from "../svgr-components/WDCheckmarkIcon";
import WDButton from "./WDButton";
import VoteType from "../../types/Vote";

/**
 * Define the voteState props passed to the function.
 * Define the setVoteState props passed to the function.
 */
interface voteProps {
  voteState: VoteType;
  setVoteState: React.Dispatch<React.SetStateAction<VoteType>>;
}

const WDCommandButtons: React.FC<voteProps> = function ({
  voteState,
  setVoteState,
}) {
  const { draw, pause, cancel } = voteState;

  return (
    <Stack direction="row" spacing={2} alignItems="center">
      <WDButton
        color={draw ? "secondary" : "primary"}
        onClick={() => {
          setVoteState((preState) => ({
            ...preState,
            draw: !draw,
          }));
        }}
        startIcon={draw ? <WDCheckmarkIcon /> : ""}
      >
        Draw
      </WDButton>
      <WDButton
        color={pause ? "secondary" : "primary"}
        onClick={() => {
          setVoteState((preState) => ({
            ...preState,
            pause: !pause,
          }));
        }}
        startIcon={pause ? <WDCheckmarkIcon /> : ""}
      >
        Pause
      </WDButton>
      <WDButton
        color={cancel ? "secondary" : "primary"}
        onClick={() => {
          setVoteState((preState) => ({
            ...preState,
            cancel: !cancel,
          }));
        }}
        startIcon={cancel ? <WDCheckmarkIcon /> : ""}
      >
        Cancel
      </WDButton>
    </Stack>
  );
};

export default WDCommandButtons;
