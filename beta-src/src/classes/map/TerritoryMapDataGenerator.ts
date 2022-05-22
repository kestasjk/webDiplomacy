import {
  BBox,
  Coordinates,
  Label,
  TerritoryI,
  TerritoryMapData,
  Texture,
  UnitSlot,
} from "../../interfaces";
import webDiplomacyTheme from "../../webDiplomacyTheme";
import Country from "../../enums/Country";
import Territory from "../../enums/map/variants/classic/Territory";

// FIXME: burn me down please
export interface TerritoryMapDataGeneratorDrawData extends BBox {
  arrowReceiver?: Coordinates;
  centerPos?: Coordinates;
  country?: Country;
  fill?: string;
  labels?: Label[];
  path: string;
  playable: boolean;
  texture?: Texture;
  unitSlots?: UnitSlot[];
  viewBox?: string;
}
