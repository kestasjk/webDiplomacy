import GameDataResponse, {
  APITerritories,
} from "../../state/interfaces/GameDataResponse";
import GameOverviewResponse from "../../state/interfaces/GameOverviewResponse";
import TerritoryMap, {
  MTerritory,
  webdipNameToTerritory,
} from "../../data/map/variants/classic/TerritoryMap";
import countryMap from "../../data/map/variants/classic/CountryMap";
import Country from "../../enums/Country";
import {
  IOrderDataHistorical,
  ITerrStatus,
  IUnit,
  IUnitHistorical,
} from "../../models/Interfaces";
import UIState from "../../enums/UIState";

// What we manually construct to unify historical and live units and
// pass down to deeper state for rendering the UI
export interface Unit {
  country: Country;
  mappedTerritory: MTerritory;
  unit: IUnit;
  isRetreating: boolean;
  isDislodging: boolean;
  movedFromTerrID: string | null;
}

export function getUnitsLive(
  territories: APITerritories,
  territoryStatuses: ITerrStatus[],
  units: { [key: string]: IUnit },
  members: GameOverviewResponse["members"],
  prevPhaseOrders: IOrderDataHistorical[],
): Unit[] {
  const unitsToDraw: Unit[] = [];
  // console.log({ units });

  const territoryStatusesByTerrID = Object.fromEntries(
    territoryStatuses.map((territoryStatus) => [
      territoryStatus.id,
      territoryStatus,
    ]),
  );
  const unitCountByTerrID: { [key: string]: number } = {};
  Object.values(units).forEach((unit) => {
    if (unit.terrID in unitCountByTerrID) {
      unitCountByTerrID[unit.terrID] += 1;
    } else {
      unitCountByTerrID[unit.terrID] = 1;
    }
  });

  // Maps current terrID => previous terrID for units that successfully moved last phase.
  const successfulMoves: { [key: string]: string } = {};
  prevPhaseOrders.forEach((prevOrder) => {
    if (prevOrder.success && prevOrder.type === "Move") {
      successfulMoves[prevOrder.toTerrID.toString()] =
        prevOrder.terrID.toString();
    }
  });
  // console.log({ prevPhaseOrders });
  // console.log({ successfulMoves });

  Object.values(units).forEach((unit) => {
    const territory = territories[unit.terrID];
    if (territory) {
      const mappedTerritory =
        TerritoryMap[webdipNameToTerritory[territory.name]];
      if (mappedTerritory) {
        const memberCountry = members.find(
          (member) => member.countryID.toString() === unit.countryID,
        );
        if (memberCountry) {
          const { country } = memberCountry;

          const isRetreating =
            territoryStatusesByTerrID[unit.terrID] &&
            territoryStatusesByTerrID[unit.terrID].unitID !== null &&
            territoryStatusesByTerrID[unit.terrID].unitID !== unit.id;

          const isDislodging =
            unitCountByTerrID[unit.terrID] >= 2 && !isRetreating;

          const movedFromTerrID =
            unit.terrID in successfulMoves
              ? successfulMoves[unit.terrID]
              : null;

          unitsToDraw.push({
            country: countryMap[country],
            mappedTerritory,
            unit,
            isRetreating,
            isDislodging,
            movedFromTerrID,
          });
        }
      }
    }
  });
  return unitsToDraw;
}

export function getUnitsHistorical(
  territories: APITerritories,
  units: IUnitHistorical[],
  members: GameOverviewResponse["members"],
  prevPhaseOrders: IOrderDataHistorical[],
): Unit[] {
  const unitsToDraw: Unit[] = [];
  console.log({ units });

  const unitCountByTerrID: { [key: string]: number } = {};
  units.forEach((unit) => {
    if (unit.terrID in unitCountByTerrID) {
      unitCountByTerrID[unit.terrID] += 1;
    } else {
      unitCountByTerrID[unit.terrID] = 1;
    }
  });

  // Maps current terrID => previous terrID for units that successfully moved last phase.
  const successfulMoves: { [key: string]: string } = {};
  prevPhaseOrders.forEach((prevOrder) => {
    if (prevOrder.success && prevOrder.type === "Move") {
      successfulMoves[prevOrder.toTerrID.toString()] =
        prevOrder.terrID.toString();
    }
  });
  units.forEach((unit, index) => {
    const territory = territories[unit.terrID];
    const iUnit = {
      id: index.toString(),
      countryID: unit.countryID.toString(),
      type: unit.unitType,
      terrID: unit.terrID.toString(),
    };

    if (territory) {
      const mappedTerritory =
        TerritoryMap[webdipNameToTerritory[territory.name]];
      if (mappedTerritory) {
        const memberCountry = members.find(
          (member) => member.countryID.toString() === iUnit.countryID,
        );
        if (memberCountry) {
          const { country } = memberCountry;

          const isRetreating = unit.retreating === "Yes";
          const isDislodging =
            unitCountByTerrID[unit.terrID] >= 2 && !isRetreating;

          const movedFromTerrID =
            unit.terrID in successfulMoves
              ? successfulMoves[unit.terrID]
              : null;

          unitsToDraw.push({
            country: countryMap[country],
            mappedTerritory,
            unit: iUnit,
            isRetreating,
            isDislodging,
            movedFromTerrID,
          });
        }
      }
    }
  });
  return unitsToDraw;
}
