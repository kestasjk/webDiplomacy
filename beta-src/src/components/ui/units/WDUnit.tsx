import * as React from "react";
import { useTheme } from "@mui/material/styles";
import UIState from "../../../enums/UIState";
import WDUnitController from "../../controllers/WDUnitController";
import { GameIconProps } from "../../../interfaces/Icons";
import WDArmyIcon from "./WDArmyIcon";
import { useAppSelector } from "../../../state/hooks";
import { gameUnitState } from "../../../state/game/game-api-slice";

export const UNIT_HEIGHT = 50;
export const UNIT_WIDTH = 50;

const WDUnit: React.FC<GameIconProps> = function ({
  id = undefined,
  meta,
  viewBox,
  type,
  iconState,
}): React.ReactElement {
  const theme = useTheme();
  return (
    <svg
      filter={theme.palette.svg.filters.dropShadows[1]}
      height={UNIT_HEIGHT}
      id={id}
      width={UNIT_WIDTH}
      viewBox={viewBox}
    >
      <WDUnitController meta={meta} type={type} iconState={iconState} />
    </svg>
  );
};

export default WDUnit;
