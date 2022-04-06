import Country from "../enums/Country";
import UIState from "../enums/UIState";
import { UnitMeta } from "./map/UnitMeta";

export interface navIconProps {
  iconState?: UIState.ACTIVE | UIState.INACTIVE;
}

export interface gameIconProps {
  country: Country;
  height?: number;
  iconState?: UIState;
  viewBox?: string;
  width?: number;
  meta: UnitMeta;
}
