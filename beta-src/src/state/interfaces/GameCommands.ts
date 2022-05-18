import BuildUnit from "../../enums/BuildUnit";
import Territory from "../../enums/map/variants/classic/Territory";
import UnitSlotName from "../../types/map/UnitSlotName";

export interface DrawArrowCommand {
  from: Territory;
  to: Territory;
  type: "move";
}

export interface BuildCommand {
  availableOrder: string;
  canBuild: BuildUnit;
  toTerrID: string;
  unitSlotName: UnitSlotName;
}
