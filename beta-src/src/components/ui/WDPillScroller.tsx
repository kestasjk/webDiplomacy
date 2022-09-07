import * as React from "react";
import Season from "../../enums/Season";
import { OrderStatus } from "../../interfaces";
import { useAppSelector } from "../../state/hooks";
import OrderState from "../../state/interfaces/OrderState";
import { IOrderDataHistorical } from "../../models/Interfaces";
import { formatPhaseForDisplay } from "../../utils/formatPhaseForDisplay";
import { ReactComponent as ArmyIconPlain } from "../../assets/svg/armyIconPlain.svg";
import { ReactComponent as FleetIconPlain } from "../../assets/svg/fleetIconPlain.svg";
import { getHistoricalPhaseSeasonYear } from "../../utils/state/getPhaseSeasonYear";
import { ReactComponent as AutumnIcon } from "../../assets/svg/phases/autumn.svg";
import { ReactComponent as SpringIcon } from "../../assets/svg/phases/spring.svg";
import { ReactComponent as WinterIcon } from "../../assets/svg/phases/winter.svg";

import {
  gameOverview,
  gameStatus,
  gameViewedPhase,
  gameOrder,
  gameMaps,
} from "../../state/game/game-api-slice";

interface WDPillScrollerProps {
  country: string;
  viewedPhase: string;
  viewedSeason: Season;
  viewedYear: number;
  orderStatus: OrderStatus | undefined;
  orders: IOrderDataHistorical[];
  phaseSelectorOpen: boolean;
}

const WDPillScroller: React.FC<WDPillScrollerProps> = function ({
  country,
  viewedPhase,
  viewedSeason,
  viewedYear,
  orderStatus,
  orders,
  phaseSelectorOpen,
}): React.ReactElement {
  const maps = useAppSelector(gameMaps);
  const { phases } = useAppSelector(gameStatus);
  const status = useAppSelector(gameStatus);
  const { viewedPhaseIdx, latestPhaseViewed } = useAppSelector(gameViewedPhase);
  const { phase } = useAppSelector(gameOverview);
  const isCurrent = viewedPhaseIdx >= phases.length - 1;

  const currentOrder: OrderState = useAppSelector(gameOrder);
  const terrID = maps.unitToTerrID[currentOrder.unitID];
  const historicalOrder = orders.find(
    (order: IOrderDataHistorical) => order.terrID === Number(terrID || 0),
  );
  const formattedPhase = formatPhaseForDisplay(viewedPhase);

  // eslint-disable-next-line consistent-return
  const getIcon = (season: Season) => {
    const iconClassName = "text-white mr-2 h-4";
    if (season === Season.AUTUMN)
      return <AutumnIcon className={iconClassName} />;
    if (season === Season.SPRING)
      return <SpringIcon className={iconClassName} />;
    if (season === Season.WINTER)
      return <WinterIcon className={iconClassName} />;
  };

  const buildMessage = () => {
    const prefix = `${viewedSeason} ${viewedYear}${
      formattedPhase ? ` ${formattedPhase}` : ""
    }. ${country}`;
    const icon =
      historicalOrder?.unitType === "Army" ? (
        <ArmyIconPlain className="w-4 h-4 mr-2" />
      ) : (
        <FleetIconPlain className="w-4 h-4 mr-2" />
      );

    if (phase === "Pre-game") {
      return "Pre-game";
    }

    if (status.status !== "Left" && phases.length > 1 && !isCurrent) {
      const idx = viewedPhaseIdx;
      const psy = getHistoricalPhaseSeasonYear(status, idx);
      return (
        <>
          {getIcon(psy.season)}
          {psy.season} {psy.year} {formatPhaseForDisplay(psy.phase)} complete
        </>
      );
    }

    if (orderStatus?.Completed && orderStatus?.Ready) {
      return "Orders ready";
    }

    if (currentOrder.inProgress) {
      const fromTerritory = maps.terrIDToProvince[terrID];

      if (currentOrder.type) {
        // when the player selected an action/order (Move, Hold, Support, etc)
        return (
          <>
            {icon}
            {fromTerritory} {currentOrder.type}
          </>
        );
      }

      return (
        <>
          {/* when the player clicks on the province icon */}
          {icon}
          {fromTerritory}, select an order.
        </>
      );
      // eslint-disable-next-line no-else-return
    } else if (orderStatus?.None) {
      return `${prefix}, no orders to fill for this phase.`;
    } else if (phase === "Builds" && isCurrent && !orderStatus?.None) {
      return `${prefix}, build a unit.`;
    } else {
      return `${prefix}${
        phaseSelectorOpen && !isCurrent ? "." : ", enter your orders."
      }`;
    }
  };

  return (
    <div className="flex items-center py-2 px-3 rounded-md text-white bg-opacity-60 bg-black font-bold select-none w-fit">
      {buildMessage()}
    </div>
  );
};

export default WDPillScroller;
