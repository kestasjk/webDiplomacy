import * as React from "react";
import { SvgIcon } from "@mui/material";
// import { ActionIcon, ActionIconSelected } from "./svgr-components";

interface navIconProps {
  isActive?: "active" | "inactive";
  component: React.FC;
}

const WDSvgIcon: React.FC<navIconProps> = function ({ component, isActive }) {
  return (
    <SvgIcon
      className={isActive === "active" ? "navIconSelected" : "navIcon"}
      component={component}
      inheritViewBox
    />
  );
};

WDSvgIcon.defaultProps = {
  isActive: "inactive",
};

export default WDSvgIcon;
