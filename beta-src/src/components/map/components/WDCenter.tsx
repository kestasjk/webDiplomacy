import { useTheme } from "@mui/material";
import * as React from "react";
import Province from "../../../enums/map/variants/classic/Province";
import { Coordinates } from "../../../interfaces";
import WDTrigger from "./WDTrigger";

interface WDCenterProps extends Coordinates {
  province: Province;
}

const WDCenter: React.FC<WDCenterProps> = function ({
  province,
  x,
  y,
}): React.ReactElement {
  const theme = useTheme();
  return (
    <svg
      id={`${province}-center`}
      width="34"
      height="34"
      viewBox="0 0 34 34"
      fill="none"
      x={x}
      y={y}
      xmlns="http://www.w3.org/2000/svg"
    >
      <path
        d="M17 32.9998C25.8366 32.9998 33 25.8364 33 16.9999C33 8.1634 25.8366 1 17 1C8.16344 1 1 8.1634 1 16.9999C1 25.8364 8.16344 32.9998 17 32.9998Z"
        stroke={theme.palette.primary.main}
      />
      <path
        d="M17.0064 25.7269C21.8263 25.7269 25.7336 21.8196 25.7336 16.9997C25.7336 12.1797 21.8263 8.27243 17.0064 8.27243C12.1866 8.27243 8.2793 12.1797 8.2793 16.9997C8.2793 21.8196 12.1866 25.7269 17.0064 25.7269Z"
        fill={theme.palette.primary.main}
      />
      <WDTrigger />
    </svg>
  );
};

export default WDCenter;
