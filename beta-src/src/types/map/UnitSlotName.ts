import GetArrayElementType from "../../utils/getArrayElementType";

export const UnitSlotNames = ["main", "nc", "sc"] as const;

type UnitSlotName = GetArrayElementType<typeof UnitSlotNames>;

export default UnitSlotName;
