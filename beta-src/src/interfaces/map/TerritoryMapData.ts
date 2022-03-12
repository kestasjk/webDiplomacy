import { AbsoluteCoordinates, Territory, BBox, Label, Texture } from "..";

export interface TerritoryMapData extends Territory, BBox {
  centerPos?: AbsoluteCoordinates;
  labels?: Label[];
  unitSlot?: AbsoluteCoordinates;
  path: string;
  fill?: string;
  texture?: Texture;
  viewBox?: string;
}
