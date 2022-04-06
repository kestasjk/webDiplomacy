import React from "react";
import { ThemeProvider } from "@mui/material";
import GameDataResponse from "../../state/interfaces/GameDataResponse";
import GameOverviewResponse from "../../state/interfaces/GameOverviewResponse";
import TerritoryMap from "../../data/map/variants/classic/TerritoryMap";
import WDArmyIcon from "../../components/svgr-components/WDArmyIcon";
import addUnitToTerritory from "./addUnitToTerritory";
import WDFleetIcon from "../../components/svgr-components/WDFleetIcon";
import countryMap from "../../data/map/variants/classic/CountryMap";
import webDiplomacyTheme from "../../webDiplomacyTheme";

export default function drawUnitsOnMap(
  members: GameOverviewResponse["members"],
  data: GameDataResponse["data"],
): void {
  if (data && members && "units" in data && "territories" in data) {
    const { territories, units } = data;
    Object.values(units).forEach((unit) => {
      const territory = territories[unit.terrID];
      if (territory) {
        const mappedTerritory = TerritoryMap[territory.name];
        if (mappedTerritory) {
          const memberCountry = members.find((member) => {
            return member.countryID.toString() === unit.countryID;
          });
          if (memberCountry) {
            const { country } = memberCountry;
            const unitIcon =
              unit.type === "Army" ? (
                <WDArmyIcon country={countryMap[country]} />
              ) : (
                <WDFleetIcon country={countryMap[country]} />
              );
            const themeUnitIcon = (
              <ThemeProvider theme={webDiplomacyTheme}>
                {unitIcon}
              </ThemeProvider>
            );
            addUnitToTerritory(
              mappedTerritory.territory,
              themeUnitIcon,
              mappedTerritory.unitSlotName,
            );
          }
        }
      }
    });
  }
}
