import TerritoryData from "../types/map/TerritoryData";
import TerritoryEnum from "../enums/Territory";
import { Territory } from "../interfaces";

export const ALBANIA: Territory = {
  name: "ALBANIA",
  abbr: "ALB",
  type: "land",
};

export const APULIA: Territory = {
  name: "APULIA",
  abbr: "APU",
  type: "land",
};

export const BLACK_SEA: Territory = {
  name: "BLACK_SEA",
  abbr: "BLA",
  type: "water",
};

export const BULGARIA: Territory = {
  name: "BULGARIA",
  abbr: "BUL",
  type: "land",
};

export const CLYDE: Territory = {
  name: "CLYDE",
  abbr: "CLY",
  type: "land",
};

export const EDINBURGH: Territory = {
  name: "EDINBURGH",
  abbr: "EDI",
  type: "land",
};

export const FINLAND: Territory = {
  name: "FINLAND",
  abbr: "FIN",
  type: "land",
};

export const GASCONY: Territory = {
  name: "GASCONY",
  abbr: "GAS",
  type: "land",
};

export const GREECE: Territory = {
  name: "GREECE",
  abbr: "GRE",
  type: "land",
};

export const GULF_OF_BOTHNIA: Territory = {
  name: "GULF_OF_BOTHNIA",
  abbr: "BOT",
  type: "water",
};

export const LIVERPOOL: Territory = {
  name: "LIVERPOOL",
  abbr: "LVP",
  type: "land",
};

export const LIVONIA: Territory = {
  name: "LIVONIA",
  abbr: "LVN",
  type: "land",
};

export const LONDON: Territory = {
  name: "LONDON",
  abbr: "LON",
  type: "land",
};

export const MARSEILLES: Territory = {
  name: "MARSEILLES",
  abbr: "MAR",
  type: "land",
};

export const MOSCOW: Territory = {
  name: "MOSCOW",
  abbr: "MOS",
  type: "land",
};

export const NAPLES: Territory = {
  name: "NAPLES",
  abbr: "NAP",
  type: "land",
};

export const PIEDMONT: Territory = {
  name: "PIEDMONT",
  abbr: "PIE",
  type: "land",
};

export const PORTUGAL: Territory = {
  name: "PORTUGAL",
  abbr: "POR",
  type: "land",
};

export const ROME: Territory = {
  name: "ROME",
  abbr: "ROM",
  type: "land",
};

export const RUMANIA: Territory = {
  name: "RUMANIA",
  abbr: "RUM",
  type: "land",
};

export const WARSAW: Territory = {
  name: "WARSAW",
  abbr: "WAR",
  type: "land",
};

export const SAINT_PETERSBURG: Territory = {
  name: "SAINT_PETERSBURG",
  abbr: "STP",
  type: "land",
};

export const SERBIA: Territory = {
  name: "SERBIA",
  abbr: "SER",
  type: "land",
};

export const SEVASTOPOL: Territory = {
  name: "SEVASTOPOL",
  abbr: "SEV",
  type: "land",
};

export const SPAIN: Territory = {
  name: "SPAIN",
  abbr: "SPA",
  type: "land",
};

export const TRIESTE: Territory = {
  name: "TRIESTE",
  abbr: "TRI",
  type: "land",
};

export const TUSCANY: Territory = {
  name: "TUSCANY",
  abbr: "TUS",
  type: "land",
};

export const UKRAINE: Territory = {
  name: "UKRAINE",
  abbr: "UKR",
  type: "land",
};

export const VENICE: Territory = {
  name: "VENICE",
  abbr: "VEN",
  type: "land",
};

export const WALES: Territory = {
  name: "WALES",
  abbr: "WAL",
  type: "land",
};

export const YORK: Territory = {
  name: "YORK",
  abbr: "YOR",
  type: "land",
};

const Territories: TerritoryData = {
  [TerritoryEnum.ALBANIA]: ALBANIA,
  [TerritoryEnum.APULIA]: APULIA,
  [TerritoryEnum.BLACK_SEA]: BLACK_SEA,
  [TerritoryEnum.BULGARIA]: BULGARIA,
  [TerritoryEnum.CLYDE]: CLYDE,
  [TerritoryEnum.EDINBURGH]: EDINBURGH,
  [TerritoryEnum.FINLAND]: FINLAND,
  [TerritoryEnum.GASCONY]: GASCONY,
  [TerritoryEnum.GREECE]: GREECE,
  [TerritoryEnum.GULF_OF_BOTHNIA]: GULF_OF_BOTHNIA,
  [TerritoryEnum.LIVERPOOL]: LIVERPOOL,
  [TerritoryEnum.LIVONIA]: LIVONIA,
  [TerritoryEnum.LONDON]: LONDON,
  [TerritoryEnum.MARSEILLES]: MARSEILLES,
  [TerritoryEnum.MOSCOW]: MOSCOW,
  [TerritoryEnum.NAPLES]: NAPLES,
  [TerritoryEnum.PIEDMONT]: PIEDMONT,
  [TerritoryEnum.PORTUGAL]: PORTUGAL,
  [TerritoryEnum.ROME]: ROME,
  [TerritoryEnum.RUMANIA]: RUMANIA,
  [TerritoryEnum.WARSAW]: WARSAW,
  [TerritoryEnum.SAINT_PETERSBURG]: SAINT_PETERSBURG,
  [TerritoryEnum.SERBIA]: SERBIA,
  [TerritoryEnum.SEVASTOPOL]: SEVASTOPOL,
  [TerritoryEnum.SPAIN]: SPAIN,
  [TerritoryEnum.TRIESTE]: TRIESTE,
  [TerritoryEnum.TUSCANY]: TUSCANY,
  [TerritoryEnum.UKRAINE]: UKRAINE,
  [TerritoryEnum.VENICE]: VENICE,
  [TerritoryEnum.WALES]: WALES,
  [TerritoryEnum.YORK]: YORK,
} as const;

export default Territories;
