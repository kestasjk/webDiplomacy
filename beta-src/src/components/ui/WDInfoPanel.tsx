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
import {
  gameApiSliceActions,
  setVoteStatus,
} from "../../state/game/game-api-slice";
import { useAppSelector, useAppDispatch } from "../../state/hooks";

interface WDInfoPanelProps {
  allCountries: CountryTableData[];
  gameID: GameOverviewResponse["gameID"];
  maxDelays: GameOverviewResponse["excusedMissedTurns"];
  userCountry: CountryTableData | null;
}

const WDInfoPanel: React.FC<WDInfoPanelProps> = function ({
  allCountries,
  gameID,
  maxDelays,
  userCountry,
}): React.ReactElement {
  const [viewport] = useViewport();
  const device = getDevice(viewport);
  const dispatch = useAppDispatch();
  const votingInProgress = useAppSelector(
    (state) => state.game.votingInProgress,
  );

  const toggleVote = (voteKey: Vote) => {
    if (userCountry) {
      const desiredVoteOn = userCountry.votes.includes(voteKey) ? "No" : "Yes";
      dispatch(
        setVoteStatus({
          countryID: String(userCountry.countryID),
          gameID: String(gameID),
          vote: voteKey,
          voteOn: desiredVoteOn,
        }),
      );
    }
  };
  const mobileLandscapeLayout =
    device === Device.MOBILE_LANDSCAPE ||
    device === Device.MOBILE_LG_LANDSCAPE ||
    device === Device.MOBILE;

  const padding = mobileLandscapeLayout ? "0 6px" : "0 16px";

  return (
    <Box>
      {userCountry && (
        <Box sx={{ p: padding }}>
          <WDVoteButtons
            toggleVote={toggleVote}
            voteState={userCountry.votes}
            votingInProgress={votingInProgress}
          />
        </Box>
      )}
      <Box
        sx={{
          m: "20px 5px 10px 0",
        }}
      >
        <WDCountryTable maxDelays={maxDelays} countries={allCountries} />
      </Box>
    </Box>
  );
};

export default WDInfoPanel;
