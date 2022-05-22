import Territory from "../../../../enums/map/variants/classic/Territory";
import UnitSlotName, {
  UnitSlotNames,
} from "../../../../types/map/UnitSlotName";
import territoriesMapData from "../../TerritoriesMapData";
import { TerritoryMapData } from "../../../../interfaces";

export interface MTerritory {
  parent?: Territory;
  territory: Territory;
  unitSlotName: UnitSlotName;
  territoryMapData: TerritoryMapData;
}

type ITerritoryMap = {
  [key: string]: MTerritory;
};

export const webdipNameToTerritory: { [key: string]: Territory } = {
  Clyde: Territory.CLYDE,
  Edinburgh: Territory.EDINBURGH,
  Liverpool: Territory.LIVERPOOL,
  Yorkshire: Territory.YORK,
  Wales: Territory.WALES,
  London: Territory.LONDON,
  Portugal: Territory.PORTUGAL,
  Spain: Territory.SPAIN,
  "North Africa": Territory.NORTH_AFRICA,
  Tunis: Territory.TUNIS,
  Naples: Territory.NAPLES,
  Rome: Territory.ROME,
  Tuscany: Territory.TUSCANY,
  Piedmont: Territory.PIEDMONT,
  Venice: Territory.VENICE,
  Apulia: Territory.APULIA,
  Greece: Territory.GREECE,
  Albania: Territory.ALBANIA,
  Serbia: Territory.SERBIA,
  Bulgaria: Territory.BULGARIA,
  Rumania: Territory.RUMANIA,
  Constantinople: Territory.CONSTANTINOPLE,
  Smyrna: Territory.SMYRNA,
  Ankara: Territory.ANKARA,
  Armenia: Territory.ARMENIA,
  Syria: Territory.SYRIA,
  Sevastopol: Territory.SEVASTOPOL,
  Ukraine: Territory.UKRAINE,
  Warsaw: Territory.WARSAW,
  Livonia: Territory.LIVONIA,
  Moscow: Territory.MOSCOW,
  "St. Petersburg": Territory.SAINT_PETERSBURG,
  Finland: Territory.FINLAND,
  Sweden: Territory.SWEDEN,
  Norway: Territory.NORWAY,
  Denmark: Territory.DENMARK,
  Kiel: Territory.KIEL,
  Berlin: Territory.BERLIN,
  Prussia: Territory.PRUSSIA,
  Silesia: Territory.SILESIA,
  Munich: Territory.MUNICH,
  Ruhr: Territory.RUHR,
  Holland: Territory.HOLLAND,
  Belgium: Territory.BELGIUM,
  Picardy: Territory.PICARDY,
  Brest: Territory.BREST,
  Paris: Territory.PARIS,
  Burgundy: Territory.BURGUNDY,
  Marseilles: Territory.MARSEILLES,
  Gascony: Territory.GASCONY,
  "Barents Sea": Territory.BARENTS_SEA,
  "Norwegian Sea": Territory.NORWEGIAN_SEA,
  "North Sea": Territory.NORTH_SEA,
  Skagerrack: Territory.SKAGERRACK,
  "Heligoland Bight": Territory.HELIGOLAND_BIGHT,
  "Baltic Sea": Territory.BALTIC_SEA,
  "Gulf of Bothnia": Territory.GULF_OF_BOTHNIA,
  "North Atlantic Ocean": Territory.NORTH_ATLANTIC,
  "Irish Sea": Territory.IRISH_SEA,
  "English Channel": Territory.ENGLISH_CHANNEL,
  "Mid-Atlantic Ocean": Territory.MIDDLE_ATLANTIC,
  "Western Mediterranean": Territory.WESTERN_MEDITERRANEAN,
  "Gulf of Lyons": Territory.GULF_OF_LYONS,
  "Tyrrhenian Sea": Territory.TYRRHENIAN_SEA,
  "Ionian Sea": Territory.IONIAN_SEA,
  "Adriatic Sea": Territory.ADRIATIC_SEA,
  "Aegean Sea": Territory.AEGEAN_SEA,
  "Eastern Mediterranean": Territory.EASTERN_MEDITERRANEAN,
  "Black Sea": Territory.BLACK_SEA,
  Tyrolia: Territory.TYROLIA,
  Bohemia: Territory.BOHEMIA,
  Vienna: Territory.VIENNA,
  Trieste: Territory.TRIESTE,
  Budapest: Territory.BUDAPEST,
  Galicia: Territory.GALICIA,
  "Spain (North Coast)": Territory.SPAIN_NORTH_COAST,
  "Spain (South Coast)": Territory.SPAIN_SOUTH_COAST,
  "St. Petersburg (North Coast)": Territory.SAINT_PETERSBURG_NORTH_COAST,
  "St. Petersburg (South Coast)": Territory.SAINT_PETERSBURG_SOUTH_COAST,
  "Bulgaria (North Coast)": Territory.BULGARIA_NORTH_COAST,
  "Bulgaria (South Coast)": Territory.BULGARIA_SOUTH_COAST,
};

export const territoryToWebdipName = Object.fromEntries(
  Object.entries(webdipNameToTerritory).map(([webdipName, territory]) => [
    territory,
    webdipName,
  ]),
);

export const coastData = {
  [Territory.SPAIN_NORTH_COAST]: {
    parent: Territory.SPAIN,
    unitSlotName: "nc",
  },
  [Territory.SPAIN_SOUTH_COAST]: {
    parent: Territory.SPAIN,
    unitSlotName: "sc",
  },
  [Territory.SAINT_PETERSBURG_NORTH_COAST]: {
    parent: Territory.SAINT_PETERSBURG,
    unitSlotName: "nc",
  },
  [Territory.SAINT_PETERSBURG_SOUTH_COAST]: {
    parent: Territory.SAINT_PETERSBURG,
    unitSlotName: "sc",
  },
  [Territory.BULGARIA_NORTH_COAST]: {
    parent: Territory.BULGARIA,
    unitSlotName: "nc",
  },
  [Territory.BULGARIA_SOUTH_COAST]: {
    parent: Territory.BULGARIA,
    unitSlotName: "sc",
  },
};

const territoryToMTerr: ITerritoryMap = Object.fromEntries(
  Object.entries(territoryToWebdipName).map(([territory, webdipName]) => [
    territory,
    {
      parent: coastData[territory]?.parent,
      territory: territory as Territory,
      unitSlotName: coastData[territory]?.unitSlotName || "main",
      territoryMapData:
        territoriesMapData[territory] ||
        territoriesMapData[coastData[territory]?.parent],
    },
  ]),
);

const webdipNameToMTerr = Object.fromEntries(
  Object.entries(territoryToMTerr).map(([territory, data]) => [
    territoryToWebdipName[territory],
    data,
  ]),
);

const TerritoryMap = { ...territoryToMTerr, ...webdipNameToMTerr };

export default TerritoryMap;
