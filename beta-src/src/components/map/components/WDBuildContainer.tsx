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
  const order = useAppSelector(gameOrder);
  const userMember = useAppSelector((state) => state.game.overview.user.member);
  if (!order || order.type !== "Build") {
    return <Box />;
  }
  const territory = maps.terrIDToTerritory[order.toTerrID];
  const territoryMeta = territoriesMeta[territory];
  const { parent } = TerritoryMap[territory];
  const unitSlotName = "main"; // FIXME
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
      territoryName={parent || territory}
      unitSlotName={unitSlotName}
      toTerrID={order.toTerrID}
    />
  );
};

export default WDBuildContainer;
