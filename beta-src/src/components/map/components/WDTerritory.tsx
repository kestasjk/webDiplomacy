import { Popover, useTheme } from "@mui/material";
import * as React from "react";
import BuildUnitMap, { BuildUnitTypeMap } from "../../../data/BuildUnit";
import countryMap from "../../../data/map/variants/classic/CountryMap";
import { TerritoryMapData } from "../../../interfaces";
import {
  gameApiSliceActions,
  gameOverview,
} from "../../../state/game/game-api-slice";
import { useAppDispatch, useAppSelector } from "../../../state/hooks";
import ClickObjectType from "../../../types/state/ClickObjectType";
import processNextCommand from "../../../utils/processNextCommand";
import WDArmy from "../../ui/units/WDArmy";
import WDArmyIcon from "../../ui/units/WDArmyIcon";
import WDFleet from "../../ui/units/WDFleet";
import WDFleetIcon from "../../ui/units/WDFleetIcon";
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
  [key: string]: React.ReactElement;
}

const WDTerritory: React.FC<WDTerritoryProps> = function ({
  territoryMapData,
}): React.ReactElement {
  const theme = useTheme();
  const dispatch = useAppDispatch();

  const [territoryFill, setTerritoryFill] = React.useState<string | undefined>(
    territoryMapData.fill,
  );

  const [territoryFillOpacity, setTerritoryFillOpacity] = React.useState<
    number | undefined
  >(undefined);

  const [territoryStrokeOpacity, setTerritoryStrokeOpacity] = React.useState(1);

  const buildPopoverRefs = React.useRef<SVGElement[]>([]);

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
    setTerritoryFill(theme.palette[userCountry].main);
    setTerritoryFillOpacity(0.9);
    setTerritoryStrokeOpacity(1);
  };

  const setCapturedHighlight = (country) => {
    if (country) {
      if (country === "none") {
        setTerritoryFill("none");
      } else {
        setTerritoryFill(theme.palette[country].main);
      }
    } else {
      setTerritoryFill(theme.palette[userCountry].main);
    }
    setTerritoryFillOpacity(0.4);
    setTerritoryStrokeOpacity(1);
  };

  const defaultBuildPopoversClose = () => {
    setCapturedHighlight(undefined);
    setOpenBuildPopovers(false);
  };

  const userBuildPopoversClose = () => {
    setOpenBuildPopovers(false);
  };

  const build = (availableOrder, canBuild, toTerrID) => {
    console.log({ availableOrder, canBuild, toTerrID });
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
    userBuildPopoversClose();
  };

  const commandActions = {
    BUILD: (command) => {
      const [key, value] = command;
      const { availableOrder, canBuild, toTerrID } = value.data.build;
      console.log({
        command,
      });
      const buildPopover = (
        <WDBuildUnitButtons
          availableOrder={availableOrder}
          canBuild={canBuild}
          country={userCountry}
          toTerrID={toTerrID}
          clickCallback={build}
        />
      );
      setBuildPopovers({
        main: buildPopover,
      });
      setMoveHighlight();
      setOpenBuildPopovers(true);
      deleteCommand(key);
    },
    CAPTURED: (command) => {
      const [key, value] = command;
      setCapturedHighlight(value.data?.country);
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
              switch (unitType) {
                case "Army":
                  newUnit = (
                    <WDArmy
                      id={`${territoryMapData.name}-unit`}
                      country={country}
                      meta={{ country, mappedTerritory, unit }}
                    />
                  );
                  break;
                case "Fleet":
                  newUnit = (
                    <WDFleet
                      id={`${territoryMapData.name}-unit`}
                      country={country}
                      meta={{ country, mappedTerritory, unit }}
                    />
                  );
                  break;
                default:
                  break;
              }
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
            <g
              className="no-pointer-events"
              ref={(el) => {
                if (el) {
                  buildPopoverRefs.current.push(el);
                }
              }}
            >
              <WDLabel
                id={id}
                name={name}
                key={id || i}
                style={style}
                text={txt}
                x={x}
                y={y}
              />
              <Popover
                id={id}
                open={openBuildPopovers}
                anchorEl={buildPopoverRefs.current[i]}
                onClose={defaultBuildPopoversClose}
                anchorOrigin={{
                  vertical: "top",
                  horizontal: "center",
                }}
                transformOrigin={{
                  vertical: "bottom",
                  horizontal: "center",
                }}
                PaperProps={{
                  sx: {
                    background: "rgba(0,0,0,.7)",
                    borderRadius: 2,
                    display: "flex",
                    justifyContent: "space-evenly",
                  },
                }}
              >
                {buildPopovers[name]}
              </Popover>
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
