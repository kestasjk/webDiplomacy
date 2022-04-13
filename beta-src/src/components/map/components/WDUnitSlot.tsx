import * as React from "react";
import { AbsoluteCoordinates } from "../../../interfaces";
import TerritoryName from "../../../types/TerritoryName";

interface WDUnitSlotProps extends AbsoluteCoordinates {
  name: string;
  territoryName: TerritoryName;
}

const WDUnitSlot: React.FC<WDUnitSlotProps> = function ({
  name,
  territoryName,
  x,
  y,
}): React.ReactElement {
  return (
    <svg
      className="unit-slot"
      data-unit-slot={territoryName}
      id={`${territoryName}-${name}-unit-slot`}
      x={x}
      y={y}
    />
  );
};

export default WDUnitSlot;
