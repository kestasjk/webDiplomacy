import * as React from "react";
import WDTerritory from "../../../components/WDTerritory";
import { Unit } from "../../../../../utils/map/getUnits";
import {
  ALBANIA,
  ANKARA,
  APULIA,
  ARMENIA,
  BELGIUM,
  BERLIN,
  BOHEMIA,
  BREST,
  BUDAPEST,
  BULGARIA,
  BURGUNDY,
  CLYDE,
  CONSTANTINOPLE,
  DENMARK,
  EDINBURGH,
  FINLAND,
  GALICIA,
  GASCONY,
  GREECE,
  HOLLAND,
  KIEL,
  LIVERPOOL,
  LIVONIA,
  LONDON,
  MARSEILLES,
  MOSCOW,
  MUNICH,
  NAPLES,
  NEUTRAL_1,
  NEUTRAL_2,
  NEUTRAL_3,
  NEUTRAL_4,
  NEUTRAL_5,
  NEUTRAL_6,
  NEUTRAL_7,
  NEUTRAL_8,
  NEUTRAL_9,
  NORTH_AFRICA,
  NORWAY,
  PARIS,
  PICARDY,
  PIEDMONT,
  PORTUGAL,
  PRUSSIA,
  ROME,
  RUHR,
  RUMANIA,
  SAINT_PETERSBURG,
  SERBIA,
  SEVASTOPOL,
  SILESIA,
  SMYRNA,
  SPAIN,
  SWEDEN,
  SYRIA,
  TRIESTE,
  TUNIS,
  TUSCANY,
  TYROLIA,
  UKRAINE,
  UNPLAYABLE_LAND1,
  UNPLAYABLE_LAND2,
  UNPLAYABLE_LAND3,
  UNPLAYABLE_LAND4,
  UNPLAYABLE_LAND5,
  UNPLAYABLE_LAND6,
  UNPLAYABLE_LAND7,
  UNPLAYABLE_LAND8,
  VIENNA,
  VENICE,
  WALES,
  WARSAW,
  YORK,
} from "../../../../../data/map/land/LandTerritoriesMapData";
import {
  ADRIATIC_SEA,
  AEGEAN_SEA,
  BALTIC_SEA,
  BARENTS_SEA,
  BLACK_SEA,
  CHANNEL_1,
  EASTERN_MEDITERRANEAN,
  ENGLISH_CHANNEL,
  GULF_OF_BOTHNIA,
  GULF_OF_LYONS,
  HELIGOLAND_BIGHT,
  IONIAN_SEA,
  IRISH_SEA,
  MIDDLE_ATLANTIC,
  NORTH_ATLANTIC,
  NORTH_ATLANTIC2,
  NORTH_SEA,
  NORWEGIAN_SEA,
  SKAGERRACK,
  SKAGERRACK2,
  TYRRHENIAN_SEA,
  UNPLAYABLE_SEA1,
  UNPLAYABLE_SEA2,
  UNPLAYABLE_SEA3,
  UNPLAYABLE_SEA4,
  UNPLAYABLE_SEA5,
  UNPLAYABLE_SEA6,
  UNPLAYABLE_SEA7,
  UNPLAYABLE_SEA8,
  UNPLAYABLE_SEA9,
  WESTERN_MEDITERRANEAN,
} from "../../../../../data/map/sea/SeaTerritoriesMapData";
// import WDLandTexture from "./WDLandTexture";
// import WDSeaTexture from "./WDSeaTexture";

interface WDBoardMapProps {
  units: Unit[];
}

const UNPLAYABLE_DATA = [
  UNPLAYABLE_SEA1,
  UNPLAYABLE_SEA2,
  UNPLAYABLE_SEA3,
  UNPLAYABLE_SEA4,
  UNPLAYABLE_SEA5,
  UNPLAYABLE_SEA6,
  UNPLAYABLE_SEA7,
  UNPLAYABLE_SEA8,
  UNPLAYABLE_SEA9,
  UNPLAYABLE_LAND1,
  UNPLAYABLE_LAND2,
  UNPLAYABLE_LAND3,
  UNPLAYABLE_LAND4,
  UNPLAYABLE_LAND5,
  UNPLAYABLE_LAND6,
  UNPLAYABLE_LAND7,
  UNPLAYABLE_LAND8,
];
const SEA_DATA = [
  ADRIATIC_SEA,
  AEGEAN_SEA,
  BALTIC_SEA,
  BARENTS_SEA,
  BLACK_SEA,
  CHANNEL_1,
  EASTERN_MEDITERRANEAN,
  ENGLISH_CHANNEL,
  GULF_OF_BOTHNIA,
  GULF_OF_LYONS,
  HELIGOLAND_BIGHT,
  IONIAN_SEA,
  IRISH_SEA,
  MIDDLE_ATLANTIC,
  NORTH_ATLANTIC,
  NORTH_ATLANTIC2,
  NORTH_SEA,
  NORWEGIAN_SEA,
  SKAGERRACK,
  SKAGERRACK2,
  TYRRHENIAN_SEA,
  WESTERN_MEDITERRANEAN,
];
const LAND_DATA = [
  ALBANIA,
  ANKARA,
  APULIA,
  ARMENIA,
  BELGIUM,
  BERLIN,
  BOHEMIA,
  BREST,
  BUDAPEST,
  BULGARIA,
  BURGUNDY,
  CLYDE,
  CONSTANTINOPLE,
  DENMARK,
  EDINBURGH,
  FINLAND,
  GALICIA,
  GASCONY,
  GREECE,
  HOLLAND,
  KIEL,
  LIVERPOOL,
  LIVONIA,
  LONDON,
  MARSEILLES,
  MOSCOW,
  MUNICH,
  NEUTRAL_1,
  NEUTRAL_2,
  NEUTRAL_3,
  NEUTRAL_4,
  NEUTRAL_5,
  NEUTRAL_6,
  NEUTRAL_7,
  NEUTRAL_8,
  NEUTRAL_9,
  NORTH_AFRICA,
  NORWAY,
  PARIS,
  PICARDY,
  PORTUGAL,
  PRUSSIA,
  RUHR,
  RUMANIA,
  SAINT_PETERSBURG,
  SERBIA,
  SEVASTOPOL,
  SILESIA,
  SMYRNA,
  SPAIN,
  SWEDEN,
  SYRIA,
  TRIESTE,
  TUNIS,
  TYROLIA,
  UKRAINE,
  VENICE,
  VIENNA,
  WALES,
  WARSAW,
  YORK,
  /* These need to be last so the labels and units will appear */
  NAPLES,
  PIEDMONT,
  ROME,
  TUSCANY,
];

const WDBoardMap: React.FC<WDBoardMapProps> = function ({
  units,
}): React.ReactElement {
  const unplayableTerritories: React.ReactElement[] = [];
  UNPLAYABLE_DATA.forEach((data) => {
    unplayableTerritories.push(
      <WDTerritory
        territoryMapData={data}
        units={units}
        key={`${data.name}-territory`}
      />,
    );
  });
  const seaTerritories: React.ReactElement[] = [];
  SEA_DATA.forEach((data) => {
    seaTerritories.push(
      <WDTerritory
        territoryMapData={data}
        units={units}
        key={`${data.name}-territory`}
      />,
    );
  });
  const landTerritories: React.ReactElement[] = [];
  LAND_DATA.forEach((data) => {
    landTerritories.push(
      <WDTerritory
        territoryMapData={data}
        units={units}
        key={`${data.name}-territory`}
      />,
    );
  });

  return (
    <g id="wD-boardmap-v10.3.4 1" clipPath="url(#clip0_3405_33911)">
      <g id="unplayable">{unplayableTerritories}</g>
      <g id="sea">{seaTerritories}</g>
      <g id="outlines">{landTerritories}</g>
    </g>
  );
};

export default WDBoardMap;
