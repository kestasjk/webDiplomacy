import { useTheme } from "@mui/material";
import * as React from "react";
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

  const commandActions = {
    SET_UNIT: (command) => {
      const [key, value] = command;
      console.log({ key, value });
      const {
        componentType,
        country,
        iconState,
        mappedTerritory,
        unit,
        unitType,
        unitSlotName,
      } = value.data.setUnit;

      console.log({
        componentType,
        country,
        iconState,
        mappedTerritory,
        unit,
        unitType,
        unitSlotName,
      });

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
                  <WDArmyIcon country={country} iconState={iconState} />
                );
                break;
              case "Fleet":
                newUnit = (
                  <WDFleetIcon country={country} iconState={iconState} />
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
    CAPTURED: (command) => {
      const [key, value] = command;
      if (value.data?.country) {
        if (value.data.country === "none") {
          setTerritoryFill("none");
        } else {
          setTerritoryFill(theme.palette[value.data.country].main);
        }
      } else {
        setTerritoryFill(theme.palette[userCountry].main);
      }
      setTerritoryFillOpacity(0.4);
      setTerritoryStrokeOpacity(1);
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
      setTerritoryFill(theme.palette[userCountry].main);
      setTerritoryFillOpacity(0.9);
      setTerritoryStrokeOpacity(1);
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
        territoryMapData.labels.map(({ x, y, text, style }, i) => {
          let txt = text;
          let id: string | undefined;
          if (!txt) {
            txt = territoryMapData.abbr;
            id = `${territoryMapData.name}-label-main`;
          }
          return (
            <g className="no-pointer-events">
              <WDLabel
                id={id}
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
