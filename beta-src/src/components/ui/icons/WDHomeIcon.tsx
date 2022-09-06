import * as React from "react";
import UIState from "../../../enums/UIState";
import { NavIconProps } from "../../../interfaces/Icons";
import LogoImage from "../../../assets/png/logo-d.png";

const WDHomeIcon: React.FC<NavIconProps> = function ({
  iconState = UIState.INACTIVE,
}): React.ReactElement {
  return (
    <div className="has-tooltip">
      <span className="tooltip rounded shadow-lg px-3 py-1 text-white mt-12 right-8 text-xs flex">
        Go to home page
      </span>
      <img src={LogoImage} alt="Web Diplomacy logo" width="46" height="46" />
    </div>
  );
};

export default WDHomeIcon;
