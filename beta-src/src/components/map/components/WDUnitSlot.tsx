import * as React from "react";
import { AbsoluteCoordinates } from "../../../interfaces";
import TerritoryName from "../../../types/TerritoryName";

interface WDUnitSlotProps extends AbsoluteCoordinates {
  terr: TerritoryName;
}

const WDUnitSlot: React.FC<WDUnitSlotProps> = function ({
  x,
  y,
  terr,
}): React.ReactElement {
  return <svg id={`${terr}-unit-slot`} x={x} y={y} />;
};

export default WDUnitSlot;
