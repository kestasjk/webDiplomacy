import Territory from "../enums/map/variants/classic/Territory";
import TerritoryType from "../types/map/TerritoryType";

export interface TerritoryI {
  territory: Territory;
  abbr: string;
  type: TerritoryType;
  parent?: TerritoryI;
}
