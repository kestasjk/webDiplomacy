import * as React from "react";
import {
  gameOrdersMeta,
  gameOverview,
  gameStatus,
  gameData,
  gameViewedPhase,
} from "../../state/game/game-api-slice";
import { useAppSelector } from "../../state/hooks";
import WDArmyIcon from "./units/WDArmyIcon";
import UIState from "../../enums/UIState";
import Country from "../../enums/Country";
import countryMap from "../../data/map/variants/classic/CountryMap";

const range = (N: number) => Array.from(Array(N).keys());

const WDBuildCounts: React.FC = function (): React.ReactElement {
  const { user, members } = useAppSelector(gameOverview);
  const { phases } = useAppSelector(gameStatus);
  const { viewedPhaseIdx } = useAppSelector(gameViewedPhase);
  const data = useAppSelector(gameData);
  const ordersMeta = useAppSelector(gameOrdersMeta);
  const phase = phases[viewedPhaseIdx];
  const isCurrent = viewedPhaseIdx >= phases.length - 1;
  if (phase?.phase !== "Builds" || !isCurrent) return <div />;
  const orders = data.data.currentOrders ?? [];
  const UNIT_WIDTH_SQUOOSHED = 40; // need to squoosh to avoid increasing HUD width
  const country = user ? countryMap[user.member.country] : Country.FRANCE;

  let buildIdxCounter = 0;
  const buildOrders = members
    .filter(
      (member) =>
        (member.country === country || data?.data?.isSandboxMode) &&
        member.supplyCenterNo !== member.unitNo,
    )
    .flatMap((member) => {
      return orders
        .filter((o) => Number(o.countryID) === Number(member.countryID))
        .filter(
          (o) =>
            !(
              ordersMeta[o.id].update?.type.startsWith("Build") ||
              ordersMeta[o.id].update?.type.startsWith("Destroy")
            ),
        )
        .map((o) => {
          buildIdxCounter += 1;
          return {
            country: countryMap[member.country],
            buildIdx: buildIdxCounter,
            type:
              member.supplyCenterNo > member.unitNo
                ? UIState.BUILD
                : UIState.DESTROY,
          };
        });
    });
  return (
    <div
      className={`${
        buildOrders.length > 0 &&
        "display-block px-7 py-3 mt-1 bg-[#1C2B33] rounded-xl mb-3"
      }`}
    >
      <div className="pr-2 flex">
        {buildOrders.map((o) => (
          <svg
            key={o.buildIdx}
            style={{
              height: 50,
              width: UNIT_WIDTH_SQUOOSHED,
              overflow: "visible",
            }}
          >
            <WDArmyIcon country={o.country} iconState={o.type} />
          </svg>
        ))}
      </div>
    </div>
  );
};

export default WDBuildCounts;
