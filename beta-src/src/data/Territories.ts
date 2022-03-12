import TerritoryData from "../types/map/TerritoryData";
import TerritoryEnum from "../enums/Territory";
import { Territory } from "../interfaces";

export const YORK: Territory = {
  name: TerritoryEnum.YORK,
  abbr: "YOR",
  type: "land",
};

export const LONDON: Territory = {
  name: TerritoryEnum.LONDON,
  abbr: "LON",
  type: "land",
};

export const LIVERPOOL: Territory = {
  name: TerritoryEnum.LIVERPOOL,
  abbr: "LVP",
  type: "land",
};

export const WALES: Territory = {
  name: TerritoryEnum.WALES,
  abbr: "WAL",
  type: "land",
};

export const EDINBURGH: Territory = {
  name: TerritoryEnum.EDINBURGH,
  abbr: "EDI",
  type: "land",
};

export const CLYDE: Territory = {
  name: TerritoryEnum.CLYDE,
  abbr: "CLY",
  type: "land",
};

export const SPAIN: Territory = {
  name: TerritoryEnum.SPAIN,
  abbr: "SPA",
  type: "land",
};

export const PORTUGAL: Territory = {
  name: TerritoryEnum.PORTUGAL,
  abbr: "POR",
  type: "land",
};

export const GASCONY: Territory = {
  name: TerritoryEnum.GASCONY,
  abbr: "GAS",
  type: "land",
};

export const SAINT_PETERSBURG: Territory = {
  name: TerritoryEnum.SAINT_PETERSBURG,
  abbr: "STP",
  type: "land",
};

export const MOSCOW: Territory = {
  name: TerritoryEnum.MOSCOW,
  abbr: "MOS",
  type: "land",
};

export const FINLAND: Territory = {
  name: TerritoryEnum.FINLAND,
  abbr: "FIN",
  type: "land",
};

export const SEVASTOPOL: Territory = {
  name: TerritoryEnum.SEVASTOPOL,
  abbr: "SEV",
  type: "land",
};

export const LIVONIA: Territory = {
  name: TerritoryEnum.LIVONIA,
  abbr: "LVN",
  type: "land",
};

export const WARSAW: Territory = {
  name: TerritoryEnum.WARSAW,
  abbr: "WAR",
  type: "land",
};

export const UKRAINE: Territory = {
  name: TerritoryEnum.UKRAINE,
  abbr: "UKR",
  type: "land",
};

export const RUMANIA: Territory = {
  name: TerritoryEnum.RUMANIA,
  abbr: "RUM",
  type: "land",
};

export const ALBANIA: Territory = {
  name: TerritoryEnum.ALBANIA,
  abbr: "ALB",
  type: "land",
};

export const GREECE: Territory = {
  name: TerritoryEnum.GREECE,
  abbr: "GRE",
  type: "land",
};

export const SERBIA: Territory = {
  name: TerritoryEnum.SERBIA,
  abbr: "SER",
  type: "land",
};

export const TRIESTE: Territory = {
  name: TerritoryEnum.TRIESTE,
  abbr: "TRI",
  type: "land",
};

export const NAPLES: Territory = {
  name: TerritoryEnum.NAPLES,
  abbr: "NAP",
  type: "land",
};

export const VENICE: Territory = {
  name: TerritoryEnum.VENICE,
  abbr: "VEN",
  type: "land",
};

export const TUSCANY: Territory = {
  name: TerritoryEnum.TUSCANY,
  abbr: "TUS",
  type: "land",
};

export const MARSEILLES: Territory = {
  name: TerritoryEnum.MARSEILLES,
  abbr: "MAR",
  type: "land",
};

export const APULIA: Territory = {
  name: TerritoryEnum.APULIA,
  abbr: "APU",
  type: "land",
};

export const PIEDMONT: Territory = {
  name: TerritoryEnum.PIEDMONT,
  abbr: "PIE",
  type: "land",
};

export const ROME: Territory = {
  name: TerritoryEnum.ROME,
  abbr: "ROM",
  type: "land",
};

export const BULGARIA: Territory = {
  name: TerritoryEnum.BULGARIA,
  abbr: "BUL",
  type: "land",
};

export const BLACK_SEA: Territory = {
  name: TerritoryEnum.BLACK_SEA,
  abbr: "BLA",
  type: "water",
};

export const GULF_OF_BOTHNIA: Territory = {
  name: TerritoryEnum.GULF_OF_BOTHNIA,
  abbr: "BOT",
  type: "water",
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
  [TerritoryEnum.GULF_OF_BOTHNIA]: GULF_OF_BOTHNIA,
} as const;

export default Territories;
