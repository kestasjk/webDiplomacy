import * as React from "react";
import Territory from "../../../enums/Territory";
import { AbsoluteCoordinates } from "../../../interfaces";
import WDArmyIcon from "../../svgr-components/WDArmyIcon";
import Country from "../../../enums/Country";

interface WDUnitSlotProps extends AbsoluteCoordinates {
  terr: Territory;
}

const WDUnitSlot: React.FC<WDUnitSlotProps> = function ({
  x,
  y,
  terr,
}): React.ReactElement {
  return (
    <svg id={`${terr}-unit-slot`} x={x} y={y}>
      <WDArmyIcon viewBox="7.5 7.5 35 35" country={Country.RUSSIA} />
    </svg>
  );
};

export default WDUnitSlot;
