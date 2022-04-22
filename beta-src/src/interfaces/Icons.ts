import Country from "../enums/Country";
import UIState from "../enums/UIState";
import { UnitMeta } from "./map/UnitMeta";

export interface NavIconProps {
  iconState?: UIState.ACTIVE | UIState.INACTIVE;
}

export interface IconProps {
  country: Country;
  iconState?: UIState;
}

export interface GameIconProps extends IconProps {
  height?: number;
  id?: string;
  viewBox?: string;
  width?: number;
  meta: UnitMeta;
}
