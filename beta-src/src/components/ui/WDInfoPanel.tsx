import * as React from "react";
import Box from "@mui/material/Box";
import Device from "../../enums/Device";
import Vote from "../../enums/Vote";
import WDCountryTable from "./WDCountryTable";
import WDVoteButtons from "./WDVoteButtons";
import { CountryTableData } from "../../interfaces/CountryTableData";
import GameOverviewResponse from "../../state/interfaces/GameOverviewResponse";

interface WDInfoPanelProps {
  countries: CountryTableData[];
  userCountry: CountryTableData;
  device: Device;
  maxDelays: GameOverviewResponse["excusedMissedTurns"];
}

const WDInfoPanel: React.FC<WDInfoPanelProps> = function ({
  countries,
  userCountry,
  device,
  maxDelays,
}): React.ReactElement {
  const [voteState, setVoteState] = React.useState(userCountry.votes);

  React.useEffect(() => {
    setVoteState(userCountry.votes);
  }, [userCountry, countries]);

  const toggleVote = (voteName: Vote) => {
    const voteKey = Vote[voteName];
    const newVoteState = {
      ...voteState,
      [voteKey]: !voteState[voteKey],
    };

    setVoteState(newVoteState);
  };

  const mobileLandscapeLayout =
    device === Device.MOBILE_LANDSCAPE || device === Device.MOBILE_LG_LANDSCAPE;

  const padding = mobileLandscapeLayout ? "0 6px" : "0 16px";

  return (
    <Box>
      <Box sx={{ p: padding }}>
        <WDVoteButtons voteState={voteState} toggleVote={toggleVote} />
      </Box>
      <Box
        sx={{
          m: "20px 5px 10px 0",
        }}
      >
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
    </Box>
  );
};

export default WDInfoPanel;
