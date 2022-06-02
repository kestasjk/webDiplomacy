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

type UnitID = string;
type ProvinceID = string;

interface LegalVia {
  dest: Territory;
  // A list of all the possible paths in province IDs by which this VIA move
  // could be accomplished. Contains the starting location but not the ending
  // location, because that's the format a path needs to be in to be accepted
  // by the webdip API.
  provIDPaths: ProvinceID[][];
}

interface LegalSupport {
  src: Province;
  dest: Province;
  convoyProvIDPath: ProvinceID[] | null;
}

interface LegalConvoy {
  src: Territory;
  dest: Territory;
  // Concatenate these two arrays if you want the final convoy path that you need
  // to give to webdip.
  // Do NOT modify these arrays.
  provIDPath1: ProvinceID[];
  provIDPath2: ProvinceID[];
}

export interface LegalOrders {
  legalMoveDestsByUnitID: { [key: UnitID]: Territory[] };
  legalRetreatDestsByUnitID: { [key: UnitID]: Territory[] };
  possibleBuildDests: Territory[];
  legalViasByUnitID: { [key: UnitID]: LegalVia[] };
  // The inner keys are provinces
  legalConvoysByUnitID: {
    [key: UnitID]: { [key: string]: { [key: string]: LegalConvoy } };
  };
  hasAnyLegalConvoysByUnitID: { [key: UnitID]: boolean };
  // The inner key is province
  legalSupportsByUnitID: {
    [key: UnitID]: { [key: string]: LegalSupport[] };
  };
}

// Returns all destination territories that a unit can legally move to on its own.
// Empty on non-movement phases.
// Computes for ALL players.
export function getAllLegalMoveDestsByUnitID(
  overview: GameOverviewResponse,
  data: GameData,
): {
  [key: UnitID]: Territory[];
} {
  if (overview.phase !== "Diplomacy") {
    return {};
  }
  const legalMoveDestsByUnitID: { [key: UnitID]: Territory[] } = {};

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
  [key: UnitID]: Territory[];
} {
  if (overview.phase !== "Retreats") {
    return {};
  }
  const legalRetreatDestsByUnitID: { [key: UnitID]: Territory[] } = {};

  const provinceStatusByProvID: { [key: ProvinceID]: IProvinceStatus } = {};
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
    const occupiedFromProvID: ProvinceID | null = provStatus.occupiedFromTerrID
      ? maps.terrIDToProvinceID[provStatus.occupiedFromTerrID]
      : null;
    iTerr.CoastalBorders.forEach((border) => {
      // If this unit type is allowed to cross this border...
      if (border[borderKind]) {
        const borderProvID = maps.terrIDToProvinceID[border.id];
        const borderStatus = provinceStatusByProvID[borderProvID];
        // Cannot retreat to any bordering province that the dislodger occupied our province from.
        // If borderStatus doesn't exist at all, then webdip is indicating that there are no units there
        // and probably no standoff or any other properites there. We should assume that they are all
        // defaults, and therefore a retreat there is possible.
        // Otherwise...
        // Cannot retreat to bordering provinces with units
        // Cannot retreat to bordering provinces with a standoff
        // Note that all these checks need to be done at the province level, not the territory level.
        if (
          occupiedFromProvID !== borderProvID &&
          (!borderStatus || (!borderStatus.unitID && !borderStatus.standoff))
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
  const possibleBuildDests: Territory[] = [];

  const ourCountryID = overview.user.member.countryID.toString();
  data.territoryStatuses.forEach((provStatus) => {
    // We have to own this province.
    if (provStatus.ownerCountryID !== ourCountryID) {
      return;
    }
    // It has to be a supply center, and our home center
    // And there can't be any units there.
    if (
      data.territories[provStatus.id].supply !== "Yes" ||
      data.territories[provStatus.id].countryID !== ourCountryID ||
      provStatus.unitID !== null
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
  provIDPath: UnitID[];
}

// Returns all legal convoy orders.
// Returns (legalViasByUnitID, legalConvoysByUnitID).
// Both are keyed by unitID, the inner map of legalConvoysByUnitID is keyed by the
// Territory, or equivalently the Province, where the convoyee starts.
// Computes via moves for ALL players.
// Computes convoy moves for only the player playing.
export function getAllLegalConvoys(
  overview: GameOverviewResponse,
  data: GameData,
  maps: GameStateMaps,
): [
  { [key: UnitID]: LegalVia[] },
  { [key: UnitID]: { [key: string]: { [key: string]: LegalConvoy } } },
] {
  if (overview.phase !== "Diplomacy") {
    return [{}, {}];
  }
  const ourCountryID = overview.user.member.countryID.toString();
  const provinceStatusByProvID: { [key: ProvinceID]: IProvinceStatus } = {};
  data.territoryStatuses.forEach((provinceStatus) => {
    provinceStatusByProvID[provinceStatus.id] = provinceStatus;
  });

  const legalViasByUnitID: { [key: UnitID]: LegalVia[] } = {};
  Object.entries(data.units).forEach(([unitID, unit]) => {
    // If this unit is not an army, then don't compute.
    if (unit.type !== UnitType.Army) {
      return;
    }
    // For armies, territory id is the same as province id.
    const initialProvID = unit.terrID;
    const legalViasByDest: { [key: string]: LegalVia } = {};

    // Perform DFS to find every location we can reach, and a path that does it.
    const reachedProvIDs = new Set<ProvinceID>();
    reachedProvIDs.add(initialProvID);

    const searchAllNeighbors = function (
      iTerr: ITerritory,
      pathSoFar: ProvinceID[],
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
  // We filter the results for paths that don't re-use the same fleet more than once.

  const legalConvoysByUnitID: {
    [key: UnitID]: { [key: string]: { [key: string]: LegalConvoy } };
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
    const reachedProvIDs = new Set<ProvinceID>();
    reachedProvIDs.add(initialProvID);

    const searchAllNeighbors = function (
      iTerr: ITerritory,
      pathSoFar: ProvinceID[],
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
          // If it's a sea, then we recurse, so long as there is a unit there.
          const nextITerr = data.territories[nextProvID];
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

    // Now putting together paths to each unique coast army with paths to a coast
    // gives us our final convoys that pass through this fleet.
    const legalConvoysByConvoyeeTerritory: {
      [key: string]: { [key: string]: LegalConvoy };
    } = {};
    pathsToCoastalArmy.forEach((pathToCoastalArmy) => {
      const provIDPath1 = [...pathToCoastalArmy.provIDPath];
      // Make sure the path contains the starting location, the flip it and pop
      // off the convoyer fleet to avoid double-counting the convoyer fleet
      // since it will be present at the start of provIDPath2.
      // Territory ID is equivalent to province ID here since it's an army.
      provIDPath1.push(maps.territoryToTerrID[pathToCoastalArmy.dest]);
      provIDPath1.reverse();
      provIDPath1.pop();

      const provIDPath1Set = new Set(provIDPath1);

      if (!legalConvoysByConvoyeeTerritory[pathToCoastalArmy.dest]) {
        legalConvoysByConvoyeeTerritory[pathToCoastalArmy.dest] = {};
      }
      const legalConvoysByDestination =
        legalConvoysByConvoyeeTerritory[pathToCoastalArmy.dest];

      pathsToCoast.forEach((pathToCoast) => {
        // Cannot convoy from a location to itself.
        if (pathToCoast.dest === pathToCoastalArmy.dest) return;
        // If we already found a valid convoy for this army to this dest, skip
        if (legalConvoysByDestination[pathToCoast.dest]) return;

        // Check whether the to the destination overlaps with the path to the army
        if (
          pathToCoast.provIDPath.every(
            (provIDInPath) => !provIDPath1Set.has(provIDInPath),
          )
        ) {
          legalConvoysByDestination[pathToCoast.dest] = {
            src: pathToCoastalArmy.dest,
            dest: pathToCoast.dest,
            provIDPath1,
            provIDPath2: pathToCoast.provIDPath,
          };
        }
      });
    });

    // Filter out all armies from the map that didn't find a valid way to convoy somewhere
    const convoyeeSrcs = Object.keys(legalConvoysByConvoyeeTerritory);
    convoyeeSrcs.forEach((convoyeeSrc) => {
      if (
        Object.keys(legalConvoysByConvoyeeTerritory[convoyeeSrc]).length <= 0
      ) {
        delete legalConvoysByConvoyeeTerritory[convoyeeSrc];
      }
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
  legalMoveDestsByUnitID: { [key: UnitID]: Territory[] },
  legalViasByUnitID: { [key: UnitID]: LegalVia[] },
): { [key: UnitID]: { [key: ProvinceID]: LegalSupport[] } } {
  if (overview.phase !== "Diplomacy") {
    return {};
  }
  const legalSupportsByUnitID: {
    [key: UnitID]: { [key: ProvinceID]: LegalSupport[] };
  } = {};

  const ourCountryID = overview.user.member.countryID.toString();
  Object.entries(data.units).forEach(([unitID, unit]) => {
    // If this unit is owned by someone else, then don't compute.
    if (unit.countryID !== ourCountryID) {
      return;
    }

    const legalSupportsBySrc: { [key: ProvinceID]: LegalSupport[] } = {};
    const addSupport = function (
      src: Province,
      dest: Province,
      convoyProvIDPath: ProvinceID[] | null,
    ) {
      if (!legalSupportsBySrc[src]) {
        legalSupportsBySrc[src] = [];
      }
      legalSupportsBySrc[src].push({ src, dest, convoyProvIDPath });
    };

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
          // Support hold
          const supporteeProvince = maps.terrIDToProvince[supporteeUnit.terrID];
          if (supporteeProvince === destination) {
            addSupport(supporteeProvince, destination, null);
          }
          // Move to that province
          else if (
            legalMoveDestsByUnitID[supporteeID].find(
              (territory) => TerritoryMap[territory].province === destination,
            )
          ) {
            addSupport(supporteeProvince, destination, null);
          }
          // VIA to that province
          else if (legalViasByUnitID[supporteeID]) {
            const foundVia = legalViasByUnitID[supporteeID].find(
              (via) =>
                // If a unit can move via convoy, it is an army, in which case it being able
                // to reach a province is equivalent to it being able to reach the root territory
                // of that province, since armies never go on special coasts.
                // So we can simply test its desination vs the root territory.
                via.dest === destinationTerrRoot &&
                via.provIDPaths.find((path) => !path.includes(unitProvID)),
            );
            if (foundVia) {
              // Make the typechecker happy with || [], this should be guaranteed to succeed though
              const foundPath =
                foundVia.provIDPaths.find(
                  (path) => !path.includes(unitProvID),
                ) || [];
              addSupport(supporteeProvince, destination, foundPath);
            }
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
  const startTimeStamp = performance.now();
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
  const hasAnyLegalConvoysByUnitID = Object.fromEntries(
    Object.entries(legalConvoysByUnitID).map(([unitID, convoysBySrc]) => [
      unitID,
      Object.values(convoysBySrc).length > 0,
    ]),
  );

  const legalSupportsByUnitID = getAllLegalSupportsByUnitID(
    overview,
    data,
    maps,
    legalMoveDestsByUnitID,
    legalViasByUnitID,
  );
  const endTimeStamp = performance.now();
  // console.log(
  //   `getLegalOrders took ${endTimeStamp - startTimeStamp} milliseconds`,
  // );
  return {
    legalMoveDestsByUnitID,
    legalRetreatDestsByUnitID,
    possibleBuildDests,
    legalViasByUnitID,
    legalConvoysByUnitID,
    hasAnyLegalConvoysByUnitID,
    legalSupportsByUnitID,
  };
}
