import BuildUnit from "../../enums/BuildUnit";
import UnitSlotName from "../../types/map/UnitSlotName";
import { TerritoryMeta } from "./TerritoriesState";

export interface BuildCommand {
  availableOrder: string;
  canBuild: BuildUnit;
  territoryMeta: TerritoryMeta;
  unitSlotName: UnitSlotName;
}
