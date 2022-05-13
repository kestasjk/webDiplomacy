import * as React from "react";
import { useTheme } from "@mui/material/styles";
import UIState from "../../../enums/UIState";
import WDUnitController from "../../controllers/WDUnitController";
import { GameIconProps } from "../../../interfaces/Icons";
import WDArmyIcon from "./WDArmyIcon";

const WDArmy: React.FC<GameIconProps> = function ({
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
      width={width}
      viewBox={viewBox}
    >
      <WDUnitController meta={meta} setIconState={setFluidIconState}>
        <WDArmyIcon country={meta.country} iconState={fluidIconState} />
      </WDUnitController>
    </svg>
  );
};

export default WDArmy;
