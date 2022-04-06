import UnitSlotName from "../../types/map/UnitSlotName";
import { AbsoluteCoordinates } from "./AbsoluteCoordinates";

export interface UnitSlot extends AbsoluteCoordinates {
  name: UnitSlotName;
}
