import { Coordinates, Label, TerritoryI, Texture } from "..";
import GetArrayElementType from "../../utils/getArrayElementType";

export interface Dimensions {
  height: number;
  width: number;
}
  
export interface BBox extends Coordinates, Dimensions {}

export const UnitSlotNames = ["main", "nc", "sc"] as const;
export type UnitSlotName = GetArrayElementType<typeof UnitSlotNames>;

export interface UnitSlot extends Coordinates {
  name: UnitSlotName;
  arrowReceiver: Coordinates;
}

// just used for construction the TerritoryMapData. Do not use.
export interface ProvinceMapDrawData extends BBox {
  centerPos?: Coordinates;
  fill?: string;
  labels?: Label[];
  path: string;
  playable: boolean;
  texture?: Texture;
  unitSlots: UnitSlot[]; // always present, but might be zero-length 
  viewBox?: string;
}

export interface ProvinceMapData
  extends ProvinceMapDrawData,
    TerritoryI,
    BBox {
  unitSlotsBySlotName: { [key: string]: UnitSlot };
}
