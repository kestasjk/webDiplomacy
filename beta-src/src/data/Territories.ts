import TerritoryData from "../types/map/TerritoryData";
import TerritoryEnum from "../enums/Territory";
import { Territory } from "../interfaces";

export const YORK: Territory = {
  name: "YORK",
  abbr: "YOR",
  type: "land",
};

export const LONDON: Territory = {
  name: "LONDON",
  abbr: "LON",
  type: "land",
};

export const LIVERPOOL: Territory = {
  name: "LIVERPOOL",
  abbr: "LVP",
  type: "land",
};

export const WALES: Territory = {
  name: "WALES",
  abbr: "WAL",
  type: "land",
};

export const EDINBURGH: Territory = {
  name: "EDINBURGH",
  abbr: "EDI",
  type: "land",
};

export const CLYDE: Territory = {
  name: "CLYDE",
  abbr: "CLY",
  type: "land",
};

export const SPAIN: Territory = {
  name: "SPAIN",
  abbr: "SPA",
  type: "land",
};

export const PORTUGAL: Territory = {
  name: "PORTUGAL",
  abbr: "POR",
  type: "land",
};

export const GASCONY: Territory = {
  name: "GASCONY",
  abbr: "GAS",
  type: "land",
};

export const SAINT_PETERSBURG: Territory = {
  name: "SAINT_PETERSBURG",
  abbr: "STP",
  type: "land",
};

export const MOSCOW: Territory = {
  name: "MOSCOW",
  abbr: "MOS",
  type: "land",
};

export const FINLAND: Territory = {
  name: "FINLAND",
  abbr: "FIN",
  type: "land",
};

export const SEVASTOPOL: Territory = {
  name: "SEVASTOPOL",
  abbr: "SEV",
  type: "land",
};

export const LIVONIA: Territory = {
  name: "LIVONIA",
  abbr: "LVN",
  type: "land",
};

export const WARSAW: Territory = {
  name: "WARSAW",
  abbr: "WAR",
  type: "land",
};

export const UKRAINE: Territory = {
  name: "UKRAINE",
  abbr: "UKR",
  type: "land",
};

export const RUMANIA: Territory = {
  name: "RUMANIA",
  abbr: "RUM",
  type: "land",
};

export const ALBANIA: Territory = {
  name: "ALBANIA",
  abbr: "ALB",
  type: "land",
};

export const GREECE: Territory = {
  name: "GREECE",
  abbr: "GRE",
  type: "land",
};

export const SERBIA: Territory = {
  name: "SERBIA",
  abbr: "SER",
  type: "land",
};

export const TRIESTE: Territory = {
  name: "TRIESTE",
  abbr: "TRI",
  type: "land",
};

export const NAPLES: Territory = {
  name: "NAPLES",
  abbr: "NAP",
  type: "land",
};

export const VENICE: Territory = {
  name: "VENICE",
  abbr: "VEN",
  type: "land",
};

export const TUSCANY: Territory = {
  name: "TUSCANY",
  abbr: "TUS",
  type: "land",
};

export const MARSEILLES: Territory = {
  name: "MARSEILLES",
  abbr: "MAR",
  type: "land",
};

export const APULIA: Territory = {
  name: "APULIA",
  abbr: "APU",
  type: "land",
};

export const PIEDMONT: Territory = {
  name: "PIEDMONT",
  abbr: "PIE",
  type: "land",
};

export const ROME: Territory = {
  name: "ROME",
  abbr: "ROM",
  type: "land",
};

export const BULGARIA: Territory = {
  name: "BULGARIA",
  abbr: "BUL",
  type: "land",
};

export const BLACK_SEA: Territory = {
  name: "BLACK_SEA",
  abbr: "BLA",
  type: "water",
};

export const ENGLISH_CHANNEL: Territory = {
  name: "ENGLISH_CHANNEL",
  abbr: "ENG",
  type: "water",
};

export const GULF_OF_BOTHNIA: Territory = {
  name: "GULF_OF_BOTHNIA",
  abbr: "BOT",
  type: "water",
};

export const IRISH_SEA: Territory = {
  name: "IRISH_SEA",
  abbr: "IRI",
  type: "water",
};

export const NORTH_ATLANTIC: Territory = {
  name: "NORTH_ATLANTIC",
  abbr: "NAO",
  type: "water",
};

export const NORTH_SEA: Territory = {
  name: "NORTH_SEA",
  abbr: "NTH",
  type: "water",
};

export const NEUTRAL_2: Territory = {
  name: "NEUTRAL_2",
  abbr: "",
  type: "land",
};

const Territories: TerritoryData = {
  [TerritoryEnum.YORK]: YORK,
  [TerritoryEnum.LONDON]: LONDON,
  [TerritoryEnum.LIVERPOOL]: LIVERPOOL,
  [TerritoryEnum.WALES]: WALES,
  [TerritoryEnum.EDINBURGH]: EDINBURGH,
  [TerritoryEnum.CLYDE]: CLYDE,
  [TerritoryEnum.SPAIN]: SPAIN,
  [TerritoryEnum.PORTUGAL]: PORTUGAL,
  [TerritoryEnum.GASCONY]: GASCONY,
  [TerritoryEnum.SAINT_PETERSBURG]: SAINT_PETERSBURG,
  [TerritoryEnum.MOSCOW]: MOSCOW,
  [TerritoryEnum.LIVONIA]: LIVONIA,
  [TerritoryEnum.SEVASTOPOL]: SEVASTOPOL,
  [TerritoryEnum.WARSAW]: WARSAW,
  [TerritoryEnum.FINLAND]: FINLAND,
  [TerritoryEnum.UKRAINE]: UKRAINE,
  [TerritoryEnum.RUMANIA]: RUMANIA,
  [TerritoryEnum.ALBANIA]: ALBANIA,
  [TerritoryEnum.GREECE]: GREECE,
  [TerritoryEnum.SERBIA]: SERBIA,
  [TerritoryEnum.BULGARIA]: BULGARIA,
  [TerritoryEnum.TRIESTE]: TRIESTE,
  [TerritoryEnum.NAPLES]: NAPLES,
  [TerritoryEnum.VENICE]: VENICE,
  [TerritoryEnum.TUSCANY]: TUSCANY,
  [TerritoryEnum.MARSEILLES]: MARSEILLES,
  [TerritoryEnum.APULIA]: APULIA,
  [TerritoryEnum.PIEDMONT]: PIEDMONT,
  [TerritoryEnum.ROME]: ROME,
  [TerritoryEnum.BLACK_SEA]: BLACK_SEA,
  [TerritoryEnum.ENGLISH_CHANNEL]: ENGLISH_CHANNEL,
  [TerritoryEnum.GULF_OF_BOTHNIA]: GULF_OF_BOTHNIA,
  [TerritoryEnum.IRISH_SEA]: IRISH_SEA,
  [TerritoryEnum.NORTH_ATLANTIC]: NORTH_ATLANTIC,
  [TerritoryEnum.NORTH_SEA]: NORTH_SEA,
  [TerritoryEnum.NEUTRAL_2]: NEUTRAL_2,
} as const;

export default Territories;
