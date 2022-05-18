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
  gameUnits,
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
  const territoriesMeta = useAppSelector(gameTerritoriesMeta);
  const [territoryStrokeOpacity, setTerritoryStrokeOpacity] = React.useState(1);

  const [buildPopovers, setBuildPopovers] = React.useState<BuildPopovers>({});

  const [openBuildPopovers, setOpenBuildPopovers] = React.useState(false);

  const { user, members } = useAppSelector(gameOverview);
  const units = useAppSelector(gameUnits);
  const userCountry = countryMap[user.member.country];

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
=======
>>>>>>> cf82051 (Got units displayed again)
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
  //     deleteCommand(key);
  //   },
  //   CAPTURED: (command) => {
  //     const [key, value] = command;
  //     territoryMapData.type === "water"
  //       ? setTerritoryFill("none")
  //       : setCapturedHighlight(value.data?.country);
  //     deleteCommand(key);
  //   },
  //   HOLD: (command) => {
  //     const [key] = command;
  //     setTerritoryFill(theme.palette[userCountry].main);
  //     setTerritoryFillOpacity(0.9);
  //     setTerritoryStrokeOpacity(2);
  //     deleteCommand(key);
  //   },
  //   MOVE: (command) => {
  //     const [key] = command;
  //     setMoveHighlight();
  //     deleteCommand(key);
  //   },
  //   REMOVE_BUILD: (command) => {
  //     const [key] = command;
  //     setOpenBuildPopovers(false);
  //     setCapturedHighlight(userCountry);
  //     deleteCommand(key);
  //   },
  //   SET_UNIT: (command) => {
  //     const [key, value] = command;
  //     const {
  //       componentType,
  //       country,
  //       iconState,
  //       mappedTerritory,
  //       unit,
  //       unitType,
  //       unitSlotName,
  //     } = value.data.setUnit;

  const territoryName = territoryMapData.name;
  const territoryNameToMeta: { [key: string]: TerritoryMeta } = {};

  Object.entries(territoriesMeta).forEach(([id, meta]) => {
    territoryNameToMeta[Territories[id].name] = meta;
  });
  const territoryMeta = territoryNameToMeta[territoryName];

  const countryIDToCountry: { [key: number]: string } = {};
  Object.entries(members).forEach(([id, memberData]) => {
    countryIDToCountry[memberData.countryID] = memberData.country;
  });

  let territoryFill = "none";
  let territoryFillOpacity = 0;
  if (territoryMeta && territoryMeta.countryID) {
    const ownerCountryID = territoryMeta.countryID;
    const ownerCountry = countryIDToCountry[ownerCountryID];
    territoryFill = ownerCountryID ? theme.palette[ownerCountry].main : "none";

    territoryFillOpacity = 0.4;
  }

  const unitFCs: { [key: string]: any } = {};
  units
    .filter((unit) => territoryMeta && unit.unit.terrID === territoryMeta.id)
    .forEach((unit) => {
      const unitType = unit.unit.type;
      // FIXME: Maybe we want just a WDFleetIcon for other powers. But does it really matter?
      // It doesn't seem like I can click on other people's armies / fleets.
      const WDUnitComponent = unitType === "Fleet" ? WDFleet : WDArmy;
      unitFCs[unit.mappedTerritory.unitSlotName] = (
        <WDUnitComponent
          id={`${territoryName}-unit`} // n.b. the id here is ref'd by drawOrders, do not change!
          country={unit.country}
          meta={unit}
        />
      );
    });

  const clickAction = function (evt, clickObject: ClickObjectType) {
    dispatch(
      gameApiSliceActions.processMapClick({
        clickObject,
        evt,
        name: territoryMapData.name,
      }),
    );
  };

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
          return (
            <g key={id} className="no-pointer-events">
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
            {unitFCs[name]}
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
