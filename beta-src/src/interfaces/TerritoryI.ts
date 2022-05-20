import TerritoryType from "../types/map/TerritoryType";
import TerritoryName from "../types/TerritoryName";

export interface TerritoryI {
  name: TerritoryName;
  abbr: string;
  type: TerritoryType;
  parent?: TerritoryI;
}
