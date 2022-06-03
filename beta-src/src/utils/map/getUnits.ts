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
  IOrderData,
  IOrderDataHistorical,
  IProvinceStatus,
  IUnit,
  IUnitHistorical,
} from "../../models/Interfaces";
import UIState from "../../enums/UIState";
import OrdersMeta, { OrderMeta } from "../../state/interfaces/SavedOrders";
import { MemberData } from "../../interfaces/state/MemberData";
import getAvailableOrder from "../state/getAvailableOrder";
import GameStateMaps from "../../state/interfaces/GameStateMaps";

export enum UnitDrawMode {
  NONE = "none",
  HOLD = "hold",
  DISBANDED = "disbanded",
  DISLODGED = "dislodged",
  DISLODGING = "dislodging",
  BUILD = "build",
}

// What we manually construct to unify historical and live units and
// pass down to deeper state for rendering the UI
export interface Unit {
  country: Country;
  mappedTerritory: MTerritory;
  unit: IUnit;
  drawMode: UnitDrawMode;
  movedFromTerrID: string | null;
}

export function getUnitsLive(
  territories: APITerritories,
  territoryStatuses: IProvinceStatus[],
  units: { [key: string]: IUnit },
  members: GameOverviewResponse["members"],
  prevPhaseOrders: IOrderDataHistorical[],
  ordersMeta: OrdersMeta,
  currentOrders: IOrderData[],
  currentUser: { member: MemberData },
  phase: string,
  maps: GameStateMaps,
): Unit[] {
  // Accumulate all the units we want to draw into here
  const unitsToDraw: Unit[] = [];

  //--------------------------------------------------------------------
  // Precompute a bunch of useful mappings
  //--------------------------------------------------------------------

  const territoryStatusesByProvID = Object.fromEntries(
    territoryStatuses.map((territoryStatus) => [
      territoryStatus.id,
      territoryStatus,
    ]),
  );
  const unitCountByProvID: { [key: string]: number } = {};
  Object.values(units).forEach((unit) => {
    const provID = maps.terrIDToProvinceID[unit.terrID];
    if (provID in unitCountByProvID) {
      unitCountByProvID[provID] += 1;
    } else {
      unitCountByProvID[provID] = 1;
    }
  });

  // Maps current terrID => previous terrID for units that successfully moved last phase.
  const successfulPrevMoves: { [key: string]: string } = {};
  prevPhaseOrders.forEach((prevOrder) => {
    if (prevOrder.success && prevOrder.type === "Move") {
      successfulPrevMoves[prevOrder.toTerrID.toString()] =
        prevOrder.terrID.toString();
    }
  });

  const ordersMetaByTerrID: { [key: string]: OrderMeta } = {};
  Object.entries(ordersMeta).forEach(([orderID, orderMeta]) => {
    // FIXME having to chain lookups like this here and in many other places in the
    // code is horrible. Maybe we can improve this by just making things like OrdersMeta
    // have *all* of the data on them, rather than just a subset.
    // Or making the webdip API be more helpful and add more convenience fields to stuff.
    const currentOrder = currentOrders.find((order) => order.id === orderID);
    // console.log({ orderID, orderMeta, currentOrder, units });

    if (currentOrder) {
      // for normal orders it's the first case, for builds/destroys the second
      const terrID =
        units[currentOrder.unitID]?.terrID || orderMeta.update?.toTerrID;
      if (terrID) {
        ordersMetaByTerrID[terrID] = orderMeta;
      }
    }
  });

  const excessUnitsBeyondSCs =
    currentUser.member.unitNo - currentUser.member.supplyCenterNo;
  const isDestroyPhase = phase === "Builds" && excessUnitsBeyondSCs > 0;
  const allDestroysAssigned =
    !isDestroyPhase || !getAvailableOrder(currentOrders, ordersMeta);

  // console.log({ prevPhaseOrders });
  // console.log({ successfulMoves });
  // console.log({ ordersMetaByTerrID });
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

          const unitProvID = maps.terrIDToProvinceID[unit.terrID];
          let drawMode = UnitDrawMode.NONE;
          const isRetreat =
            territoryStatusesByProvID[unitProvID] &&
            territoryStatusesByProvID[unitProvID].unitID !== null &&
            territoryStatusesByProvID[unitProvID].unitID !== unit.id;

          if (ordersMetaByTerrID[unit.terrID]?.update?.type === "Hold") {
            drawMode = UnitDrawMode.HOLD;
          } else if (
            isRetreat &&
            // Webdip API might specify disbands in terms of province ID.
            // So also check the province ID, i.e. the ID of the root territory.
            (ordersMetaByTerrID[unit.terrID]?.update?.type === "Disband" ||
              ordersMetaByTerrID[unitProvID]?.update?.type === "Disband")
          ) {
            drawMode = UnitDrawMode.DISBANDED;
          } else if (
            // Webdip API specifies destroys in terms of province ID!!
            // So also check the province ID, i.e. the ID of the root territory.
            ordersMetaByTerrID[unit.terrID]?.update?.type === "Destroy" ||
            ordersMetaByTerrID[unitProvID]?.update?.type === "Destroy"
          ) {
            drawMode = UnitDrawMode.DISBANDED;
          } else if (isRetreat) {
            drawMode = UnitDrawMode.DISLODGED;
          } else if (unitCountByProvID[unitProvID] >= 2) {
            drawMode = UnitDrawMode.DISLODGING;
          } else if (
            unit.countryID === currentUser.member.countryID.toString() &&
            isDestroyPhase &&
            !allDestroysAssigned
          ) {
            // while the user hasn't inputted enough destroy orders,
            // mark all units according to the little red striping for dislodged
            drawMode = UnitDrawMode.DISLODGED;
          }

          const movedFromTerrID =
            unit.terrID in successfulPrevMoves
              ? successfulPrevMoves[unit.terrID]
              : null;

          unitsToDraw.push({
            country: countryMap[country],
            mappedTerritory,
            unit,
            drawMode,
            movedFromTerrID,
          });
        }
      }
    }
  });

  //--------------------------------------------------------------------
  // Compute all the additional units to draw from the current orders
  // Namely, the builds.
  //--------------------------------------------------------------------
  Object.entries(ordersMeta).forEach(([orderID, { update }], index) => {
    if (
      !update ||
      !update.type ||
      !update.type.startsWith("Build ") ||
      update.toTerrID === null
    ) {
      return;
    }
    // FIXME hack to handle the fact that right now ordersmeta is not
    // cleaned out between phases, we have to make sure that the order
    // actually exists
    const currentOrder = currentOrders.find((order) => order.id === orderID);
    if (!currentOrder) {
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
            drawMode: UnitDrawMode.BUILD,
            movedFromTerrID: null,
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
  maps: GameStateMaps,
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
  const successfulPrevMoves: { [key: string]: string } = {};
  prevPhaseOrders.forEach((prevOrder) => {
    if (prevOrder.success && prevOrder.type === "Move") {
      successfulPrevMoves[prevOrder.toTerrID.toString()] =
        prevOrder.terrID.toString();
    }
  });

  const curPhaseOrdersByTerrID: { [key: string]: IOrderDataHistorical } = {};
  curPhaseOrders.forEach((order) => {
    curPhaseOrdersByTerrID[order.terrID] = order;
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

          const unitProvID = maps.terrIDToProvinceID[unit.terrID];

          let drawMode = UnitDrawMode.NONE;
          if (curPhaseOrdersByTerrID[unit.terrID]?.type === "Hold") {
            drawMode = UnitDrawMode.HOLD;
          } else if (
            unit.retreating === "Yes" &&
            // Webdip API might specify disbands in terms of province ID.
            // So also check the province ID, i.e. the ID of the root territory.
            (curPhaseOrdersByTerrID[unit.terrID]?.type === "Disband" ||
              curPhaseOrdersByTerrID[unitProvID]?.type === "Disband")
          ) {
            drawMode = UnitDrawMode.DISBANDED;
          } else if (
            // Webdip API specifies destroys in terms of province ID!!
            // So also check the province ID, i.e. the ID of the root territory.
            curPhaseOrdersByTerrID[unit.terrID]?.type === "Destroy" ||
            curPhaseOrdersByTerrID[unitProvID]?.type === "Destroy"
          ) {
            drawMode = UnitDrawMode.DISBANDED;
          } else if (unit.retreating === "Yes") {
            drawMode = UnitDrawMode.DISLODGED;
          } else if (unitCountByTerrID[unit.terrID] >= 2) {
            drawMode = UnitDrawMode.DISLODGING;
          }

          const movedFromTerrID =
            unit.terrID in successfulPrevMoves
              ? successfulPrevMoves[unit.terrID]
              : null;

          unitsToDraw.push({
            country: countryMap[country],
            mappedTerritory,
            unit: iUnit,
            drawMode,
            movedFromTerrID,
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
            drawMode: UnitDrawMode.BUILD,
            movedFromTerrID: null,
          });
        }
      }
    }
  });

  return unitsToDraw;
}
