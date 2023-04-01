import * as React from "react";
import WDCountryTable from "./WDCountryTable";
import { CountryTableData } from "../../interfaces/CountryTableData";
import GameOverviewResponse from "../../state/interfaces/GameOverviewResponse";
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

  const intoCivilDisorder: boolean = allCountries.some(
    (country) => country.status === "Left",
  );

  return (
    <div>
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
