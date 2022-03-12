import * as React from "react";
import { TerritoryMapData } from "../../../interfaces";
import WDCenter from "./WDCenter";
import WDLabel from "./WDLabel";
import WDUnitSlot from "./WDUnitSlot";

interface WDTerritoryProps {
  terr: TerritoryMapData;
}

const WDTerritory: React.FC<WDTerritoryProps> = function ({
  terr,
}): React.ReactElement {
  return (
    <svg
      id={`${terr.name}-territory`}
      viewBox={terr.viewBox}
      width={terr.width}
      height={terr.height}
      x={terr.x}
      y={terr.y}
    >
      {terr.texture?.texture && (
        <path
          id={`${terr.name}-texture`}
          d={terr.path}
          fill={terr.texture.texture}
          stroke={terr.texture.stroke}
          strokeWidth={terr.texture.strokeWidth}
          strokeOpacity={terr.texture.strokeOpacity}
        />
      )}
      <path
        id={`${terr.name}-control-path`}
        d={terr.path}
        fill={terr.fill}
        fillOpacity={0.2}
        stroke="black"
        strokeWidth="1"
        strokeOpacity={1}
      />
      {terr.centerPos && (
        <WDCenter terr={terr.name} x={terr.centerPos.x} y={terr.centerPos.y} />
      )}
      {terr.labels &&
        terr.labels.map(({ x, y, text, style }, i) => {
          let txt = text;
          let id: string | undefined;
          if (!txt) {
            txt = terr.abbr;
            id = `${terr.name}-label-main`;
          }
          return (
            <WDLabel
              key={id || i}
              style={style}
              id={id}
              x={x}
              y={y}
              text={txt}
            />
          );
        })}
      {terr.unitSlot && (
        <WDUnitSlot x={terr.unitSlot.x} y={terr.unitSlot.y} terr={terr.name} />
      )}
    </svg>
  );
};

export default WDTerritory;
