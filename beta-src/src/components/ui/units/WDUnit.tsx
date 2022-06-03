import * as React from "react";
import { useTheme } from "@mui/material/styles";
import UIState from "../../../enums/UIState";
import WDUnitController from "../../controllers/WDUnitController";
import { GameIconProps } from "../../../interfaces/Icons";
import WDArmyIcon from "./WDArmyIcon";
import { useAppSelector } from "../../../state/hooks";

export const UNIT_HEIGHT = 75;
export const UNIT_WIDTH = 75;

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
      style={{ overflow: "visible" }}
    >
      <WDUnitController
        meta={meta}
        type={type}
        iconState={iconState}
        unitWidth={UNIT_WIDTH}
        unitHeight={UNIT_HEIGHT}
      />
    </svg>
  );
};

export default WDUnit;
