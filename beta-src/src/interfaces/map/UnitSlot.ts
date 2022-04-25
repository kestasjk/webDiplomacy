import UnitSlotName from "../../types/map/UnitSlotName";
import { Coordinates } from "./Coordinates";

export interface UnitSlot extends Coordinates {
  name: UnitSlotName;
}
