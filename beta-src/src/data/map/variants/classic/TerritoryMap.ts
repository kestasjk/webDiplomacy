import Territory from "../../../../enums/map/variants/classic/Territory";
import UnitSlotName from "../../../../types/map/UnitSlotName";

export interface ITerritory {
  territory: Territory;
  unitSlotName: UnitSlotName;
}

type ITerritoryMap = {
  [key: string]: ITerritory;
};

/**
 * This file maps territory names to local app enums.
 */
const TerritoryMap: ITerritoryMap = {
  Clyde: {
    territory: Territory.CLYDE,
    unitSlotName: "main",
  },
  Edinburgh: {
    territory: Territory.EDINBURGH,
    unitSlotName: "main",
  },
  Liverpool: {
    territory: Territory.LIVERPOOL,
    unitSlotName: "main",
  },
  Yorkshire: {
    territory: Territory.YORK,
    unitSlotName: "main",
  },
  Wales: {
    territory: Territory.WALES,
    unitSlotName: "main",
  },
  London: {
    territory: Territory.LONDON,
    unitSlotName: "main",
  },
  Portugal: {
    territory: Territory.PORTUGAL,
    unitSlotName: "main",
  },
  Spain: {
    territory: Territory.SPAIN,
    unitSlotName: "main",
  },
  "North Africa": {
    territory: Territory.NORTH_AFRICA,
    unitSlotName: "main",
  },
  Tunis: {
    territory: Territory.TUNIS,
    unitSlotName: "main",
  },
  Naples: {
    territory: Territory.NAPLES,
    unitSlotName: "main",
  },
  Rome: {
    territory: Territory.ROME,
    unitSlotName: "main",
  },
  Tuscany: {
    territory: Territory.TUSCANY,
    unitSlotName: "main",
  },
  Piedmont: {
    territory: Territory.PIEDMONT,
    unitSlotName: "main",
  },
  Venice: {
    territory: Territory.VENICE,
    unitSlotName: "main",
  },
  Apulia: {
    territory: Territory.APULIA,
    unitSlotName: "main",
  },
  Greece: {
    territory: Territory.GREECE,
    unitSlotName: "main",
  },
  Albania: {
    territory: Territory.ALBANIA,
    unitSlotName: "main",
  },
  Serbia: {
    territory: Territory.SERBIA,
    unitSlotName: "main",
  },
  Bulgaria: {
    territory: Territory.BULGARIA,
    unitSlotName: "main",
  },
  Rumania: {
    territory: Territory.RUMANIA,
    unitSlotName: "main",
  },
  Constantinople: {
    territory: Territory.CONSTANTINOPLE,
    unitSlotName: "main",
  },
  Smyrna: {
    territory: Territory.SMYRNA,
    unitSlotName: "main",
  },
  Ankara: {
    territory: Territory.ANKARA,
    unitSlotName: "main",
  },
  Armenia: {
    territory: Territory.ARMENIA,
    unitSlotName: "main",
  },
  Syria: {
    territory: Territory.SYRIA,
    unitSlotName: "main",
  },
  Sevastopol: {
    territory: Territory.SEVASTOPOL,
    unitSlotName: "main",
  },
  Ukraine: {
    territory: Territory.UKRAINE,
    unitSlotName: "main",
  },
  Warsaw: {
    territory: Territory.WARSAW,
    unitSlotName: "main",
  },
  Livonia: {
    territory: Territory.LIVONIA,
    unitSlotName: "main",
  },
  Moscow: {
    territory: Territory.MOSCOW,
    unitSlotName: "main",
  },
  "St. Petersburg": {
    territory: Territory.SAINT_PETERSBURG,
    unitSlotName: "main",
  },
  Finland: {
    territory: Territory.FINLAND,
    unitSlotName: "main",
  },
  Sweden: {
    territory: Territory.SWEDEN,
    unitSlotName: "main",
  },
  Norway: {
    territory: Territory.NORWAY,
    unitSlotName: "main",
  },
  Denmark: {
    territory: Territory.DENMARK,
    unitSlotName: "main",
  },
  Kiel: {
    territory: Territory.KIEL,
    unitSlotName: "main",
  },
  Berlin: {
    territory: Territory.BERLIN,
    unitSlotName: "main",
  },
  Prussia: {
    territory: Territory.PRUSSIA,
    unitSlotName: "main",
  },
  Silesia: {
    territory: Territory.SILESIA,
    unitSlotName: "main",
  },
  Munich: {
    territory: Territory.MUNICH,
    unitSlotName: "main",
  },
  Ruhr: {
    territory: Territory.RUHR,
    unitSlotName: "main",
  },
  Holland: {
    territory: Territory.HOLLAND,
    unitSlotName: "main",
  },
  Belgium: {
    territory: Territory.BELGIUM,
    unitSlotName: "main",
  },
  Picardy: {
    territory: Territory.PICARDY,
    unitSlotName: "main",
  },
  Brest: {
    territory: Territory.BREST,
    unitSlotName: "main",
  },
  Paris: {
    territory: Territory.PARIS,
    unitSlotName: "main",
  },
  Burgundy: {
    territory: Territory.BURGUNDY,
    unitSlotName: "main",
  },
  Marseilles: {
    territory: Territory.MARSEILLES,
    unitSlotName: "main",
  },
  Gascony: {
    territory: Territory.GASCONY,
    unitSlotName: "main",
  },
  "Barents Sea": {
    territory: Territory.BARENTS_SEA,
    unitSlotName: "main",
  },
  "Norwegian Sea": {
    territory: Territory.NORWEGIAN_SEA,
    unitSlotName: "main",
  },
  "North Sea": {
    territory: Territory.NORTH_SEA,
    unitSlotName: "main",
  },
  Skagerrack: {
    territory: Territory.SKAGERRACK,
    unitSlotName: "main",
  },
  "Heligoland Bight": {
    territory: Territory.HELIGOLAND_BIGHT,
    unitSlotName: "main",
  },
  "Baltic Sea": {
    territory: Territory.BALTIC_SEA,
    unitSlotName: "main",
  },
  "Gulf of Bothnia": {
    territory: Territory.GULF_OF_BOTHNIA,
    unitSlotName: "main",
  },
  "North Atlantic Ocean": {
    territory: Territory.NORTH_ATLANTIC,
    unitSlotName: "main",
  },
  "Irish Sea": {
    territory: Territory.IRISH_SEA,
    unitSlotName: "main",
  },
  "English Channel": {
    territory: Territory.ENGLISH_CHANNEL,
    unitSlotName: "main",
  },
  "Mid-Atlantic Ocean": {
    territory: Territory.MIDDLE_ATLANTIC,
    unitSlotName: "main",
  },
  "Western Mediterranean": {
    territory: Territory.WESTERN_MEDITERRANEAN,
    unitSlotName: "main",
  },
  "Gulf of Lyons": {
    territory: Territory.GULF_OF_LYONS,
    unitSlotName: "main",
  },
  "Tyrrhenian Sea": {
    territory: Territory.TYRRHENIAN_SEA,
    unitSlotName: "main",
  },
  "Ionian Sea": {
    territory: Territory.IONIAN_SEA,
    unitSlotName: "main",
  },
  "Adriatic Sea": {
    territory: Territory.ADRIATIC_SEA,
    unitSlotName: "main",
  },
  "Aegean Sea": {
    territory: Territory.AEGEAN_SEA,
    unitSlotName: "main",
  },
  "Eastern Mediterranean": {
    territory: Territory.EASTERN_MEDITERRANEAN,
    unitSlotName: "main",
  },
  "Black Sea": {
    territory: Territory.BLACK_SEA,
    unitSlotName: "main",
  },
  Tyrolia: {
    territory: Territory.TYROLIA,
    unitSlotName: "main",
  },
  Bohemia: {
    territory: Territory.BOHEMIA,
    unitSlotName: "main",
  },
  Vienna: {
    territory: Territory.VIENNA,
    unitSlotName: "main",
  },
  Trieste: {
    territory: Territory.TRIESTE,
    unitSlotName: "main",
  },
  Budapest: {
    territory: Territory.BUDAPEST,
    unitSlotName: "main",
  },
  Galicia: {
    territory: Territory.GALICIA,
    unitSlotName: "main",
  },
  "Spain (North Coast)": {
    territory: Territory.SPAIN,
    unitSlotName: "nc",
  },
  "Spain (South Coast)": {
    territory: Territory.SPAIN,
    unitSlotName: "sc",
  },
  "St. Petersburg (North Coast)": {
    territory: Territory.SAINT_PETERSBURG_NORTH_COAST,
    unitSlotName: "nc",
  },
  "St. Petersburg (South Coast)": {
    territory: Territory.SAINT_PETERSBURG_SOUTH_COAST,
    unitSlotName: "sc",
  },
  "Bulgaria (North Coast)": {
    territory: Territory.BULGARIA,
    unitSlotName: "nc",
  },
  "Bulgaria (South Coast)": {
    territory: Territory.BULGARIA,
    unitSlotName: "sc",
  },
};

export default TerritoryMap;
