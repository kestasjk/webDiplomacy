import * as React from "react";
import WDProvince from "../../../components/WDProvince";
import { Unit } from "../../../../../utils/map/getUnits";
import provincesMapData from "../../../../../data/map/ProvincesMapData";
import Province from "../../../../../enums/map/variants/classic/Province";
import { gameData, gameMaps, gameOrder } from "../../../../../state/game/game-api-slice";
import { useAppSelector } from "../../../../../state/hooks";
import { IProvinceStatus } from "../../../../../models/Interfaces";

interface WDBoardMapProps {
  units: Unit[];
  centersByProvince: { [key: string]: { ownerCountryID: string } };
}

const WDBoardMap: React.FC<WDBoardMapProps> = function ({
  units,
  centersByProvince,
}): React.ReactElement {
  const gameDataResponse = useAppSelector(gameData);
  const maps = useAppSelector(gameMaps);
  const provinceStatusByProvID: { [key: string]: IProvinceStatus } = {};
  gameDataResponse.data.territoryStatuses.forEach((provinceStatus) => {
    provinceStatusByProvID[maps.terrIDToProvince[provinceStatus.id]] =
      provinceStatus;
  });

  const curOrder = useAppSelector(gameOrder);

  const unplayableProvinces = Object.values(provincesMapData)
    .filter((data) => !data.playable)
    .map((data) => {
      return (
        <WDProvince
          provinceMapData={data}
          ownerCountryID={centersByProvince[data.province]?.ownerCountryID}
          units={units}
          key={`${data.province}-province`}
        />
      );
    });
  // Hack - Rome and Naples need to be sorted to the end or else their label will get cut
  // off by neighboring territories drawn on top of it.
  const playableProvincesData = Object.values(provincesMapData).filter(
    (data) =>
      data.playable &&
      data.province !== Province.NAPLES &&
      data.province !== Province.ROME,
  );
  playableProvincesData.push(provincesMapData[Province.NAPLES]);
  playableProvincesData.push(provincesMapData[Province.ROME]);

  const playableProvinces = playableProvincesData.map((data) => {
    return (
      <WDProvince
        provinceMapData={data}
        ownerCountryID={centersByProvince[data.province]?.ownerCountryID}
        units={units}
        key={`${data.province}-province`}
      />
    );
  });

  return (
    <g id="wD-boardmap-v10.3.4 1" clipPath="url(#clip0_3405_33911)">
      <g id="unplayable">{unplayableProvinces}</g>
      <g id="playableProvinces">{playableProvinces}</g>
    </g>
  );
};

export default WDBoardMap;
