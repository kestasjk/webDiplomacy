import TerritoryEnum from "../enums/Territory";
import TerritoryType from "../types/map/TerritoryType";

export interface Territory {
  name: TerritoryEnum;
  abbr: string;
  type: TerritoryType;
}
