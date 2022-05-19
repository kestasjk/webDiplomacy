import BuildUnit from "../../enums/BuildUnit";
import Territory from "../../enums/map/variants/classic/Territory";
import UnitSlotName from "../../types/map/UnitSlotName";
import { TerritoryMeta } from "./TerritoriesState";

export interface DrawArrowCommand {
  from: Territory;
  to: Territory;
  type: "move";
}

export interface BuildCommand {
  availableOrder: string;
  canBuild: BuildUnit;
  territoryMeta: TerritoryMeta;
  unitSlotName: UnitSlotName;
}
