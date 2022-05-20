import { BBox, Coordinates, Label, Territory, Texture, UnitSlot } from "..";

export interface TerritoryMapData extends Territory, BBox {
  arrowReceiver?: Coordinates;
  centerPos?: Coordinates;
  fill?: string;
  labels?: Label[];
  path: string;
  texture?: Texture;
  unitSlots?: UnitSlot[];
  unitSlotsBySlotName: { [key: string]: UnitSlot };
  viewBox?: string;
}
