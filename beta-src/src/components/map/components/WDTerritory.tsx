import { useTheme } from "@mui/material";
import * as React from "react";
import countryMap from "../../../data/map/variants/classic/CountryMap";
import { TerritoryMapData } from "../../../interfaces";
import { gameApiSliceActions } from "../../../state/game/game-api-slice";
import { useAppDispatch, useAppSelector } from "../../../state/hooks";
import ClickObjectType from "../../../types/state/ClickObjectType";
import processNextCommand from "../../../utils/processNextCommand";
import WDArmyIcon from "../../ui/units/WDArmyIcon";
import WDFleetIcon from "../../ui/units/WDFleetIcon";
import WDUnit from "../../ui/units/WDUnit";
import WDCenter from "./WDCenter";
import WDLabel from "./WDLabel";
import WDUnitSlot from "./WDUnitSlot";

interface WDTerritoryProps {
  territoryMapData: TerritoryMapData;
}

interface Units {
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

  const [coastalClickable, setCoastalClickable] = React.useState(
    territoryMapData.type === "coast" ? "no-pointer-events" : "",
  );

  const [units, setUnits] = React.useState<Units>({});

  const commands = useAppSelector(
    (state) => state.game.commands.territoryCommands[territoryMapData.name],
  );

  let userCountry = useAppSelector(
    (state) => state.game.overview.user.member.country,
  );

  userCountry = countryMap[userCountry];

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
      country === "none"
        ? setTerritoryFill("none")
        : setTerritoryFill(theme.palette[country].main);
    } else {
      setTerritoryFill(theme.palette[userCountry].main);
    }
    setTerritoryFillOpacity(0.4);
    setTerritoryStrokeOpacity(1);
  };

  const commandActions = {
    CAPTURED: (command) => {
      const [key, value] = command;
      territoryMapData.type === "water"
        ? setTerritoryFill("none")
        : setCapturedHighlight(value.data?.country);
      deleteCommand(key);
    },
    DISABLE_COAST: (command) => {
      const [key] = command;
      setCoastalClickable("no-pointer-events");
      deleteCommand(key);
    },
    ENABLE_COAST: (command) => {
      const [key] = command;
      setCoastalClickable("");
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
      className={coastalClickable}
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
          fill={
            territoryMapData.type === "coast"
              ? "rgba(0, 0, 0, .001)"
              : territoryFill
          }
          fillOpacity={territoryFillOpacity}
          id={`${territoryMapData.name}-control-path`}
          stroke={
            territoryMapData.stroke
              ? territoryMapData.stroke
              : theme.palette.primary.main
          }
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
