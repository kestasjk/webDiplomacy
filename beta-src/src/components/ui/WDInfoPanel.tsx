import * as React from "react";
import Vote from "../../enums/Vote";
import WDCountryTable from "./WDCountryTable";
import WDVoteButtons from "./WDVoteButtons";
import { CountryTableData } from "../../interfaces/CountryTableData";
import GameOverviewResponse from "../../state/interfaces/GameOverviewResponse";
import { setVoteStatus } from "../../state/game/game-api-slice";
import { useAppSelector, useAppDispatch } from "../../state/hooks";

interface WDInfoPanelProps {
  allCountries: CountryTableData[];
  gameID: GameOverviewResponse["gameID"];
  maxDelays: GameOverviewResponse["excusedMissedTurns"];
  userCountry: CountryTableData | null;
  gameIsFinished: boolean;
  gameIsPaused: boolean;
}

const WDInfoPanel: React.FC<WDInfoPanelProps> = function ({
  allCountries,
  gameID,
  maxDelays,
  userCountry,
  gameIsFinished,
  gameIsPaused,
}): React.ReactElement {
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

  const intoCivilDisorder: boolean = allCountries.some(
    (country) => country.status === "Left",
  );

  return (
    <div>
      {userCountry && !gameIsFinished && (
        <div className="pl-4">
          <WDVoteButtons
            toggleVote={toggleVote}
            voteState={userCountry.votes}
            votingInProgress={votingInProgress}
            gameIsPaused={gameIsPaused}
          />
        </div>
      )}
      {intoCivilDisorder && (
        <div className="mt-4 ml-4 mr-3 p-3 bg-red-600 text-white text-center rounded-lg">
          Game has fallen into civil disorder due to move missed by one player
        </div>
      )}
      <WDCountryTable
        maxDelays={maxDelays}
        countries={allCountries}
        userCountry={userCountry}
        gameIsPaused={gameIsPaused}
      />
    </div>
  );
};

export default WDInfoPanel;
