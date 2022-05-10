import * as React from "react";
import Box from "@mui/material/Box";
import Device from "../../enums/Device";
import Vote from "../../enums/Vote";
import WDCountryTable from "./WDCountryTable";
import WDVoteButtons from "./WDVoteButtons";
import { CountryTableData } from "../../interfaces/CountryTableData";
import GameOverviewResponse from "../../state/interfaces/GameOverviewResponse";
import useViewport from "../../hooks/useViewport";
import getDevice from "../../utils/getDevice";
import { toggleVoteStatus } from "../../state/game/game-api-slice";
import { useAppDispatch } from "../../state/hooks";

interface WDInfoPanelProps {
  countries: CountryTableData[];
  gameID: GameOverviewResponse["gameID"];
  maxDelays: GameOverviewResponse["excusedMissedTurns"];
  userCountry: CountryTableData;
}

const WDInfoPanel: React.FC<WDInfoPanelProps> = function ({
  countries,
  gameID,
  maxDelays,
  userCountry,
}): React.ReactElement {
  const [voteState, setVoteState] = React.useState(userCountry.votes);
  const [viewport] = useViewport();
  const device = getDevice(viewport);
  const dispatch = useAppDispatch();

  React.useEffect(() => {
    setVoteState(userCountry.votes);
  }, [userCountry]);

  const toggleVote = (voteName: Vote) => {
    const voteKey = Vote[voteName];
    const newVoteState = {
      ...voteState,
      [voteKey]: !voteState[voteKey],
    };

    const currentGameID = String(gameID);
    const countryID = String(userCountry.countryID);

    setVoteState(newVoteState);

    dispatch(
      toggleVoteStatus({
        countryID,
        gameID: currentGameID,
        vote: voteKey.charAt(0).toUpperCase() + voteKey.slice(1),
      }),
    );
  };

  const mobileLandscapeLayout =
    device === Device.MOBILE_LANDSCAPE ||
    device === Device.MOBILE_LG_LANDSCAPE ||
    device === Device.MOBILE;

  const padding = mobileLandscapeLayout ? "0 6px" : "0 16px";

  return (
    <Box>
      <Box sx={{ p: padding }}>
        <WDVoteButtons toggleVote={toggleVote} voteState={voteState} />
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
        />
      </Box>
    </Box>
  );
};

export default WDInfoPanel;
