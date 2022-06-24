import * as React from "react";
import { useTheme } from "@mui/material/styles";
import WDUnitController from "../../controllers/WDUnitController";
import { Unit } from "../../../utils/map/getUnits";
import UIState from "../../../enums/UIState";

export const UNIT_HEIGHT = 75;
export const UNIT_WIDTH = 75;

interface WDUnitProps {
  id: string | undefined;
  unit: Unit;
  unitState: UIState;
}

const WDUnit: React.FC<WDUnitProps> = function ({
  id,
  unit,
  unitState,
}): React.ReactElement {
  const theme = useTheme();
  return (
    <svg
      filter={theme.palette.svg.filters.dropShadows[1]}
      height={UNIT_HEIGHT}
      id={id}
      width={UNIT_WIDTH}
      style={{ overflow: "visible" }}
    >
      <WDUnitController
        unit={unit}
        unitState={unitState}
        unitWidth={UNIT_WIDTH}
        unitHeight={UNIT_HEIGHT}
      />
    </svg>
  );
};

export default WDUnit;
