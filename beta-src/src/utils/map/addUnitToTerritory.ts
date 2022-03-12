import React from "react";
import ReactDOM from "react-dom";
import Territory from "../../enums/Territory";

export default function addUnitToTerritory(
  territory: Territory,
  unit: React.ReactElement,
): void {
  const unitSlotId = `${Territory[territory]}-unit-slot`;
  const unitSlot = document.getElementById(unitSlotId);
  if (unitSlot) {
    ReactDOM.render(unit, unitSlot);
  }
}
