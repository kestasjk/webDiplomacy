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
import OrdersMeta from "../../state/interfaces/SavedOrders";

// What we manually construct to unify historical and live units and
// pass down to deeper state for rendering the UI
export interface Unit {
  country: Country;
  mappedTerritory: MTerritory;
  unit: IUnit;
  isRetreating: boolean;
  isDislodging: boolean;
  movedFromTerrID: string | null;
  isBuild: boolean;
}

export function getUnitsLive(
  territories: APITerritories,
  territoryStatuses: ITerrStatus[],
  units: { [key: string]: IUnit },
  members: GameOverviewResponse["members"],
  prevPhaseOrders: IOrderDataHistorical[],
  ordersMeta: OrdersMeta,
): Unit[] {
  // Accumulate all the units we want to draw into here
  const unitsToDraw: Unit[] = [];

  //--------------------------------------------------------------------
  // Precompute a bunch of useful mappings
  //--------------------------------------------------------------------

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

  //--------------------------------------------------------------------
  // Compute the units to draw from the current units
  //--------------------------------------------------------------------
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
            isBuild: false,
          });
        }
      }
    }
  });

  //--------------------------------------------------------------------
  // Compute all the additional units to draw from the current orders
  //--------------------------------------------------------------------
  Object.values(ordersMeta).forEach(({ update }, index) => {
    if (
      !update ||
      !update.type.startsWith("Build ") ||
      update.toTerrID === null
    ) {
      return;
    }
    const territory = territories[update.toTerrID];
    const iUnit: IUnit = {
      // Arbitrarily add 100000 to get unique ids from the normal units
      id: (index + 100000).toString(),
      countryID: territory.countryID,
      type: update.type.split(" ")[1] as unknown as string, // Build Army --> Army
      terrID: update.toTerrID,
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
          unitsToDraw.push({
            country: countryMap[country],
            mappedTerritory,
            unit: iUnit,
            isRetreating: false,
            isDislodging: false,
            movedFromTerrID: null,
            isBuild: true,
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
  curPhaseOrders: IOrderDataHistorical[],
): Unit[] {
  // Accumulate all the units we want to draw into here
  const unitsToDraw: Unit[] = [];

  //--------------------------------------------------------------------
  // Precompute a bunch of useful mappings
  //--------------------------------------------------------------------

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

  //--------------------------------------------------------------------
  // Compute the units to draw from the historical units
  //--------------------------------------------------------------------
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
            isBuild: false,
          });
        }
      }
    }
  });

  //--------------------------------------------------------------------
  // Compute all the units additional to draw from the historical orders
  //--------------------------------------------------------------------
  curPhaseOrders.forEach((order, index) => {
    if (!order.type.startsWith("Build ")) {
      return;
    }
    const territory = territories[order.terrID];
    const iUnit: IUnit = {
      // Arbitrarily add 100000 to get unique ids from the normal units
      id: (index + 100000).toString(),
      countryID: order.countryID.toString(),
      type: order.type.split(" ")[1] as unknown as string, // Build Army --> Army
      terrID: order.terrID.toString(),
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
          unitsToDraw.push({
            country: countryMap[country],
            mappedTerritory,
            unit: iUnit,
            isRetreating: false,
            isDislodging: false,
            movedFromTerrID: null,
            isBuild: true,
          });
        }
      }
    }
  });

  return unitsToDraw;
}
