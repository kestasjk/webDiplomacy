import React from "react";
import ReactDOM from "react-dom";
import Territory from "../../enums/map/variants/classic/Territory";

export default function addUnitToTerritory(
  territory: Territory,
  unit: React.ReactElement,
  unitSlotName = "main",
): void {
  const unitSlotId = `${Territory[territory]}-${unitSlotName}-unit-slot`;
  const unitSlot = document.getElementById(unitSlotId);
  if (unitSlot) {
    ReactDOM.render(unit, unitSlot);
  }
}
