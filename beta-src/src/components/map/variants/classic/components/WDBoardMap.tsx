import * as React from "react";
import WDProvince from "../../../components/WDProvince";
import { Unit } from "../../../../../utils/map/getUnits";
import provincesMapData from "../../../../../data/map/ProvincesMapData";
import Territory from "../../../../../enums/map/variants/classic/Territory";
import { gameTerritoriesMeta } from "../../../../../state/game/game-api-slice";
import { useAppSelector } from "../../../../../state/hooks";

interface WDBoardMapProps {
  units: Unit[];
}

const WDBoardMap: React.FC<WDBoardMapProps> = function ({
  units,
}): React.ReactElement {
  const territoriesMeta = useAppSelector(gameTerritoriesMeta);

  const unplayableTerritories = Object.values(provincesMapData)
    .filter((data) => !data.playable)
    .map((data) => {
      const territoryMeta = territoriesMeta[data.territory];
      return (
        <WDProvince
          provinceMapData={data}
          territoryMeta={territoryMeta}
          units={units}
          key={`${data.territory}-territory`}
        />
      );
    });
  // Hack - Rome and Naples need to be sorted to the end or else their label will get cut
  // off by neighboring territories drawn on top of it.
  const playableTerritoriesData = Object.values(provincesMapData).filter(
    (data) =>
      data.playable &&
      data.territory !== Territory.NAPLES &&
      data.territory !== Territory.ROME,
  );
  playableTerritoriesData.push(provincesMapData[Territory.NAPLES]);
  playableTerritoriesData.push(provincesMapData[Territory.ROME]);

  const playableTerritories = playableTerritoriesData.map((data) => {
    const territoryMeta = territoriesMeta[data.territory];
    return (
      <WDProvince
        provinceMapData={data}
        territoryMeta={territoryMeta}
        units={units}
        key={`${data.territory}-territory`}
      />
    );
  });

  return (
    <g id="wD-boardmap-v10.3.4 1" clipPath="url(#clip0_3405_33911)">
      <g id="unplayable">{unplayableTerritories}</g>
      <g id="playableTerritories">{playableTerritories}</g>
    </g>
  );
};

export default WDBoardMap;
