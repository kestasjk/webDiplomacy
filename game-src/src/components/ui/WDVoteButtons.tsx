import * as React from "react";
import WDButton from "./WDButton";
import Vote from "../../enums/Vote";
import WDCheckmarkIcon from "./icons/WDCheckmarkIcon";

interface voteProps {
  voteState: string[];
  votingInProgress: { [key in Vote]: string | null };
  toggleVote: (vote: Vote) => void;
  gameIsPaused: boolean;
}

const WDVoteButtons: React.FC<voteProps> = function ({
  voteState,
  votingInProgress,
  toggleVote,
  gameIsPaused,
}): React.ReactElement {
  const commandButtons = Object.keys(Vote).map((vote) => {
    const status = voteState.includes(vote);
    const disabled = votingInProgress[vote] !== null;
    return (
      <WDButton
        key={vote}
        disabled={disabled}
        color={status ? "secondary" : "primary"}
        onClick={() => toggleVote(Vote[vote])}
        startIcon={status ? <WDCheckmarkIcon /> : ""}
      >
        {vote === Vote.Pause && gameIsPaused ? "Unpause" : vote}
      </WDButton>
    );
  });

  return <div className="flex space-x-1 sm:space-x-3">{commandButtons}</div>;
};

export default WDVoteButtons;
