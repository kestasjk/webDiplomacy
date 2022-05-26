import * as React from "react";
import Territory from "../../../enums/map/variants/classic/Territory";
import { Coordinates } from "../../../interfaces";

interface WDUnitSlotProps extends Coordinates {
  name: string;
  territory: Territory;
}

const WDUnitSlot: React.FC<WDUnitSlotProps> = function ({
  children,
  name,
  territory,
  x,
  y,
}): React.ReactElement {
  return (
    <svg
      className="unit-slot"
      data-unit-slot={territory}
      id={`${territory}-${name}-unit-slot`}
      x={x}
      y={y}
    >
      {children}
    </svg>
  );
};

export default WDUnitSlot;
