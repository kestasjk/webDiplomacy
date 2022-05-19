import TerritoryType from "../types/map/TerritoryType";
import TerritoryName from "../types/TerritoryName";

export interface Territory {
  name: TerritoryName;
  abbr: string;
  type: TerritoryType;
  parent?: Territory;
}
