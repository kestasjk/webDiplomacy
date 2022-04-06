import { useTheme } from "@mui/material";
import * as React from "react";
import countryMap from "../../../data/map/variants/classic/CountryMap";
import { TerritoryMapData } from "../../../interfaces";
import {
  gameApiSliceActions,
  gameCommands,
  gameOverview,
} from "../../../state/game/game-api-slice";
import { useAppDispatch, useAppSelector } from "../../../state/hooks";
import debounce from "../../../utils/debounce";
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
  // const [territoryFilter, setTerritoryFilter] = React.useState<
  //   string | undefined
  // >(undefined);
  const [territoryFill, setTerritoryFill] = React.useState<string | undefined>(
    territoryMapData.fill,
  );
  const [territoryFillOpacity, setTerritoryFillOpacity] = React.useState<
    number | undefined
  >(undefined);
  const [territoryStrokeOpacity, setTerritoryStrokeOpacity] = React.useState(1);

  const { territoryCommands } = useAppSelector(gameCommands);
  const {
    user: { member },
  } = useAppSelector(gameOverview);
  const userCountry = countryMap[member.country];

  const commands = territoryCommands[territoryMapData.name];

  if (commands && commands.size > 0) {
    const firstCommand = commands.entries().next().value;
    if (firstCommand) {
      const [key, value] = firstCommand;
      switch (value.command) {
        case "HOLD":
          // setTerritoryFilter(`url(#${userCountry}-hold)`);
          setTerritoryFill(theme.palette[userCountry].main);
          setTerritoryFillOpacity(0.9);
          setTerritoryStrokeOpacity(2);
          dispatch(
            gameApiSliceActions.deleteCommand({
              type: "territory",
              name: territoryMapData.name,
              command: key,
            }),
          );
          break;
        case "CAPTURED":
          if (value.data?.country) {
            // setTerritoryFilter(`url(#${value.data.country})`);
            setTerritoryFill(theme.palette[value.data.country].main);
            setTerritoryFillOpacity(0.4);
            setTerritoryStrokeOpacity(1);
            dispatch(
              gameApiSliceActions.deleteCommand({
                type: "territory",
                name: territoryMapData.name,
                command: key,
              }),
            );
          }
          break;
        default:
          break;
      }
    }
  }

  const clickAction = function (e) {
    dispatch(
      gameApiSliceActions.processTerritoryClick({
        name: territoryMapData.name,
      }),
    );
  };

  const handleClick = debounce((e) => {
    clickAction(e);
  }, 200);

  const handleSingleClick = (e) => {
    handleClick[0](e);
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
      <g onClick={handleSingleClick}>
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
          // filter={territoryFill}
          fillOpacity={territoryFillOpacity}
          id={`${territoryMapData.name}-control-path`}
          stroke={theme.palette.primary.main}
          strokeOpacity={1}
          strokeWidth={territoryStrokeOpacity}
        />
      </g>
      {territoryMapData.centerPos && (
        <WDCenter
          territoryName={territoryMapData.name}
          x={territoryMapData.centerPos.x}
          y={territoryMapData.centerPos.y}
        />
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
            <WDLabel
              id={id}
              key={id || i}
              style={style}
              text={txt}
              x={x}
              y={y}
            />
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
