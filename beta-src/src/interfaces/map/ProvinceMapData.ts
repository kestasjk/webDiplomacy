import { Coordinates, Label, TextureData } from "..";
import GetArrayElementType from "../../utils/getArrayElementType";
import Territory from "../../enums/map/variants/classic/Territory";
import Province from "../../enums/map/variants/classic/Province";
import TerritoryType from "../../types/map/TerritoryType";
import TerritoryLabel from "../../types/UnitLabel";

export interface Dimensions {
  height: number;
  width: number;
}

export interface BBox extends Coordinates, Dimensions {}

export const UnitSlotNames = ["main", "nc", "sc"] as const;
export type UnitSlotName = GetArrayElementType<typeof UnitSlotNames>;

export interface UnitSlot extends Coordinates {
  name: UnitSlotName;
  territory: Territory;
  arrowReceiver: Coordinates;
}

export interface ProvinceMapData extends BBox {
  province: Province;
  abbr: string;
  centerPos?: Coordinates;
  fill?: string;
  labels?: Label[];
  path: string;
  playable: boolean;
  texture?: TextureData;
  type: TerritoryType;
  rootTerritory: Territory | null; // null for unplayable provinces
  unitSlots: UnitSlot[]; // always present, but might be zero-length
  viewBox?: string;
  unitSlotsBySlotName: { [key: string]: UnitSlot };
}
