import provincesMapData from "../../../../../data/map/ProvincesMapData";
import TerritoryMap from "../../../../../data/map/variants/classic/TerritoryMap";
import Province from "../../../../../enums/map/variants/classic/Province";
import Territory from "../../../../../enums/map/variants/classic/Territory";
import { UnitType } from "../../../../../models/enums";
import { IProvinceStatus, ITerritory } from "../../../../../models/Interfaces";
import { GameData } from "../../../../../state/interfaces/GameDataResponse";
import GameOverviewResponse from "../../../../../state/interfaces/GameOverviewResponse";
import { GameState } from "../../../../../state/interfaces/GameState";
import GameStateMaps from "../../../../../state/interfaces/GameStateMaps";

interface LegalVia {
  dest: Territory;
  provIDPaths: string[][];
}

interface LegalConvoy {
  src: Territory;
  dest: Territory;
  // Concatenate these two arrays if you want the final convoy path.
  // Do NOT modify these arrays.
  provIDPath1: string[];
  provIDPath2: string[];
}

export interface LegalOrders {
  legalMoveDestsByUnitID: { [key: string]: Territory[] };
  legalRetreatDestsByUnitID: { [key: string]: Territory[] };
  possibleBuildDests: Territory[];
  legalViasByUnitID: { [key: string]: LegalVia[] };
  legalConvoysByUnitID: { [key: string]: { [key: string]: LegalConvoy[] } };
  legalSupportsByUnitID: { [key: string]: { [key: string]: Province[] } };
}

// Returns all destination territories that a unit can legally move to on its own.
// Empty on non-movement phases.
// Computes for ALL players.
export function getAllLegalMoveDestsByUnitID(
  overview: GameOverviewResponse,
  data: GameData,
): {
  [key: string]: Territory[];
} {
  if (overview.phase !== "Diplomacy") {
    return {};
  }
  console.log("getAllLegalMoveDestsByUnitID");
  const legalMoveDestsByUnitID: { [key: string]: Territory[] } = {};

  Object.entries(data.units).forEach(([unitID, unit]) => {
    const legalDests: Territory[] = [];
    const iTerr = data.territories[unit.terrID];
    const borderKind = unit.type === UnitType.Army ? "a" : "f";
    iTerr.CoastalBorders.forEach((border) => {
      // If this unit type is allowed to cross this border, then add the dest
      if (border[borderKind]) {
        legalDests.push(
          TerritoryMap[data.territories[border.id].name].territory,
        );
      }
    });
    legalMoveDestsByUnitID[unitID] = legalDests;
  });
  return legalMoveDestsByUnitID;
}

// Returns all destination territories that a unit can legally retreat to.
// Empty on non-retreat phases.
// Only includes units that were dislodged.
// Doesn't attempt to represent or include the disband action.
// Computes for only the player playing.
// Contains an entry for each dislodged unit of the current player even if
// that entry is empty.
export function getAllLegalRetreatDestsByUnitID(
  overview: GameOverviewResponse,
  data: GameData,
  maps: GameStateMaps,
): {
  [key: string]: Territory[];
} {
  if (overview.phase !== "Retreats") {
    return {};
  }
  console.log("getAllLegalRetreatDestsByUnitID");
  const legalRetreatDestsByUnitID: { [key: string]: Territory[] } = {};

  const provinceStatusByProvID: { [key: string]: IProvinceStatus } = {};
  data.territoryStatuses.forEach((provinceStatus) => {
    provinceStatusByProvID[provinceStatus.id] = provinceStatus;
  });
  const ourCountryID = overview.user.member.countryID.toString();
  Object.entries(data.units).forEach(([unitID, unit]) => {
    // If this unit is owned by someone else, then don't compute.
    if (unit.countryID !== ourCountryID) {
      return;
    }
    const provID = maps.terrIDToProvinceID[unit.terrID];
    const provStatus = provinceStatusByProvID[provID];
    // If this is the rightful unit holding the territory, then it doesn't need to retreat.
    if (!provStatus || provStatus.unitID === unitID) {
      return;
    }
    const legalDests: Territory[] = [];
    const iTerr = data.territories[unit.terrID];
    const borderKind = unit.type === UnitType.Army ? "a" : "f";
    // If non-null, a dislodger occupied our province coming from this province
    const occupiedFromProvID: string | null = provStatus.occupiedFromTerrID
      ? maps.terrIDToProvinceID[provStatus.occupiedFromTerrID]
      : null;
    iTerr.CoastalBorders.forEach((border) => {
      // If this unit type is allowed to cross this border...
      if (border[borderKind]) {
        const borderProvID = maps.terrIDToProvinceID[border.id];
        const borderStatus = provinceStatusByProvID[borderProvID];
        // Cannot retreat to bordering provinces with units
        // Cannot retreat to bordering provinces with a standoff
        // Cannot retreat to any bordering province that the dislodger occupied our province from.
        // Note that all these checks need to be done at the province level, not the territory level.
        if (
          borderStatus &&
          !borderStatus.unitID &&
          !borderStatus.standoff &&
          occupiedFromProvID !== borderProvID
        ) {
          legalDests.push(
            TerritoryMap[data.territories[border.id].name].territory,
          );
        }
      }
    });
    legalRetreatDestsByUnitID[unitID] = legalDests;
  });
  return legalRetreatDestsByUnitID;
}

// Returns all territories that we could legally build in, if we had available builds
// (does not actually check if we can build or how many builds we have).
export function getAllPossibleBuildDests(
  overview: GameOverviewResponse,
  data: GameData,
  maps: GameStateMaps,
): Territory[] {
  if (overview.phase !== "Builds") {
    return [];
  }
  console.log("getAllPossibleBuildDests");
  const possibleBuildDests: Territory[] = [];

  const ourCountryID = overview.user.member.countryID.toString();
  data.territoryStatuses.forEach((provStatus) => {
    // We have to own this province.
    if (provStatus.ownerCountryID !== ourCountryID) {
      return;
    }
    // It has to be a supply center, and our home center
    if (
      data.territories[provStatus.id].supply !== "Yes" ||
      data.territories[provStatus.id].countryID !== ourCountryID
    ) {
      return;
    }

    // Iterate through all
    provincesMapData[maps.terrIDToProvince[provStatus.id]].unitSlots.forEach(
      (unitSlot) => {
        possibleBuildDests.push(unitSlot.territory);
      },
    );
  });
  return possibleBuildDests;
}

interface PathToCoast {
  dest: Territory;
  provIDPath: string[];
}

// Returns all legal convoy orders.
// Returns (legalViasByUnitID, legalConvoysByUnitID).
// Both are keyed by unitID, the inner map of legalConvoysByUnitID is keyed by the Territory
// where convoyee starts.
// Computes via moves for ALL players.
// Computes convoy moves for only the player playing.
export function getAllLegalConvoys(
  overview: GameOverviewResponse,
  data: GameData,
  maps: GameStateMaps,
): [
  { [key: string]: LegalVia[] },
  { [key: string]: { [key: string]: LegalConvoy[] } },
] {
  if (overview.phase !== "Diplomacy") {
    return [{}, {}];
  }
  console.log("getAllLegalConvoys");
  const ourCountryID = overview.user.member.countryID.toString();
  const provinceStatusByProvID: { [key: string]: IProvinceStatus } = {};
  data.territoryStatuses.forEach((provinceStatus) => {
    provinceStatusByProvID[provinceStatus.id] = provinceStatus;
  });

  const legalViasByUnitID: { [key: string]: LegalVia[] } = {};
  Object.entries(data.units).forEach(([unitID, unit]) => {
    // If this unit is not an army, then don't compute.
    if (unit.type !== UnitType.Army) {
      return;
    }
    // For armies, territory id is the same as province id.
    const initialProvID = unit.terrID;
    const legalViasByDest: { [key: string]: LegalVia } = {};

    // Perform DFS to find every location we can reach, and a path that does it.
    const reachedProvIDs = new Set<string>();
    reachedProvIDs.add(initialProvID);

    const searchAllNeighbors = function (
      iTerr: ITerritory,
      pathSoFar: string[],
    ) {
      // Use Borders instead of CoastalBorders.
      // Borders appears to be province-level adjacency, whereas CoastalBoarders
      // is territory-level adjacency.
      iTerr.Borders.forEach((border) => {
        // If fleet type is allowed to cross this border, then step through the border
        if (border.f) {
          const nextProvID = border.id;
          const nextITerr = data.territories[nextProvID];
          // The first step must be on to a sea
          if (pathSoFar.length <= 1 && nextITerr.type !== "Sea") {
            return;
          }

          if (reachedProvIDs.has(nextProvID)) {
            return;
          }
          // If it's a sea, then we recurse, so long as there is a unit there.
          if (nextITerr.type === "Sea") {
            if (provinceStatusByProvID[nextProvID]?.unitID) {
              reachedProvIDs.add(nextProvID);
              pathSoFar.push(nextProvID);
              searchAllNeighbors(nextITerr, pathSoFar);
              pathSoFar.pop();
              reachedProvIDs.delete(nextProvID);
            }
          }
          // Otherwise it's not a sea. Then we terminate.
          else {
            const dest = TerritoryMap[nextITerr.name].territory;
            if (!legalViasByDest[dest]) {
              legalViasByDest[dest] = { dest, provIDPaths: [[...pathSoFar]] };
            } else {
              legalViasByDest[dest].provIDPaths.push([...pathSoFar]);
            }
          }
        }
      });
    };
    searchAllNeighbors(data.territories[initialProvID], [initialProvID]);
    legalViasByUnitID[unitID] = Object.values(legalViasByDest);
  });

  // ========================================================================
  // Now compute something similar, from the other perspective - look at what
  // convoys our fleets can support.
  // We start in the middle at the fleet and use DFS to compute paths going outward
  // to armies and to coastal provinces.
  // The cartesian product of these two gives us all our results.
  // Note that when taking such a cartesian product, the final paths may
  // self-intersect. This is okay! We permit this.

  const legalConvoysByUnitID: {
    [key: string]: { [key: string]: LegalConvoy[] };
  } = {};
  Object.entries(data.units).forEach(([unitID, unit]) => {
    // If this unit is owned by someone else or isn't a fleet on a sea, then don't compute.
    if (
      unit.countryID !== ourCountryID ||
      unit.type !== UnitType.Fleet ||
      data.territories[unit.terrID].type !== "Sea"
    ) {
      return;
    }
    const pathsToCoastalArmy: PathToCoast[] = [];
    const pathsToCoast: PathToCoast[] = [];

    // For fleets on seas, territory id is the same as province id.
    const initialProvID = unit.terrID;

    // Perform DFS to find every coastal army and coast we can reach, and a path that does it.
    const reachedProvIDs = new Set<string>();
    reachedProvIDs.add(initialProvID);

    const searchAllNeighbors = function (
      iTerr: ITerritory,
      pathSoFar: string[],
    ) {
      // Use Borders instead of CoastalBorders.
      // Borders appears to be province-level adjacency, whereas CoastalBoarders
      // is territory-level adjacency.
      iTerr.Borders.forEach((border) => {
        // If fleet type is allowed to cross this border, then step through the border
        if (border.f) {
          const nextProvID = border.id;
          if (reachedProvIDs.has(nextProvID)) {
            return;
          }
          reachedProvIDs.add(nextProvID);
          // If it's a sea, then we recurse, so long as there is a unit there.
          const nextITerr = data.territories[nextProvID];
          if (nextITerr.type === "Sea") {
            if (provinceStatusByProvID[nextProvID]?.unitID) {
              pathSoFar.push(nextProvID);
              searchAllNeighbors(nextITerr, pathSoFar);
              pathSoFar.pop();
            }
          }
          // Otherwise it's not a sea. Then we terminate.
          else {
            const dest = TerritoryMap[nextITerr.name].territory;
            const result = { dest, provIDPath: [...pathSoFar] };
            pathsToCoast.push(result);
            const possibleArmyID = provinceStatusByProvID[nextProvID]?.unitID;
            if (
              possibleArmyID &&
              data.units[possibleArmyID].type === UnitType.Army
            ) {
              pathsToCoastalArmy.push(result);
            }
          }
        }
      });
    };
    searchAllNeighbors(data.territories[initialProvID], [initialProvID]);

    // Now putting together each path to a coast army with each path to a coast
    // gives us our final convoys that pass through this fleet.
    const legalConvoysByConvoyeeTerritory: { [key: string]: LegalConvoy[] } =
      {};
    pathsToCoastalArmy.forEach((pathToCoastalArmy) => {
      const provIDPath1 = [...pathToCoastalArmy.provIDPath];
      // Make sure the path contains the starting location, the flip it and pop
      // off the convoyer fleet to avoid double-counting the convoyer fleet
      // since it will be present at the start of provIDPath2.
      // Territory ID is equivalent to province ID here since it's an army.
      provIDPath1.push(maps.territoryToTerrID[pathToCoastalArmy.dest]);
      provIDPath1.reverse();
      provIDPath1.pop();
      const legalConvoys: LegalConvoy[] = [];
      pathsToCoast.forEach((pathToCoast) => {
        // Cannot convoy from a location to itself.
        if (pathToCoast.dest !== pathToCoastalArmy.dest) {
          legalConvoys.push({
            src: pathToCoastalArmy.dest,
            dest: pathToCoast.dest,
            provIDPath1,
            provIDPath2: pathToCoast.provIDPath,
          });
        }
      });
      legalConvoysByConvoyeeTerritory[pathToCoastalArmy.dest] = legalConvoys;
    });

    legalConvoysByUnitID[unitID] = legalConvoysByConvoyeeTerritory;
  });

  return [legalViasByUnitID, legalConvoysByUnitID];
}

// Returns all support orders that can be made by each unit, in the form of a nested
// mapping unitID -> fromProvince -> toProvince
// Support holds are encoded as having the fromProvince and toProvince.
// Computes for only the player playing.
export function getAllLegalSupportsByUnitID(
  overview: GameOverviewResponse,
  data: GameData,
  maps: GameStateMaps,
  legalMoveDestsByUnitID: { [key: string]: Territory[] },
  legalViasByUnitID: { [key: string]: LegalVia[] },
): { [key: string]: { [key: string]: Province[] } } {
  if (overview.phase !== "Diplomacy") {
    return {};
  }
  console.log("getAllLegalSupportsByUnitID");
  const legalSupportsByUnitID: {
    [key: string]: { [key: string]: Province[] };
  } = {};

  const ourCountryID = overview.user.member.countryID.toString();
  Object.entries(data.units).forEach(([unitID, unit]) => {
    // If this unit is owned by someone else, then don't compute.
    if (unit.countryID !== ourCountryID) {
      return;
    }

    const legalSupportsBySrc: { [key: string]: Province[] } = {};

    // Find all provinces that this unit can move to.
    const unitProvID = maps.terrIDToProvinceID[unit.terrID];
    const iTerr = data.territories[unit.terrID];
    const borderKind = unit.type === UnitType.Army ? "a" : "f";
    // Use Borders instead of CoastalBorders.
    // Borders appears to be province-level adjacency, whereas CoastalBoarders
    // is territory-level adjacency.
    iTerr.Borders.forEach((border) => {
      // If this unit type is allowed to cross this border, then this is
      // a destination province that we can support units to.
      if (border[borderKind]) {
        const destination: Province = maps.terrIDToProvince[border.id];
        const destinationTerrRoot: Territory =
          maps.terrIDToTerritory[border.id];
        // Now find all units that can move or VIA to the same province
        Object.entries(data.units).forEach(([supporteeID, supporteeUnit]) => {
          if (supporteeID === unitID) {
            return;
          }
          // Move to that province
          if (
            legalMoveDestsByUnitID[supporteeID].find(
              (territory) => TerritoryMap[territory].province === destination,
            )
          ) {
            const src = maps.terrIDToProvince[supporteeUnit.terrID];
            if (!legalSupportsBySrc[src]) legalSupportsBySrc[src] = [];
            legalSupportsBySrc[src].push(destination);
          }
          // VIA to that province
          else if (
            legalViasByUnitID[supporteeID] &&
            legalViasByUnitID[supporteeID].find(
              (via) =>
                // If a unit can move via convoy, it is an army, in which case it being able
                // to reach a province is equivalent to it being able to reach the root territory
                // of that province, since armies never go on special coasts.
                // So we can simply test its desination vs the root territory.
                via.dest === destinationTerrRoot &&
                via.provIDPaths.find((path) => !path.includes(unitProvID)),
            )
          ) {
            const src = maps.terrIDToProvince[supporteeUnit.terrID];
            if (!legalSupportsBySrc[src]) legalSupportsBySrc[src] = [];
            legalSupportsBySrc[src].push(destination);
          }
        });
      }
    });

    legalSupportsByUnitID[unitID] = legalSupportsBySrc;
  });
  return legalSupportsByUnitID;
}

export function getLegalOrders(
  overview: GameOverviewResponse,
  data: GameData,
  maps: GameStateMaps,
): LegalOrders {
  const legalMoveDestsByUnitID = getAllLegalMoveDestsByUnitID(overview, data);
  const legalRetreatDestsByUnitID = getAllLegalRetreatDestsByUnitID(
    overview,
    data,
    maps,
  );
  const possibleBuildDests = getAllPossibleBuildDests(overview, data, maps);
  const [legalViasByUnitID, legalConvoysByUnitID] = getAllLegalConvoys(
    overview,
    data,
    maps,
  );
  const legalSupportsByUnitID = getAllLegalSupportsByUnitID(
    overview,
    data,
    maps,
    legalMoveDestsByUnitID,
    legalViasByUnitID,
  );
  return {
    legalMoveDestsByUnitID,
    legalRetreatDestsByUnitID,
    possibleBuildDests,
    legalViasByUnitID,
    legalConvoysByUnitID,
    legalSupportsByUnitID,
  };
}
