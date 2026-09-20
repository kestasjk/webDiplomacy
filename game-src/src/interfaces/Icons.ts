import Country from "../enums/Country";
import UIState from "../enums/UIState";

export interface NavIconProps {
  iconState?: UIState.ACTIVE | UIState.INACTIVE;
}

export interface IconProps {
  country: Country;
  iconState?: UIState;
}
