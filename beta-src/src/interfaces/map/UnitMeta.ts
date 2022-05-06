import { MTerritory } from "../../data/map/variants/classic/TerritoryMap";
import Country from "../../enums/Country";
import { IUnit } from "../../models/Interfaces";

export interface UnitMeta {
  mappedTerritory: MTerritory;
  unit: IUnit;
  country: Country;
}
