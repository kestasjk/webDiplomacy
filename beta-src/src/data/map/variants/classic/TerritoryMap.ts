import Territory from "../../../../enums/map/variants/classic/Territory";
import UnitSlotName from "../../../../types/map/UnitSlotName";

export interface MTerritory {
  parent?: Territory;
  territory: Territory;
  territoryName: string;
  parentName?: string;
  unitSlotName: UnitSlotName;
}

type ITerritoryMap = {
  [key: string]: MTerritory;
};

/**
 * This file maps territory names to local app enums.
 */
const TerritoryMap: ITerritoryMap = {
  Clyde: {
    territory: Territory.CLYDE,
    territoryName: "CLYDE",
    unitSlotName: "main",
  },
  Edinburgh: {
    territory: Territory.EDINBURGH,
    territoryName: "EDINBURGH",
    unitSlotName: "main",
  },
  Liverpool: {
    territory: Territory.LIVERPOOL,
    territoryName: "LIVERPOOL",
    unitSlotName: "main",
  },
  Yorkshire: {
    territory: Territory.YORK,
    territoryName: "YORK",
    unitSlotName: "main",
  },
  Wales: {
    territory: Territory.WALES,
    territoryName: "WALES",
    unitSlotName: "main",
  },
  London: {
    territory: Territory.LONDON,
    territoryName: "LONDON",
    unitSlotName: "main",
  },
  Portugal: {
    territory: Territory.PORTUGAL,
    territoryName: "PORTUGAL",
    unitSlotName: "main",
  },
  Spain: {
    territory: Territory.SPAIN,
    territoryName: "SPAIN",
    unitSlotName: "main",
  },
  "North Africa": {
    territory: Territory.NORTH_AFRICA,
    territoryName: "NORTH_AFRICA",
    unitSlotName: "main",
  },
  Tunis: {
    territory: Territory.TUNIS,
    territoryName: "TUNIS",
    unitSlotName: "main",
  },
  Naples: {
    territory: Territory.NAPLES,
    territoryName: "NAPLES",
    unitSlotName: "main",
  },
  Rome: {
    territory: Territory.ROME,
    territoryName: "ROME",
    unitSlotName: "main",
  },
  Tuscany: {
    territory: Territory.TUSCANY,
    territoryName: "TUSCANY",
    unitSlotName: "main",
  },
  Piedmont: {
    territory: Territory.PIEDMONT,
    territoryName: "PIEDMONT",
    unitSlotName: "main",
  },
  Venice: {
    territory: Territory.VENICE,
    territoryName: "VENICE",
    unitSlotName: "main",
  },
  Apulia: {
    territory: Territory.APULIA,
    territoryName: "APULIA",
    unitSlotName: "main",
  },
  Greece: {
    territory: Territory.GREECE,
    territoryName: "GREECE",
    unitSlotName: "main",
  },
  Albania: {
    territory: Territory.ALBANIA,
    territoryName: "ALBANIA",
    unitSlotName: "main",
  },
  Serbia: {
    territory: Territory.SERBIA,
    territoryName: "SERBIA",
    unitSlotName: "main",
  },
  Bulgaria: {
    territory: Territory.BULGARIA,
    territoryName: "BULGARIA",
    unitSlotName: "main",
  },
  Rumania: {
    territory: Territory.RUMANIA,
    territoryName: "RUMANIA",
    unitSlotName: "main",
  },
  Constantinople: {
    territory: Territory.CONSTANTINOPLE,
    territoryName: "CONSTANTINOPLE",
    unitSlotName: "main",
  },
  Smyrna: {
    territory: Territory.SMYRNA,
    territoryName: "SMYRNA",
    unitSlotName: "main",
  },
  Ankara: {
    territory: Territory.ANKARA,
    territoryName: "ANKARA",
    unitSlotName: "main",
  },
  Armenia: {
    territory: Territory.ARMENIA,
    territoryName: "ARMENIA",
    unitSlotName: "main",
  },
  Syria: {
    territory: Territory.SYRIA,
    territoryName: "SYRIA",
    unitSlotName: "main",
  },
  Sevastopol: {
    territory: Territory.SEVASTOPOL,
    territoryName: "SEVASTOPOL",
    unitSlotName: "main",
  },
  Ukraine: {
    territory: Territory.UKRAINE,
    territoryName: "UKRAINE",
    unitSlotName: "main",
  },
  Warsaw: {
    territory: Territory.WARSAW,
    territoryName: "WARSAW",
    unitSlotName: "main",
  },
  Livonia: {
    territory: Territory.LIVONIA,
    territoryName: "LIVONIA",
    unitSlotName: "main",
  },
  Moscow: {
    territory: Territory.MOSCOW,
    territoryName: "MOSCOW",
    unitSlotName: "main",
  },
  "St. Petersburg": {
    territory: Territory.SAINT_PETERSBURG,
    territoryName: "SAINT_PETERSBURG",
    unitSlotName: "main",
  },
  Finland: {
    territory: Territory.FINLAND,
    territoryName: "FINLAND",
    unitSlotName: "main",
  },
  Sweden: {
    territory: Territory.SWEDEN,
    territoryName: "SWEDEN",
    unitSlotName: "main",
  },
  Norway: {
    territory: Territory.NORWAY,
    territoryName: "NORWAY",
    unitSlotName: "main",
  },
  Denmark: {
    territory: Territory.DENMARK,
    territoryName: "DENMARK",
    unitSlotName: "main",
  },
  Kiel: {
    territory: Territory.KIEL,
    territoryName: "KIEL",
    unitSlotName: "main",
  },
  Berlin: {
    territory: Territory.BERLIN,
    territoryName: "BERLIN",
    unitSlotName: "main",
  },
  Prussia: {
    territory: Territory.PRUSSIA,
    territoryName: "PRUSSIA",
    unitSlotName: "main",
  },
  Silesia: {
    territory: Territory.SILESIA,
    territoryName: "SILESIA",
    unitSlotName: "main",
  },
  Munich: {
    territory: Territory.MUNICH,
    territoryName: "MUNICH",
    unitSlotName: "main",
  },
  Ruhr: {
    territory: Territory.RUHR,
    territoryName: "RUHR",
    unitSlotName: "main",
  },
  Holland: {
    territory: Territory.HOLLAND,
    territoryName: "HOLLAND",
    unitSlotName: "main",
  },
  Belgium: {
    territory: Territory.BELGIUM,
    territoryName: "BELGIUM",
    unitSlotName: "main",
  },
  Picardy: {
    territory: Territory.PICARDY,
    territoryName: "PICARDY",
    unitSlotName: "main",
  },
  Brest: {
    territory: Territory.BREST,
    territoryName: "BREST",
    unitSlotName: "main",
  },
  Paris: {
    territory: Territory.PARIS,
    territoryName: "PARIS",
    unitSlotName: "main",
  },
  Burgundy: {
    territory: Territory.BURGUNDY,
    territoryName: "BURGUNDY",
    unitSlotName: "main",
  },
  Marseilles: {
    territory: Territory.MARSEILLES,
    territoryName: "MARSEILLES",
    unitSlotName: "main",
  },
  Gascony: {
    territory: Territory.GASCONY,
    territoryName: "GASCONY",
    unitSlotName: "main",
  },
  "Barents Sea": {
    territory: Territory.BARENTS_SEA,
    territoryName: "BARENTS_SEA",
    unitSlotName: "main",
  },
  "Norwegian Sea": {
    territory: Territory.NORWEGIAN_SEA,
    territoryName: "NORWEGIAN_SEA",
    unitSlotName: "main",
  },
  "North Sea": {
    territory: Territory.NORTH_SEA,
    territoryName: "NORTH_SEA",
    unitSlotName: "main",
  },
  Skagerrack: {
    territory: Territory.SKAGERRACK,
    territoryName: "SKAGERRACK",
    unitSlotName: "main",
  },
  "Heligoland Bight": {
    territory: Territory.HELIGOLAND_BIGHT,
    territoryName: "HELIGOLAND_BIGHT",
    unitSlotName: "main",
  },
  "Baltic Sea": {
    territory: Territory.BALTIC_SEA,
    territoryName: "BALTIC_SEA",
    unitSlotName: "main",
  },
  "Gulf of Bothnia": {
    territory: Territory.GULF_OF_BOTHNIA,
    territoryName: "GULF_OF_BOTHNIA",
    unitSlotName: "main",
  },
  "North Atlantic Ocean": {
    territory: Territory.NORTH_ATLANTIC,
    territoryName: "NORTH_ATLANTIC",
    unitSlotName: "main",
  },
  "Irish Sea": {
    territory: Territory.IRISH_SEA,
    territoryName: "IRISH_SEA",
    unitSlotName: "main",
  },
  "English Channel": {
    territory: Territory.ENGLISH_CHANNEL,
    territoryName: "ENGLISH_CHANNEL",
    unitSlotName: "main",
  },
  "Mid-Atlantic Ocean": {
    territory: Territory.MIDDLE_ATLANTIC,
    territoryName: "MIDDLE_ATLANTIC",
    unitSlotName: "main",
  },
  "Western Mediterranean": {
    territory: Territory.WESTERN_MEDITERRANEAN,
    territoryName: "WESTERN_MEDITERRANEAN",
    unitSlotName: "main",
  },
  "Gulf of Lyons": {
    territory: Territory.GULF_OF_LYONS,
    territoryName: "GULF_OF_LYONS",
    unitSlotName: "main",
  },
  "Tyrrhenian Sea": {
    territory: Territory.TYRRHENIAN_SEA,
    territoryName: "TYRRHENIAN_SEA",
    unitSlotName: "main",
  },
  "Ionian Sea": {
    territory: Territory.IONIAN_SEA,
    territoryName: "IONIAN_SEA",
    unitSlotName: "main",
  },
  "Adriatic Sea": {
    territory: Territory.ADRIATIC_SEA,
    territoryName: "ADRIATIC_SEA",
    unitSlotName: "main",
  },
  "Aegean Sea": {
    territory: Territory.AEGEAN_SEA,
    territoryName: "AEGEAN_SEA",
    unitSlotName: "main",
  },
  "Eastern Mediterranean": {
    territory: Territory.EASTERN_MEDITERRANEAN,
    territoryName: "EASTERN_MEDITERRANEAN",
    unitSlotName: "main",
  },
  "Black Sea": {
    territory: Territory.BLACK_SEA,
    territoryName: "BLACK_SEA",
    unitSlotName: "main",
  },
  Tyrolia: {
    territory: Territory.TYROLIA,
    territoryName: "TYROLIA",
    unitSlotName: "main",
  },
  Bohemia: {
    territory: Territory.BOHEMIA,
    territoryName: "BOHEMIA",
    unitSlotName: "main",
  },
  Vienna: {
    territory: Territory.VIENNA,
    territoryName: "VIENNA",
    unitSlotName: "main",
  },
  Trieste: {
    territory: Territory.TRIESTE,
    territoryName: "TRIESTE",
    unitSlotName: "main",
  },
  Budapest: {
    territory: Territory.BUDAPEST,
    territoryName: "BUDAPEST",
    unitSlotName: "main",
  },
  Galicia: {
    territory: Territory.GALICIA,
    territoryName: "GALICIA",
    unitSlotName: "main",
  },
  "Spain (North Coast)": {
    parent: Territory.SPAIN,
    parentName: "SPAIN",
    territory: Territory.SPAIN_NORTH_COAST,
    territoryName: "SPAIN_NORTH_COAST",
    unitSlotName: "nc",
  },
  "Spain (South Coast)": {
    parent: Territory.SPAIN,
    parentName: "SPAIN",
    territory: Territory.SPAIN_SOUTH_COAST,
    territoryName: "SPAIN_SOUTH_COAST",
    unitSlotName: "sc",
  },
  "St. Petersburg (North Coast)": {
    parent: Territory.SAINT_PETERSBURG,
    parentName: "SAINT_PETERSBURG",
    territory: Territory.SAINT_PETERSBURG_NORTH_COAST,
    territoryName: "SAINT_PETERSBURG_NORTH_COAST",
    unitSlotName: "nc",
  },
  "St. Petersburg (South Coast)": {
    parent: Territory.SAINT_PETERSBURG,
    parentName: "SAINT_PETERSBURG",
    territory: Territory.SAINT_PETERSBURG_SOUTH_COAST,
    territoryName: "SAINT_PETERSBURG_SOUTH_COAST",
    unitSlotName: "sc",
  },
  "Bulgaria (North Coast)": {
    parent: Territory.BULGARIA,
    parentName: "BULGARIA",
    territory: Territory.BULGARIA_NORTH_COAST,
    territoryName: "BULGARIA_NORTH_COAST",
    unitSlotName: "nc",
  },
  "Bulgaria (South Coast)": {
    parent: Territory.BULGARIA,
    parentName: "BULGARIA",
    territory: Territory.BULGARIA_SOUTH_COAST,
    territoryName: "BULGARIA_SOUTH_COAST",
    unitSlotName: "sc",
  },
};

export default TerritoryMap;
