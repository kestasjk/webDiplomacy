import { BBox, Coordinates, Label, TerritoryI, Texture, UnitSlot } from "..";

// just used for construction the TerritoryMapData. Do not use.
export interface TerritoryMapDrawData extends BBox {
  arrowReceiver?: Coordinates;
  centerPos?: Coordinates;
  fill?: string;
  labels?: Label[];
  path: string;
  playable: boolean;
  texture?: Texture;
  unitSlots?: UnitSlot[];
  viewBox?: string;
}

export interface TerritoryMapData
  extends TerritoryMapDrawData,
    TerritoryI,
    BBox {
  unitSlotsBySlotName: { [key: string]: UnitSlot };
}
