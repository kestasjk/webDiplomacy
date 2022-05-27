import { Coordinates, Label, Texture } from "..";
import GetArrayElementType from "../../utils/getArrayElementType";
import Territory from "../../enums/map/variants/classic/Territory";
import Province from "../../enums/map/variants/classic/Province";
import TerritoryType from "../../types/map/TerritoryType";

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

// just used for construction the ProvinceMapData. Do not use.
export interface ProvinceMapDrawData extends BBox {
  abbr: string;
  centerPos?: Coordinates;
  fill?: string;
  labels?: Label[];
  path: string;
  playable: boolean;
  texture?: Texture;
  type: TerritoryType;
  unitSlots: UnitSlot[]; // always present, but might be zero-length 
  viewBox?: string;
}

export interface ProvinceMapData extends ProvinceMapDrawData, BBox {
  territory: Territory;
  unitSlotsBySlotName: { [key: string]: UnitSlot };
}
