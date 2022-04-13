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
import WDCenter from "./WDCenter";
import WDLabel from "./WDLabel";
import WDUnitSlot from "./WDUnitSlot";

interface WDTerritoryProps {
  territoryMapData: TerritoryMapData;
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
    HOLD: (command) => {
      const [key] = command;
      setTerritoryFill(theme.palette[userCountry].main);
      setTerritoryFillOpacity(0.9);
      setTerritoryStrokeOpacity(2);
      deleteCommand(key);
    },
    CAPTURED: (command) => {
      const [key, value] = command;
      if (value.data?.country) {
        setTerritoryFill(theme.palette[value.data.country].main);
      } else {
        setTerritoryFill(theme.palette[userCountry].main);
      }
      setTerritoryFillOpacity(0.4);
      setTerritoryStrokeOpacity(1);
      deleteCommand(key);
    },
  };

  processNextCommand(commands, commandActions);

  const clickAction = function (clickObject: ClickObjectType) {
    dispatch(
      gameApiSliceActions.processMapClick({
        name: territoryMapData.name,
        clickObject,
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
        onClick={() => clickAction("territory")}
        stroke={theme.palette.primary.main}
        strokeOpacity={1}
        strokeWidth={territoryStrokeOpacity}
      />
      {territoryMapData.centerPos && (
        <g onClick={() => clickAction("center")}>
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
            <g onClick={() => clickAction("label")}>
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
        territoryMapData.unitSlots.map((unitSlot) => (
          <WDUnitSlot
            key={unitSlot.name}
            name={unitSlot.name}
            territoryName={territoryMapData.name}
            x={unitSlot.x}
            y={unitSlot.y}
          />
        ))}
    </svg>
  );
};

export default WDTerritory;
