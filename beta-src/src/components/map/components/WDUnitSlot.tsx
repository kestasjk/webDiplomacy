import * as React from "react";
import Territory from "../../../enums/map/variants/classic/Territory";
import { Coordinates } from "../../../interfaces";

interface WDUnitSlotProps extends Coordinates {
  name: string;
}

const WDUnitSlot: React.FC<WDUnitSlotProps> = function ({
  children,
  name,
  x,
  y,
}): React.ReactElement {
  return (
    <svg className="unit-slot" id={`${name}-unit-slot`} x={x} y={y}>
      {children}
    </svg>
  );
};

export default WDUnitSlot;
