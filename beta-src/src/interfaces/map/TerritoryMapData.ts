import { BBox, Coordinates, Label, TerritoryI, Texture, UnitSlot } from "..";

export interface TerritoryMapData extends TerritoryI, BBox {
  arrowReceiver?: Coordinates;
  centerPos?: Coordinates;
  fill?: string;
  labels?: Label[];
  path: string;
  playable: boolean;
  texture?: Texture;
  unitSlots?: UnitSlot[];
  unitSlotsBySlotName: { [key: string]: UnitSlot };
  viewBox?: string;
}
