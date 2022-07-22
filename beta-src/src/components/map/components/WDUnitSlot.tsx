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
    <g
      className="unit-slot"
      id={`${name}-unit-slot`}
      style={{ overflow: "visible" }}
      x={x}
      y={y}
      transform={`translate(${x} ${y})`}
    >
      {children}
    </g>
  );
};

export default WDUnitSlot;
