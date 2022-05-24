import BuildUnit from "../../enums/BuildUnit";
import { UnitSlotName } from "../../interfaces/map/TerritoryMapData";
import { TerritoryMeta } from "./TerritoriesState";

export interface BuildCommand {
  availableOrder: string;
  canBuild: BuildUnit;
  territoryMeta: TerritoryMeta;
  unitSlotName: UnitSlotName;
}
