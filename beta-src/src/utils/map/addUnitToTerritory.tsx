import { ThemeProvider } from "@mui/material";
import React from "react";
import ReactDOM from "react-dom";
import { Provider } from "react-redux";
import WDArmyIcon from "../../components/svgr-components/WDArmyIcon";
import WDFleetIcon from "../../components/svgr-components/WDFleetIcon";
import { ITerritory } from "../../data/map/variants/classic/TerritoryMap";
import Country from "../../enums/Country";
import Territory from "../../enums/map/variants/classic/Territory";
import { UnitMeta } from "../../interfaces";
import { IUnit } from "../../models/Interfaces";
import { store } from "../../state/store";
import webDiplomacyTheme from "../../webDiplomacyTheme";

export default function addUnitToTerritory(
  mappedTerritory: ITerritory,
  unit: IUnit,
  country: Country,
): void {
  const unitSlotId = `${Territory[mappedTerritory.territory]}-${
    mappedTerritory.unitSlotName
  }-unit-slot`;
  const unitSlot = document.getElementById(unitSlotId);
  const meta: UnitMeta = {
    mappedTerritory,
    unit,
    country,
  };
  const unitIcon =
    unit.type === "Army" ? (
      <WDArmyIcon country={country} meta={meta} />
    ) : (
      <WDFleetIcon country={country} meta={meta} />
    );
  const wrappedUnit = (
    <Provider store={store}>
      <ThemeProvider theme={webDiplomacyTheme}>{unitIcon}</ThemeProvider>
    </Provider>
  );
  if (unitSlot) {
    ReactDOM.render(wrappedUnit, unitSlot);
  }
}
