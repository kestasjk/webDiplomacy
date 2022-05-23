import BuildUnit from "../../enums/BuildUnit";
import Territory from "../../enums/map/variants/classic/Territory";
import UnitSlotName from "../../types/map/UnitSlotName";
import { TerritoryMeta } from "./TerritoriesState";

export interface BuildCommand {
  availableOrder: string;
  canBuild: BuildUnit;
  territoryMeta: TerritoryMeta;
  unitSlotName: UnitSlotName;
}

export interface FlyoutCommand {
  orderID: string;
  territory?: Territory;
  unitSlotName: string;
}
