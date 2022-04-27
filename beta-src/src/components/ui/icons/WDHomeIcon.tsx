import * as React from "react";
import { useTheme } from "@mui/material";
import UIState from "../../../enums/UIState";
import { NavIconProps } from "../../../interfaces/Icons";

const WDHomeIcon: React.FC<NavIconProps> = function ({
  iconState = UIState.INACTIVE,
}): React.ReactElement {
  const theme = useTheme();
  return (
    <svg
      filter={theme.palette.svg.filters.dropShadows[0]}
      height={40}
      width={40}
      xmlns="http://www.w3.org/2000/svg"
    >
      {iconState === UIState.ACTIVE && (
        <circle cx="20" cy="20" r="20" fill="#000" />
      )}
      <path
        d="M30.692 16.247 20.579 7.562a.874.874 0 0 0-1.137 0L9.307 16.247a.855.855 0 0 0-.251.962c.063.167.176.31.325.412a.9.9 0 0 0 .506.156h2.047V28.94a.42.42 0 0 0 .127.289.436.436 0 0 0 .294.124h5.074a.436.436 0 0 0 .294-.124.42.42 0 0 0 .126-.29v-7.59a.41.41 0 0 1 .124-.292.425.425 0 0 1 .297-.121h3.413c.111 0 .218.043.297.12a.41.41 0 0 1 .124.293v7.59a.42.42 0 0 0 .126.29.436.436 0 0 0 .294.124h5.074a.436.436 0 0 0 .294-.124.42.42 0 0 0 .127-.29V17.778h2.047a.9.9 0 0 0 .524-.136.875.875 0 0 0 .346-.41.856.856 0 0 0-.244-.984Z"
        fill="#fff"
      />
    </svg>
  );
};

export default WDHomeIcon;
