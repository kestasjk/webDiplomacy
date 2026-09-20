import * as React from "react";
import debounce from "../../utils/debounce";
import WDFleetIcon, {
  FLEET_RAW_ICON_WIDTH,
  FLEET_RAW_ICON_HEIGHT,
} from "../ui/units/WDFleetIcon";
import WDArmyIcon, {
  ARMY_RAW_ICON_WIDTH,
  ARMY_RAW_ICON_HEIGHT,
} from "../ui/units/WDArmyIcon";
import { Unit } from "../../utils/map/getUnits";
import UIState from "../../enums/UIState";
import { makeSVGDrawAsUnsavedAnimateElementGentle } from "../../utils/map/drawArrowFunctional";

interface UnitControllerProps {
  unit: Unit;
  unitState: UIState;
  unitWidth: number;
  unitHeight: number;
}

const WDUnitController: React.FC<UnitControllerProps> = function ({
  unit,
  unitState,
  unitWidth,
  unitHeight,
}): React.ReactElement {
  const iconWidth =
    unit.unit.type === "Fleet" ? FLEET_RAW_ICON_WIDTH : ARMY_RAW_ICON_WIDTH;
  const iconHeight =
    unit.unit.type === "Fleet" ? FLEET_RAW_ICON_HEIGHT : ARMY_RAW_ICON_HEIGHT;

  // The icons expect that 0,0 is the upper left corner of the unit, whereas this WDUnitController
  // svg element is expected to be positioned such that 0,0 is the center of the
  // unit. So we need to translate to reconcile these.
  const xOffset = iconWidth / 2;
  const yOffset = iconHeight / 2;

  // Translate from the coordinate scale of the icons in WDFleetIcon and
  // WDArmyIcon to the size we want to actually draw this unit
  const xScale = unitWidth / iconWidth;
  const yScale = unitHeight / iconHeight;

  const transform = `scale(${xScale}, ${yScale}) translate(-${xOffset},-${yOffset})`;
  return (
    <g
      style={{ pointerEvents: "none", overflow: "visible" }}
      transform={transform}
    >
      {unit.unit.type === "Fleet" && (
        <WDFleetIcon iconState={unitState} country={unit.country} />
      )}
      {unit.unit.type === "Army" && (
        <WDArmyIcon iconState={unitState} country={unit.country} />
      )}
      {unit.drawAsUnsaved && makeSVGDrawAsUnsavedAnimateElementGentle()}
    </g>
  );
};

export default WDUnitController;
