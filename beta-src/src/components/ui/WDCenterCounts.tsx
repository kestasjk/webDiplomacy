import React, { ReactElement, FunctionComponent } from "react";
import { useWindowSize } from "react-use";
import { gameOverview } from "../../state/game/game-api-slice";
import { useAppSelector } from "../../state/hooks";

const WDCenterCounts: FunctionComponent = function (): ReactElement {
  const { members } = useAppSelector(gameOverview);

  const sortedMembers = [...members].sort((a, b) => a.countryID - b.countryID);

  return (
    <div className="flex justify-center items-center">
      {sortedMembers.map((m) => (
        <span key={m.countryID} className="pl-1 pr-1">
          {m.country.substring(0, 3)}: {m.supplyCenterNo}
        </span>
      ))}
    </div>
  );
};

export default WDCenterCounts;
