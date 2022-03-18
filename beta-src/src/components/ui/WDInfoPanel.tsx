import * as React from "react";
import WDPopover from "./WDPopover";
import WDCountryTable from "./WDCountryTable";
import WDVoteButtons from "./WDVoteButtons";
import { CountryTableData } from "../../interfaces/CountryTableData";
import { UserData } from "../../interfaces/UserData";
import getDevice from "../../utils/getDevice";

interface WDInfoPanelProps {
  countries: CountryTableData[];
  userData: UserData;
}

const WDInfoPanel: React.FC<WDInfoPanelProps> = function ({
  countries,
  userData,
}): React.ReactElement {
  const [voteState, setVoteState] = React.useState(userData.votes);
  const [isOpen, setIsOpen] = React.useState(false);
  const device = getDevice({ width: 1300, height: 900 });

  const toggleVote = (voteName: string, voteStatus: boolean) => {
    const newVoteState = {
      ...voteState,
      [voteName]: !voteStatus,
    };

    setVoteState(newVoteState);
  };

  return (
    <WDPopover
      popoverTrigger={
        <button
          type="button"
          onClick={() => {
            setIsOpen(!isOpen);
          }}
        >
          {/**
           * TODO
           * Replace with correct Icon component
           */}
          open
        </button>
      }
      isOpen={isOpen}
    >
      <WDVoteButtons voteState={voteState} toggleVote={toggleVote} />
      <WDCountryTable
        maxDelays={3}
        countries={[
          { ...userData.countryTableData, votes: voteState },
          ...countries,
        ]}
        device={device}
      />
    </WDPopover>
  );
};

export default WDInfoPanel;
