import * as React from "react";
import Country from "../../../enums/Country";
import { AbsoluteCoordinates } from "../../../interfaces";
import TerritoryName from "../../../types/TerritoryName";
import WDArmyIcon from "../../svgr-components/WDArmyIcon";
import WDFleetIcon from "../../svgr-components/WDFleetIcon";

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
  // temporarily adding army or fleet icons to help verify unit positions - TODO
  const rand = Math.round(Math.random());
  return (
    <svg id={`${territoryName}-${name}-unit-slot`} x={x} y={y}>
      {rand && <WDArmyIcon viewBox="5 6 35 35" country={Country.FRANCE} />}
      {!rand && <WDFleetIcon viewBox="2 7 40 40" country={Country.FRANCE} />}
    </svg>
  );
};

export default WDUnitSlot;
