import * as React from "react";
import Box from "@mui/material/Box";
import Device from "../../enums/Device";
import Vote from "../../enums/Vote";
import IntegerRange from "../../types/IntegerRange";
import WDCountryTable from "./WDCountryTable";
import WDVoteButtons from "./WDVoteButtons";
import { CountryTableData } from "../../interfaces/CountryTableData";

interface WDInfoPanelProps {
  countries: CountryTableData[];
  userCountry: CountryTableData;
  device: Device;
  maxDelays: IntegerRange<0, 5>;
}

const WDInfoPanel: React.FC<WDInfoPanelProps> = function ({
  countries,
  userCountry,
  device,
  maxDelays,
}): React.ReactElement {
  const [voteState, setVoteState] = React.useState(userCountry.votes);

  const toggleVote = (voteName: Vote) => {
    const newVoteState = {
      ...voteState,
      [voteName]: !voteState[voteName],
    };

    setVoteState(newVoteState);
  };

  return (
    <Box>
      <WDVoteButtons voteState={voteState} toggleVote={toggleVote} />
      <WDCountryTable
        maxDelays={maxDelays}
        /**
         * always show current user at the top
         *
         */
        countries={[{ ...userCountry, votes: voteState }, ...countries]}
        device={device}
      />
    </Box>
  );
};

export default WDInfoPanel;
