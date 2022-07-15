import * as React from "react";
import UIState from "../../../enums/UIState";
import { NavIconProps } from "../../../interfaces/Icons";
import LogoImage from "../../../assets/png/web-diplomacy-logo.png";

const WDHomeIcon: React.FC<NavIconProps> = function ({
  iconState = UIState.INACTIVE,
}): React.ReactElement {
  return (
    <img src={LogoImage} alt="Web Diplomacy logo" width="46" height="46" />
  );
};

export default WDHomeIcon;
