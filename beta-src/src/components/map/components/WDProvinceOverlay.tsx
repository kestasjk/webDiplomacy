import * as React from "react";
import UIState from "../../../enums/UIState";
import { ProvinceMapData } from "../../../interfaces";
import UnitType from "../../../types/UnitType";
import WDUnit from "../../ui/units/WDUnit";
import WDUnitSlot from "./WDUnitSlot";
import { Unit, UnitDrawMode } from "../../../utils/map/getUnits";
import Province from "../../../enums/map/variants/classic/Province";
import Territory from "../../../enums/map/variants/classic/Territory";

interface WDProvinceOverlayProps {
  provinceMapData: ProvinceMapData;
  units: Unit[];
}

const WDProvinceOverlay: React.FC<WDProvinceOverlayProps> = function ({
  provinceMapData,
  units,
}): React.ReactElement {
  const { province } = provinceMapData;

  // Maps unitSlot name -> unit to draw.
  const unitFCs: { [key: string]: React.ReactElement } = {};
  // Maps unitSlot name -> unit to draw, but specifically for units
  // that are currently disloging another unit on a retreat phase.
  // This is separate because we need to draw the
  // dislodger unit in an alternative location when there are two
  // units in a territory so that they don't overlap each other, including
  // when those units share the same unitSlot within that territory.
  const unitFCsDislodging: { [key: string]: React.ReactElement } = {};

  units
    .filter((unit) => unit.mappedTerritory.province === province)
    .forEach((unit) => {
      let unitState: UIState;
      switch (unit.drawMode) {
        case UnitDrawMode.NONE:
          unitState = UIState.NONE;
          break;
        case UnitDrawMode.HOLD:
          unitState = UIState.HOLD;
          break;
        case UnitDrawMode.BUILD:
          // This state of drawing the unit reduces constrast on the unit and isn't necessary
          // now that we have green build circles highlighting the new builds.
          // unitState = UIState.BUILD;
          unitState = UIState.NONE;
          break;
        case UnitDrawMode.DISLODGING:
          unitState = UIState.NONE;
          break;
        case UnitDrawMode.DISLODGED:
          unitState = UIState.DISLODGED;
          break;
        case UnitDrawMode.DISBANDED:
          unitState = UIState.DISBANDED;
          break;
        default:
          unitState = UIState.NONE;
          break;
      }
      const wdUnit = (
        <WDUnit id={`${province}-unit`} unit={unit} unitState={unitState} />
      );
      if (unit.drawMode === UnitDrawMode.DISLODGING) {
        unitFCsDislodging[unit.mappedTerritory.unitSlotName] = wdUnit;
      } else {
        unitFCs[unit.mappedTerritory.unitSlotName] = wdUnit;
      }
    });

  return (
    <svg
      height={provinceMapData.height}
      id={`${province}-province-overlay`}
      viewBox={provinceMapData.viewBox}
      width={provinceMapData.width}
      x={provinceMapData.x}
      y={provinceMapData.y}
      overflow="visible"
    >
      {provinceMapData.unitSlots
        .filter(({ name }) => name in unitFCs)
        .map(({ name, x, y }) => (
          <WDUnitSlot key={name} name={name} x={x} y={y}>
            {unitFCs[name]}
          </WDUnitSlot>
        ))}
      {provinceMapData.unitSlots
        .filter(({ name }) => name in unitFCsDislodging)
        .map(({ name, arrowReceiver }) => {
          const unitName = `${name}-dislodging`;
          // For dislodger units, we draw them at the location of the
          // arrow receiver.
          return (
            <WDUnitSlot
              key={unitName}
              name={unitName}
              x={arrowReceiver.x}
              y={arrowReceiver.y}
            >
              {unitFCsDislodging[name]}
            </WDUnitSlot>
          );
        })}
    </svg>
  );
};

export default WDProvinceOverlay;
