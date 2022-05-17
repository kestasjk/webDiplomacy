import * as React from "react";
import WDTerritory from "../../../components/WDTerritory";
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
  BULGARIA_NORTH_COAST,
  BULGARIA_SOUTH_COAST,
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
  SAINT_PETERSBURG_SOUTH_COAST,
  SAINT_PETERSBURG_NORTH_COAST,
  SERBIA,
  SEVASTOPOL,
  SILESIA,
  SMYRNA,
  SPAIN_NORTH_COAST,
  SPAIN_SOUTH_COAST,
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

const WDBoardMap: React.FC = function (): React.ReactElement {
  return (
    <g id="wD-boardmap-v10.3.4 1" clipPath="url(#clip0_3405_33911)">
      <g id="sea">
        <WDTerritory territoryMapData={UNPLAYABLE_SEA1} />
        <WDTerritory territoryMapData={UNPLAYABLE_SEA2} />
        <WDTerritory territoryMapData={UNPLAYABLE_SEA3} />
        <WDTerritory territoryMapData={UNPLAYABLE_SEA4} />
        <WDTerritory territoryMapData={UNPLAYABLE_SEA5} />
        <WDTerritory territoryMapData={UNPLAYABLE_SEA6} />
        <WDTerritory territoryMapData={UNPLAYABLE_SEA7} />
        <WDTerritory territoryMapData={UNPLAYABLE_SEA8} />
        <WDTerritory territoryMapData={UNPLAYABLE_SEA9} />
        <WDTerritory territoryMapData={ADRIATIC_SEA} />
        <WDTerritory territoryMapData={AEGEAN_SEA} />
        <WDTerritory territoryMapData={BALTIC_SEA} />
        <WDTerritory territoryMapData={BARENTS_SEA} />
        <WDTerritory territoryMapData={BLACK_SEA} />
        <WDTerritory territoryMapData={CHANNEL_1} />
        <WDTerritory territoryMapData={EASTERN_MEDITERRANEAN} />
        <WDTerritory territoryMapData={ENGLISH_CHANNEL} />
        <WDTerritory territoryMapData={GULF_OF_BOTHNIA} />
        <WDTerritory territoryMapData={GULF_OF_LYONS} />
        <WDTerritory territoryMapData={HELIGOLAND_BIGHT} />
        <WDTerritory territoryMapData={IONIAN_SEA} />
        <WDTerritory territoryMapData={IRISH_SEA} />
        <WDTerritory territoryMapData={MIDDLE_ATLANTIC} />
        <WDTerritory territoryMapData={NORTH_ATLANTIC} />
        <WDTerritory territoryMapData={NORTH_ATLANTIC2} />
        <WDTerritory territoryMapData={NORTH_SEA} />
        <WDTerritory territoryMapData={NORWEGIAN_SEA} />
        <WDTerritory territoryMapData={SKAGERRACK} />
        <WDTerritory territoryMapData={SKAGERRACK2} />
        <WDTerritory territoryMapData={TYRRHENIAN_SEA} />
        <WDTerritory territoryMapData={WESTERN_MEDITERRANEAN} />
      </g>
      <g id="outlines">
        <WDTerritory territoryMapData={ALBANIA} />
        <WDTerritory territoryMapData={ANKARA} />
        <WDTerritory territoryMapData={APULIA} />
        <WDTerritory territoryMapData={ARMENIA} />
        <WDTerritory territoryMapData={BELGIUM} />
        <WDTerritory territoryMapData={BERLIN} />
        <WDTerritory territoryMapData={BOHEMIA} />
        <WDTerritory territoryMapData={BREST} />
        <WDTerritory territoryMapData={BUDAPEST} />
        <WDTerritory territoryMapData={BULGARIA_NORTH_COAST} />
        <WDTerritory territoryMapData={BULGARIA_SOUTH_COAST} />
        <WDTerritory territoryMapData={BULGARIA} />
        <WDTerritory territoryMapData={BURGUNDY} />
        <WDTerritory territoryMapData={CLYDE} />
        <WDTerritory territoryMapData={CONSTANTINOPLE} />
        <WDTerritory territoryMapData={DENMARK} />
        <WDTerritory territoryMapData={EDINBURGH} />
        <WDTerritory territoryMapData={FINLAND} />
        <WDTerritory territoryMapData={GALICIA} />
        <WDTerritory territoryMapData={GASCONY} />
        <WDTerritory territoryMapData={GREECE} />
        <WDTerritory territoryMapData={HOLLAND} />
        <WDTerritory territoryMapData={KIEL} />
        <WDTerritory territoryMapData={LIVERPOOL} />
        <WDTerritory territoryMapData={LIVONIA} />
        <WDTerritory territoryMapData={LONDON} />
        <WDTerritory territoryMapData={MARSEILLES} />
        <WDTerritory territoryMapData={MOSCOW} />
        <WDTerritory territoryMapData={MUNICH} />
        <WDTerritory territoryMapData={NEUTRAL_1} />
        <WDTerritory territoryMapData={NEUTRAL_2} />
        <WDTerritory territoryMapData={NEUTRAL_3} />
        <WDTerritory territoryMapData={NEUTRAL_4} />
        <WDTerritory territoryMapData={NEUTRAL_5} />
        <WDTerritory territoryMapData={NEUTRAL_6} />
        <WDTerritory territoryMapData={NEUTRAL_7} />
        <WDTerritory territoryMapData={NEUTRAL_8} />
        <WDTerritory territoryMapData={NEUTRAL_9} />
        <WDTerritory territoryMapData={NORTH_AFRICA} />
        <WDTerritory territoryMapData={NORWAY} />
        <WDTerritory territoryMapData={PARIS} />
        <WDTerritory territoryMapData={PICARDY} />
        <WDTerritory territoryMapData={PORTUGAL} />
        <WDTerritory territoryMapData={PRUSSIA} />
        <WDTerritory territoryMapData={RUHR} />
        <WDTerritory territoryMapData={RUMANIA} />
        <WDTerritory territoryMapData={SAINT_PETERSBURG_NORTH_COAST} />
        <WDTerritory territoryMapData={SAINT_PETERSBURG_SOUTH_COAST} />
        <WDTerritory territoryMapData={SAINT_PETERSBURG} />
        <WDTerritory territoryMapData={SERBIA} />
        <WDTerritory territoryMapData={SEVASTOPOL} />
        <WDTerritory territoryMapData={SILESIA} />
        <WDTerritory territoryMapData={SMYRNA} />
        <WDTerritory territoryMapData={SPAIN_NORTH_COAST} />
        <WDTerritory territoryMapData={SPAIN_SOUTH_COAST} />
        <WDTerritory territoryMapData={SPAIN} />
        <WDTerritory territoryMapData={SWEDEN} />
        <WDTerritory territoryMapData={SYRIA} />
        <WDTerritory territoryMapData={TRIESTE} />
        <WDTerritory territoryMapData={TUNIS} />
        <WDTerritory territoryMapData={TYROLIA} />
        <WDTerritory territoryMapData={UKRAINE} />
        <WDTerritory territoryMapData={VENICE} />
        <WDTerritory territoryMapData={VIENNA} />
        <WDTerritory territoryMapData={WALES} />
        <WDTerritory territoryMapData={WARSAW} />
        <WDTerritory territoryMapData={YORK} />
        {/* These need to be last so the labels and units will appear */}
        <WDTerritory territoryMapData={NAPLES} />
        <WDTerritory territoryMapData={PIEDMONT} />
        <WDTerritory territoryMapData={ROME} />
        <WDTerritory territoryMapData={TUSCANY} />
      </g>
      <g id="unplayable">
        <WDTerritory territoryMapData={UNPLAYABLE_LAND1} />
        <WDTerritory territoryMapData={UNPLAYABLE_LAND2} />
        <WDTerritory territoryMapData={UNPLAYABLE_LAND3} />
        <WDTerritory territoryMapData={UNPLAYABLE_LAND4} />
        <WDTerritory territoryMapData={UNPLAYABLE_LAND5} />
        <WDTerritory territoryMapData={UNPLAYABLE_LAND6} />
        <WDTerritory territoryMapData={UNPLAYABLE_LAND7} />
        <WDTerritory territoryMapData={UNPLAYABLE_LAND8} />
        <WDTerritory territoryMapData={UNPLAYABLE_SEA1} />
        <WDTerritory territoryMapData={UNPLAYABLE_SEA2} />
        <WDTerritory territoryMapData={UNPLAYABLE_SEA3} />
        <WDTerritory territoryMapData={UNPLAYABLE_SEA4} />
        <WDTerritory territoryMapData={UNPLAYABLE_SEA5} />
        <WDTerritory territoryMapData={UNPLAYABLE_SEA6} />
        <WDTerritory territoryMapData={UNPLAYABLE_SEA7} />
        <WDTerritory territoryMapData={UNPLAYABLE_SEA8} />
        <WDTerritory territoryMapData={UNPLAYABLE_SEA9} />
      </g>
      {/* <WDLandTexture /> */}
      {/* <WDSeaTexture /> */}
    </g>
  );
};

export default WDBoardMap;
