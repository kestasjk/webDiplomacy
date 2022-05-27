/* eslint-disable no-bitwise */
import { Box } from "@mui/material";
import * as React from "react";
import BuildUnitMap from "../../../data/BuildUnit";
import countryMap from "../../../data/map/variants/classic/CountryMap";
import BuildUnit from "../../../enums/BuildUnit";
import {
  gameApiSliceActions,
  gameOrder,
  gameTerritoriesMeta,
  gameMaps,
} from "../../../state/game/game-api-slice";
import { useAppDispatch, useAppSelector } from "../../../state/hooks";
import WDBuildUnitButtons from "./WDBuildUnitButtons";
import { TerritoryMeta } from "../../../state/interfaces/TerritoriesState";
import TerritoryMap from "../../../data/map/variants/classic/TerritoryMap";

const WDBuildContainer: React.FC = function (): React.ReactElement {
  const dispatch = useAppDispatch();
  const territoriesMeta = useAppSelector(gameTerritoriesMeta);
  const maps = useAppSelector(gameMaps);

  const build = (availableOrder, canBuild, toTerrID) => {
    console.log(
      `Dispatched a build ${canBuild} ${BuildUnitMap[canBuild]} ${toTerrID}`,
    );
    let terrIDToBuildOn = toTerrID;
    if (BuildUnitMap[canBuild] === "Build Army") {
      // If we initiated a build popup onto a special coast but the user
      // chose to build an army, make sure the army gets built on the province
      // root territory rather than a special coast.
      terrIDToBuildOn = maps.terrIDToProvinceID[toTerrID];
    }
    dispatch(
      gameApiSliceActions.updateOrdersMeta({
        [availableOrder]: {
          saved: false,
          update: {
            type: BuildUnitMap[canBuild],
            toTerrID: terrIDToBuildOn,
          },
        },
      }),
    );
    dispatch(gameApiSliceActions.resetOrder());
  };
  const order = useAppSelector(gameOrder);
  const userMember = useAppSelector((state) => state.game.overview.user.member);
  if (!order || order.type !== "Build") {
    return <Box />;
  }
  const territory = maps.terrIDToTerritory[order.toTerrID];
  const territoryMeta = territoriesMeta[territory];
  const { province, unitSlotName } = TerritoryMap[territory];
  const canBuild =
    territoryMeta?.type === "Coast" ? BuildUnit.All : BuildUnit.Army;
  console.log({ canBuild, territoryMeta });
  return (
    <WDBuildUnitButtons
      key={`${territoryMeta?.id}-${unitSlotName}`}
      availableOrder={order.orderID}
      canBuild={canBuild}
      clickCallback={build}
      country={countryMap[userMember.country]}
      province={province}
      unitSlotName={unitSlotName}
      toTerrID={order.toTerrID}
    />
  );
};

export default WDBuildContainer;
