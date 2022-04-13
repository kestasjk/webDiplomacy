import {
  AbsoluteCoordinates,
  BBox,
  Coordinates,
  Label,
  Territory,
  Texture,
  UnitSlot,
} from "..";

export interface TerritoryMapData extends Territory, BBox {
  arrowReceiver?: Coordinates;
  centerPos?: AbsoluteCoordinates;
  fill?: string;
  labels?: Label[];
  path: string;
  texture?: Texture;
  unitSlots?: UnitSlot[];
  viewBox?: string;
}
