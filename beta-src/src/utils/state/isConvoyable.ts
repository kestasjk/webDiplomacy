import { Unit } from "../map/getUnits";
import BoardClass from "../../models/BoardClass";
import { UnitType } from "../../models/enums";

/* eslint-disable no-param-reassign */
export default function isConvoyable(board: BoardClass, unit?: Unit): boolean {
  // to be convoyable, the unit needs to be an army adjacent to a fleet in water
  if (!unit) return false;
  if (unit.unit.type !== "Army") return false;
  const unitTerr = board.findUnitByID(unit.unit.id);
  return unitTerr?.Territory.convoyLink || false;
  // const borderUnits = board.getBorderUnits(unitTerr);
  // console.log({ borderUnits });
  // const borderWaterFleets = borderUnits.filter(
  //   (u) => u.type === UnitType.Fleet && u.Territory.type === "Sea",
  // );
  // return !!borderWaterFleets.length;
}
