import { useTheme } from "@mui/material";
import * as React from "react";
import { TerritoryMapData } from "../../../interfaces";
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
        fill={territoryMapData.fill}
        fillOpacity={territoryMapData.texture?.texture ? 0.5 : 1}
        filter={territoryMapData.texture?.texture}
        id={`${territoryMapData.name}-control-path`}
        stroke={theme.palette.primary.main}
        strokeOpacity={1}
        strokeWidth={1}
      />
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
