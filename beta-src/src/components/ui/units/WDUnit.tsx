import * as React from "react";
import { useTheme } from "@mui/material/styles";
import UIState from "../../../enums/UIState";
import WDUnitController from "../../controllers/WDUnitController";
import { GameIconProps } from "../../../interfaces/Icons";

const WDUnit: React.FC<GameIconProps> = function ({
  height = 50,
  iconState = UIState.NONE,
  id = undefined,
  meta,
  viewBox,
  width = 50,
  type,
}): React.ReactElement {
  const theme = useTheme();

  return (
    <svg
      filter={theme.palette.svg.filters.dropShadows[1]}
      height={height}
      id={id}
      width={width}
      viewBox={viewBox}
    >
      <WDUnitController meta={meta} initialIconState={iconState} type={type} />
    </svg>
  );
};

export default WDUnit;
