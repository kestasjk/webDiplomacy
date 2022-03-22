import * as React from "react";
import Box from "@mui/material/Box";
import Device from "../../enums/Device";
import WDCountryTable from "./WDCountryTable";
import WDVoteButtons from "./WDVoteButtons";
import { CountryTableData } from "../../interfaces/CountryTableData";
import { UserData } from "../../interfaces/UserData";

interface WDInfoPanelProps {
  countries: CountryTableData[];
  userData: UserData;
  device: Device;
}

const WDInfoPanel: React.FC<WDInfoPanelProps> = function ({
  countries,
  userData,
  device,
}): React.ReactElement {
  const [voteState, setVoteState] = React.useState(userData.votes);

  const toggleVote = (voteName: string, voteStatus: boolean) => {
    const newVoteState = {
      ...voteState,
      [voteName]: !voteStatus,
    };

    setVoteState(newVoteState);
  };

  return (
    <Box>
      <WDVoteButtons voteState={voteState} toggleVote={toggleVote} />
      <WDCountryTable
        maxDelays={3}
        /**
         * always show current user at the top
         *
         */
        countries={[
          { ...userData.countryTableData, votes: voteState },
          ...countries,
        ]}
        device={device}
      />
    </Box>
  );
};

export default WDInfoPanel;
