import * as React from "react";
import Season from "../../enums/Season";
import { OrderStatus } from "../../interfaces";
import { useAppSelector } from "../../state/hooks";
import {
  gameOverview,
  gameStatus,
  gameViewedPhase,
} from "../../state/game/game-api-slice";

interface WDPillScrollerProps {
  country: string;
  viewedSeason: Season;
  viewedYear: number;
  orderStatus: OrderStatus | undefined;
}

const WDPillScroller: React.FC<WDPillScrollerProps> = function ({
  country,
  viewedSeason,
  viewedYear,
  orderStatus,
}): React.ReactElement {
  const { phases } = useAppSelector(gameStatus);
  const { viewedPhaseIdx } = useAppSelector(gameViewedPhase);
  const { phase } = useAppSelector(gameOverview);
  const isCurrent = viewedPhaseIdx >= phases.length - 1;
  if (phase !== "Builds" || !isCurrent) return <div />;

  const buildMessage = () => {
    let response;
    if (orderStatus?.None) {
      response = `${viewedSeason},${viewedYear}. ${country}, no orders to fill for this phase.`;
      // eslint-disable-next-line no-else-return
    } else if (phase === "Builds" && isCurrent && !orderStatus?.None) {
      response = `${viewedSeason},${viewedYear}. ${country}, build a unit.`;
    }
    return response;
  };

  return (
    <div className="flex items-center py-2 px-3 rounded-md text-white bg-opacity-60 bg-black font-bold select-none w-fit">
      {/* , Select a unit */}
      {buildMessage()}
    </div>
  );
};

export default WDPillScroller;
