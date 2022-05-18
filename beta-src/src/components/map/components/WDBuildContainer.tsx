/* eslint-disable no-bitwise */
import * as React from "react";
import BuildUnitMap from "../../../data/BuildUnit";
import countryMap from "../../../data/map/variants/classic/CountryMap";
import {
  gameApiSliceActions,
  gameOverview,
  gameTerritoriesMeta,
} from "../../../state/game/game-api-slice";
import { useAppDispatch, useAppSelector } from "../../../state/hooks";
import WDBuildUnitButtons from "./WDBuildUnitButtons";

const WDBuildContainer: React.FC = function (): React.ReactElement {
  const dispatch = useAppDispatch();
  const build = (availableOrder, canBuild, toTerrID) => {
    dispatch(
      gameApiSliceActions.updateOrdersMeta({
        [availableOrder]: {
          saved: false,
          update: {
            type: BuildUnitMap[canBuild],
            toTerrID,
          },
        },
      }),
    );
    dispatch(gameApiSliceActions.resetOrder());
  };
  const buildPopover = useAppSelector((state) => state.game.buildPopover);
  const userMember = useAppSelector((state) => state.game.overview.user.member);

  return (
    <>
      {Object.values(buildPopover).map((b) => (
        <WDBuildUnitButtons
          availableOrder={b.availableOrder}
          canBuild={b.canBuild}
          clickCallback={() => build(b.availableOrder, b.canBuild, b.toTerrID)}
          country={countryMap[userMember.country]}
          territoryName={gameTerritoriesMeta[b.toTerrID].name}
          unitSlotName={b.unitSlotName}
          toTerrID={b.toTerrID}
        />
      ))}
    </>
  );
};

export default WDBuildContainer;
