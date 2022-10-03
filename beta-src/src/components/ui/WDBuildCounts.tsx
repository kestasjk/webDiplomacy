import * as React from "react";
import {
  gameOrdersMeta,
  gameOverview,
  gameStatus,
  gameViewedPhase,
} from "../../state/game/game-api-slice";
import { useAppSelector } from "../../state/hooks";
import WDArmyIcon from "./units/WDArmyIcon";
import UIState from "../../enums/UIState";
import Country from "../../enums/Country";
import countryMap from "../../data/map/variants/classic/CountryMap";

const range = (N: number) => Array.from(Array(N).keys());

const WDBuildCounts: React.FC = function (): React.ReactElement {
  const { user } = useAppSelector(gameOverview);
  const { phases } = useAppSelector(gameStatus);
  const { viewedPhaseIdx } = useAppSelector(gameViewedPhase);
  const ordersMeta = useAppSelector(gameOrdersMeta);

  const phase = phases[viewedPhaseIdx];
  const isCurrent = viewedPhaseIdx >= phases.length - 1;
  if (phase?.phase !== "Builds" || !isCurrent) return <div />;
  const extraSCs = user ? user.member.supplyCenterNo - user.member.unitNo : 0;
  const numBuildOrders = Object.values(ordersMeta).filter((o) =>
    o.update?.type.startsWith("Build"),
  ).length;
  const numDestroyOrders = Object.values(ordersMeta).filter((o) =>
    o.update?.type.startsWith("Destroy"),
  ).length;
  const numRemainingBuilds = Math.max(extraSCs - numBuildOrders, 0);
  const numRemainingDestroys = Math.max(-extraSCs - numDestroyOrders, 0);
  const UNIT_WIDTH_SQUOOSHED = 40; // need to squoosh to avoid increasing HUD width
  const country = user ? countryMap[user.member.country] : Country.FRANCE;
  return (
    <div
      className={`${
        (numRemainingBuilds > 0 || numRemainingDestroys > 0) &&
        "display-block px-7 py-3 mt-1 bg-[#1C2B33] rounded-xl mb-3"
      }`}
    >
      <div className="pr-2 flex">
        {range(numRemainingBuilds).map((buildIdx) => (
          <svg
            key={buildIdx}
            style={{
              height: 50,
              width: UNIT_WIDTH_SQUOOSHED,
              overflow: "visible",
            }}
          >
            <WDArmyIcon country={country} iconState={UIState.BUILD} />
          </svg>
        ))}
        {range(numRemainingDestroys).map((buildIdx) => (
          <svg
            key={buildIdx}
            style={{
              height: 50,
              width: UNIT_WIDTH_SQUOOSHED,
              overflow: "visible",
            }}
          >
            <WDArmyIcon country={country} iconState={UIState.DESTROY} />
          </svg>
        ))}
      </div>
    </div>
  );
};

export default WDBuildCounts;
