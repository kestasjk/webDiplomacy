/* eslint-disable no-bitwise */
import * as React from "react";
import BuildUnitMap from "../../../data/BuildUnit";
import countryMap from "../../../data/map/variants/classic/CountryMap";
import {
  gameApiSliceActions,
  gameOverview,
} from "../../../state/game/game-api-slice";
import { useAppDispatch, useAppSelector } from "../../../state/hooks";
import processNextCommand from "../../../utils/processNextCommand";
import WDBuildUnitButtons, { BuildData } from "./WDBuildUnitButtons";

interface BuildPopovers {
  [key: string]: BuildData;
}

const WDBuildContainer: React.FC = function (): React.ReactElement {
  const commands = useAppSelector(
    (state) => state.game.commands.mapCommands.build,
  );
  const {
    user: {
      member: { country },
    },
  } = useAppSelector(gameOverview);
  const dispatch = useAppDispatch();
  const [buildPopovers, setBuildPopovers] = React.useState<BuildPopovers>({});
  const [buildTerritoryName, setBuildTerritoryName] = React.useState(null);
  const [openBuildPopovers, setOpenBuildPopovers] = React.useState(false);
  const userCountry = countryMap[country];
  const deleteCommand = (key) => {
    dispatch(
      gameApiSliceActions.deleteCommand({
        type: "mapCommands",
        id: "build",
        command: key,
      }),
    );
  };
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
    setOpenBuildPopovers(false);
    dispatch(gameApiSliceActions.resetOrder());
  };
  const commandActions = {
    BUILD: (command) => {
      const [key, value] = command;
      const builds: BuildPopovers = {};
      const { builds: buildsArray, territoryName } = value.data.build;
      if (buildsArray?.length) {
        buildsArray.forEach((b) => {
          builds[b.unitSlotName] = {
            ...b,
            ...{ clickCallback: build, country: userCountry },
          };
        });
      }
      setBuildPopovers(builds);
      setBuildTerritoryName(territoryName);
      dispatch(
        gameApiSliceActions.dispatchCommand({
          command: { command: "MOVE" },
          container: "territoryCommands",
          identifier: territoryName,
        }),
      );
      setOpenBuildPopovers(true);
      deleteCommand(key);
    },
    REMOVE_BUILD: (command) => {
      const [key] = command;
      setOpenBuildPopovers(false);
      setBuildTerritoryName(null);
      deleteCommand(key);
    },
  };
  processNextCommand(commands, commandActions);
  return (
    <>
      {Object.values(buildPopovers).map(
        (b) =>
          openBuildPopovers && (
            <WDBuildUnitButtons
              availableOrder={b.availableOrder}
              canBuild={b.canBuild}
              clickCallback={b.clickCallback}
              country={b.country}
              territoryName={buildTerritoryName}
              unitSlotName={b.unitSlotName}
              toTerrID={b.toTerrID}
            />
          ),
      )}
    </>
  );
};

export default WDBuildContainer;
