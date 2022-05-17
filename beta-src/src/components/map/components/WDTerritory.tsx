import { useTheme } from "@mui/material";
import * as React from "react";
import BuildUnitMap from "../../../data/BuildUnit";
import countryMap from "../../../data/map/variants/classic/CountryMap";
import Territories from "../../../data/Territories";
import { MemberData, TerritoryMapData } from "../../../interfaces";
import {
  gameApiSliceActions,
  gameOverview,
  gameTerritoriesMeta,
} from "../../../state/game/game-api-slice";
import { useAppDispatch, useAppSelector } from "../../../state/hooks";
import { TerritoryMeta } from "../../../state/interfaces/TerritoriesState";
import ClickObjectType from "../../../types/state/ClickObjectType";
import processNextCommand from "../../../utils/processNextCommand";
import WDArmyIcon from "../../ui/units/WDArmyIcon";
import WDFleetIcon from "../../ui/units/WDFleetIcon";
import WDUnit from "../../ui/units/WDUnit";
import WDBuildUnitButtons, { BuildData } from "./WDBuildUnitButtons";
import WDCenter from "./WDCenter";
import WDLabel from "./WDLabel";
import WDUnitSlot from "./WDUnitSlot";

interface WDTerritoryProps {
  territoryMapData: TerritoryMapData;
}

interface Units {
  [key: string]: React.ReactElement;
}

interface BuildPopovers {
  [key: string]: BuildData;
}

const WDTerritory: React.FC<WDTerritoryProps> = function ({
  territoryMapData,
}): React.ReactElement {
  const theme = useTheme();
  const dispatch = useAppDispatch();
  const { members } = useAppSelector(gameOverview);
  const territoriesMeta = useAppSelector(gameTerritoriesMeta);

  const [territoryStrokeOpacity, setTerritoryStrokeOpacity] = React.useState(1);

  const [buildPopovers, setBuildPopovers] = React.useState<BuildPopovers>({});

  const [openBuildPopovers, setOpenBuildPopovers] = React.useState(false);

  const [units, setUnits] = React.useState<Units>({});

  const commands = useAppSelector(
    (state) => state.game.commands.territoryCommands[territoryMapData.name],
  );

  const {
    user: { member },
  } = useAppSelector(gameOverview);
  const userCountry = countryMap[member.country];

  const deleteCommand = (key) => {
    dispatch(
      gameApiSliceActions.deleteCommand({
        type: "territoryCommands",
        id: territoryMapData.name,
        command: key,
      }),
    );
  };

  const setMoveHighlight = () => {
    setTerritoryStrokeOpacity(1);
  };

  const setCapturedHighlight = (country) => {
    setTerritoryStrokeOpacity(1);
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

<<<<<<< HEAD
  const commandActions = {
    BUILD: (command) => {
      const [key, value] = command;
      const builds: BuildPopovers = {};
      const buildsArray = value.data.build;
      if (buildsArray?.length) {
        buildsArray.forEach((b) => {
          builds[b.unitSlotName] = {
            ...b,
            ...{ clickCallback: build, country: userCountry },
          };
        });
      }
      setBuildPopovers(builds);
      setMoveHighlight();
      setOpenBuildPopovers(true);
      deleteCommand(key);
    },
    CAPTURED: (command) => {
      const [key, value] = command;
      territoryMapData.type === "water"
        ? setTerritoryFill("none")
        : setCapturedHighlight(value.data?.country);
      deleteCommand(key);
    },
    HOLD: (command) => {
      const [key] = command;
      setTerritoryFill(theme.palette[userCountry].main);
      setTerritoryFillOpacity(0.9);
      setTerritoryStrokeOpacity(2);
      deleteCommand(key);
    },
    MOVE: (command) => {
      const [key] = command;
      setMoveHighlight();
      deleteCommand(key);
    },
    REMOVE_BUILD: (command) => {
      const [key] = command;
      setOpenBuildPopovers(false);
      setCapturedHighlight(userCountry);
      deleteCommand(key);
    },
    SET_UNIT: (command) => {
      const [key, value] = command;
      const {
        componentType,
        country,
        iconState,
        mappedTerritory,
        unit,
        unitType,
        unitSlotName,
      } = value.data.setUnit;

      let newUnit;
      if (country && unitType && componentType) {
        switch (componentType) {
          case "Game":
            if (unit) {
              newUnit = (
                <WDUnit
                  id={`${territoryMapData.name}-unit`}
                  country={country}
                  meta={{ country, mappedTerritory, unit }}
                  type={unitType}
                />
              );
            }
            break;
          case "Icon":
            switch (unitType) {
              case "Army":
                newUnit = (
                  <svg filter={theme.palette.svg.filters.dropShadows[1]}>
                    <WDArmyIcon country={country} iconState={iconState} />
                  </svg>
                );
                break;
              case "Fleet":
                newUnit = (
                  <svg filter={theme.palette.svg.filters.dropShadows[1]}>
                    <WDFleetIcon country={country} iconState={iconState} />
                  </svg>
                );
                break;
              default:
                break;
            }
            break;
          default:
            break;
        }
      }

      const set = {
        ...units,
        ...{ [unitSlotName]: newUnit },
      };
      setUnits(set);
      deleteCommand(key);
    },
  };

  processNextCommand(commands, commandActions);
=======
  // const commandActions = {
  //   BUILD: (command) => {
  //     const [key, value] = command;
  //     const builds: BuildPopovers = {};
  //     const buildsArray = value.data.build;
  //     if (buildsArray?.length) {
  //       buildsArray.forEach((b) => {
  //         builds[b.unitSlotName] = {
  //           ...b,
  //           ...{ clickCallback: build, country: userCountry },
  //         };
  //       });
  //     }
  //     setBuildPopovers(builds);
  //     setMoveHighlight();
  //     setOpenBuildPopovers(true);
  //   },
  //   CAPTURED: (command) => {
  //     const [key, value] = command;
  //     console.log(`${territoryMapData.name} CAPTURED ${value.data?.country}`);
  //     territoryMapData.type === "water"
  //       ? setTerritoryFill("none")
  //       : setCapturedHighlight(value.data?.country);
  //   },
  //   HOLD: (command) => {
  //     const [key] = command;
  //     setTerritoryStrokeOpacity(2);
  //   },
  //   MOVE: (command) => {
  //     const [key] = command;
  //     setMoveHighlight();
  //   },
  //   REMOVE_BUILD: (command) => {
  //     const [key] = command;
  //     setOpenBuildPopovers(false);
  //     setCapturedHighlight(userCountry);
  //   },
  //   SET_UNIT: (command) => {
  //     console.log("SET_UNIT");
  //     const [key, value] = command;
  //     console.log(value.data);

  //     const {
  //       componentType,
  //       country,
  //       iconState,
  //       mappedTerritory,
  //       unit,
  //       unitType,
  //       unitSlotName,
  //     } = value.data.setUnit;
  //     console.log(
  //       `${territoryMapData.name} ${country} ${unitType} ${componentType}`,
  //     );
  //     let newUnit;
  //     if (country && unitType && componentType) {
  //       switch (componentType) {
  //         case "Game":
  //           if (unit) {
  //             switch (unitType) {
  //               case "Army":
  //                 console.log("Made an army...");
  //                 newUnit = (
  //                   <WDArmy
  //                     id={`${territoryMapData.name}-unit`}
  //                     country={country}
  //                     meta={{ country, mappedTerritory, unit }}
  //                   />
  //                 );
  //                 break;
  //               case "Fleet":
  //                 newUnit = (
  //                   <WDFleet
  //                     id={`${territoryMapData.name}-unit`}
  //                     country={country}
  //                     meta={{ country, mappedTerritory, unit }}
  //                   />
  //                 );
  //                 break;
  //               default:
  //                 break;
  //             }
  //           }
  //           break;
  //         case "Icon":
  //           switch (unitType) {
  //             case "Army":
  //               newUnit = (
  //                 <svg filter={theme.palette.svg.filters.dropShadows[1]}>
  //                   <WDArmyIcon country={country} iconState={iconState} />
  //                 </svg>
  //               );
  //               break;
  //             case "Fleet":
  //               newUnit = (
  //                 <svg filter={theme.palette.svg.filters.dropShadows[1]}>
  //                   <WDFleetIcon country={country} iconState={iconState} />
  //                 </svg>
  //               );
  //               break;
  //             default:
  //               break;
  //           }
  //           break;
  //         default:
  //           break;
  //       }
  //     }
  //     const set = {
  //       ...units,
  //       ...{ [unitSlotName]: newUnit },
  //     };
  //     setUnits(set);
  //   },
  // };
  // processNextCommand(commands, commandActions);
>>>>>>> f31209a (Proof-of-concept territory fill without commands)

  const territoryNameToMeta: { [key: string]: TerritoryMeta } = {};
  Object.entries(territoriesMeta).forEach(([id, meta]) => {
    territoryNameToMeta[Territories[id].name] = meta;
  });

  const countryIDToMember: { [key: number]: MemberData } = {};
  Object.entries(members).forEach(([id, memberData]) => {
    countryIDToMember[memberData.countryID] = memberData;
  });
  const territoryName = territoryMapData.name;
  let territoryFill = "none";
  let territoryFillOpacity = 0;
  const territoryMeta = territoryNameToMeta[territoryName];

  if (territoryMeta && territoryMeta.countryID) {
    const ownerCountryID = territoryMeta.countryID;
    const ownerMember = countryIDToMember[ownerCountryID];
    territoryFill = ownerCountryID
      ? theme.palette[ownerMember.country].main
      : "none";

    territoryFillOpacity = 0.4;
  }
  const clickAction = function (evt, clickObject: ClickObjectType) {
    dispatch(
      gameApiSliceActions.processMapClick({
        clickObject,
        evt,
        name: territoryMapData.name,
      }),
    );
  };
<<<<<<< HEAD

=======
>>>>>>> f31209a (Proof-of-concept territory fill without commands)
  return (
    <svg
      height={territoryMapData.height}
      id={`${territoryMapData.name}-territory`}
      viewBox={territoryMapData.viewBox}
      width={territoryMapData.width}
      x={territoryMapData.x}
      y={territoryMapData.y}
    >
      <g onClick={(e) => clickAction(e, "territory")}>
        {territoryMapData.texture?.texture && (
          <path
            d={territoryMapData.path}
            fill={territoryMapData.texture.texture}
            id={`${territoryMapData.name}-texture`}
            stroke={territoryMapData.texture.stroke}
            strokeOpacity={territoryMapData.texture.strokeOpacity}
            strokeWidth={territoryMapData.texture.strokeWidth}
          />
        )}
        <path
          d={territoryMapData.path}
          fill={territoryFill}
          fillOpacity={territoryFillOpacity}
          id={`${territoryMapData.name}-control-path`}
          stroke={theme.palette.primary.main}
          strokeOpacity={1}
          strokeWidth={territoryStrokeOpacity}
        />
      </g>
      {territoryMapData.centerPos && (
        <g className="no-pointer-events">
          <WDCenter
            territoryName={territoryMapData.name}
            x={territoryMapData.centerPos.x}
            y={territoryMapData.centerPos.y}
          />
        </g>
      )}
      {territoryMapData.labels &&
        territoryMapData.labels.map(({ name, text, style, x, y }, i) => {
          let txt = text;
          const id = `${territoryMapData.name}-label-${name}`;
          if (!txt) {
            txt = territoryMapData.abbr;
          }
          const b = buildPopovers[name];
          return (
            <>
              <g className="no-pointer-events">
                <WDLabel
                  id={id}
                  name={name}
                  key={id || i}
                  style={style}
                  text={txt}
                  x={x}
                  y={y}
                />
              </g>
              {b && openBuildPopovers && (
                <WDBuildUnitButtons
                  availableOrder={b.availableOrder}
                  canBuild={b.canBuild}
                  clickCallback={b.clickCallback}
                  country={b.country}
                  labelID={id}
                  toTerrID={b.toTerrID}
                  x={x}
                  y={y}
                />
              )}
            </>
          );
        })}
      {territoryMapData.unitSlots &&
        territoryMapData.unitSlots.map(({ name, x, y }) => (
          <WDUnitSlot
            key={name}
            name={name}
            territoryName={territoryMapData.name}
            x={x}
            y={y}
          >
            {units[name]}
          </WDUnitSlot>
        ))}
      {territoryMapData.arrowReceiver && (
        <rect
          id={`${territoryMapData.name}-arrow-receiver`}
          x={territoryMapData.arrowReceiver.x}
          y={territoryMapData.arrowReceiver.y}
          width="1"
          height="1"
        />
      )}
    </svg>
  );
};

export default WDTerritory;
