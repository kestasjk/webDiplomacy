import * as React from "react";
import { useTheme } from "@mui/material/styles";
import UIState from "../../../enums/UIState";
import WDUnitController from "../../controllers/WDUnitController";
import { GameIconProps } from "../../../interfaces/Icons";
import WDArmyIcon from "./WDArmyIcon";
import { useAppSelector } from "../../../state/hooks";
import { gameUnitState } from "../../../state/game/game-api-slice";

const WDUnit: React.FC<GameIconProps> = function ({
  height = 50,
  id = undefined,
  meta,
  viewBox,
  width = 50,
  type,
  iconState,
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
      <WDUnitController meta={meta} type={type} iconState={iconState} />
    </svg>
  );
};

export default WDUnit;
