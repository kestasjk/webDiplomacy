import {
  AbsoluteCoordinates,
  BBox,
  Label,
  Territory,
  Texture,
  UnitSlot,
} from "..";

export interface TerritoryMapData extends Territory, BBox {
  centerPos?: AbsoluteCoordinates;
  fill?: string;
  labels?: Label[];
  path: string;
  texture?: Texture;
  unitSlots?: UnitSlot[];
  viewBox?: string;
}
