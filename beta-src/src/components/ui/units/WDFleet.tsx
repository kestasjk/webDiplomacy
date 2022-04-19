import * as React from "react";
import { useTheme } from "@mui/material/styles";
import { GameIconProps } from "../../../interfaces/Icons";
import WDFleetIcon from "./WDFleetIcon";
import WDUnitController from "../WDUnitController";
import UIState from "../../../enums/UIState";

const WDFleet: React.FC<GameIconProps> = function ({
  country,
  height = 50,
  iconState = UIState.NONE,
  id = undefined,
  meta,
  viewBox,
  width = 50,
}): React.ReactElement {
  const theme = useTheme();
  const [fluidIconState, setFluidIconState] = React.useState(iconState);

  return (
    <svg
      filter={theme.palette.svg.filters.dropShadows[1]}
      height={height}
      id={id}
      viewBox={viewBox}
      width={width}
    >
      <WDUnitController meta={meta} setIconState={setFluidIconState}>
        <WDFleetIcon country={country} iconState={fluidIconState} />
      </WDUnitController>
    </svg>
  );
};

export default WDFleet;
