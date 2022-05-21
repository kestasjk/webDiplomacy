import * as React from "react";
import WDTerritory from "../../../components/WDTerritory";
import { Unit } from "../../../../../utils/map/getUnits";
import territoriesMapData from "../../../../../data/map/TerritoriesMapData";

interface WDBoardMapProps {
  units: Unit[];
}

const WDBoardMap: React.FC<WDBoardMapProps> = function ({
  units,
}): React.ReactElement {
  const unplayableTerritories = Object.values(territoriesMapData)
    .filter((data) => !data.playable)
    .map((data) => (
      <WDTerritory
        territoryMapData={data}
        units={units}
        key={`${data.name}-territory`}
      />
    ));
  const playableTerritories = Object.values(territoriesMapData)
    .filter((data) => data.playable)
    .map((data) => (
      <WDTerritory
        territoryMapData={data}
        units={units}
        key={`${data.name}-territory`}
      />
    ));

  return (
    <g id="wD-boardmap-v10.3.4 1" clipPath="url(#clip0_3405_33911)">
      <g id="unplayable">{unplayableTerritories}</g>
      <g id="playableTerritories">{playableTerritories}</g>
    </g>
  );
};

export default WDBoardMap;
