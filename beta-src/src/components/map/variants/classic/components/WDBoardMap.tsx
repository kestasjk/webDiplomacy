import * as React from "react";
import WDTerritory from "../../../components/WDTerritory";
import { Unit } from "../../../../../utils/map/getUnits";
import territoriesMapData from "../../../../../data/map/TerritoriesMapData";
import Territory from "../../../../../enums/map/variants/classic/Territory";

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
        key={`${data.territory}-territory`}
      />
    ));
  // Hack - Rome and Naples need to be sorted to the end or else their label will get cut
  // off by neighboring territories drawn on top of it.
  const playableTerritoriesData = Object.values(territoriesMapData).filter(
    (data) =>
      data.playable &&
      data.territory !== Territory.NAPLES &&
      data.territory !== Territory.ROME,
  );
  playableTerritoriesData.push(territoriesMapData[Territory.NAPLES]);
  playableTerritoriesData.push(territoriesMapData[Territory.ROME]);

  const playableTerritories = playableTerritoriesData.map((data) => (
    <WDTerritory
      territoryMapData={data}
      units={units}
      key={`${data.territory}-territory`}
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
