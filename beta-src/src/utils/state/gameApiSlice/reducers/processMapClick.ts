import { current } from "@reduxjs/toolkit";
import TerritoryMap, {
  webdipNameToTerritory,
} from "../../../../data/map/variants/classic/TerritoryMap";
import Territories from "../../../../data/Territories";
import BuildUnit from "../../../../enums/BuildUnit";
import Territory from "../../../../enums/map/variants/classic/Territory";
import GameDataResponse from "../../../../state/interfaces/GameDataResponse";
import { GameState } from "../../../../state/interfaces/GameState";
import GameStateMaps from "../../../../state/interfaces/GameStateMaps";
import { OrderMeta } from "../../../../state/interfaces/SavedOrders";
import { TerritoryMeta } from "../../../../state/interfaces/TerritoriesState";
import invalidClick from "../../../map/invalidClick";
import getAvailableOrder from "../../getAvailableOrder";
import getOrderStates from "../../getOrderStates";
import processConvoy from "../../processConvoy";
import resetOrder from "../../resetOrder";
import startNewOrder from "../../startNewOrder";
import updateOrder from "../../updateOrder";
import updateOrdersMeta from "../../updateOrdersMeta";

function canUnitMove(orderMeta: OrderMeta, territory: Territory): boolean {
  const { allowedBorderCrossings } = orderMeta;
  return !!allowedBorderCrossings?.find(
    (border) => TerritoryMap[border.name].territory === territory,
  );
}

function canSupportTerritory(
  orderMeta: OrderMeta,
  territory: Territory,
): boolean {
  const { supportHoldChoices, supportMoveChoices } = orderMeta;
  console.log({ supportHoldChoices, supportMoveChoices });
  const all: Territory[] = [];
  supportHoldChoices?.forEach((t) => {
    all.push(webdipNameToTerritory[t.name]);
  });
  supportMoveChoices?.forEach((x) => {
    x.supportMoveFrom.forEach((t) => {
      all.push(webdipNameToTerritory[t.name]);
    });
  });
  return all.includes(territory);
}

/* eslint-disable no-param-reassign */
export default function processMapClick(state, clickData) {
  const {
    data: { data },
    order,
    ordersMeta,
    overview,
    territoriesMeta,
    maps,
    ownUnits,
  }: {
    data: { data: GameDataResponse["data"] };
    order: GameState["order"];
    ordersMeta: GameState["ordersMeta"];
    overview: GameState["overview"];
    territoriesMeta: GameState["territoriesMeta"];
    maps: GameStateMaps;
    ownUnits: GameState["ownUnits"];
  } = current(state);
  // ---------------------- PREPARATION ---------------------------

  console.log("processMapClick");
  const {
    user: { member },
    phase,
  } = overview;
  const { orderStatus } = member;

  const {
    payload: { clickObject, evt, territory },
  } = clickData;

  if (orderStatus.Ready) {
    alert("You need to unready your orders to update them"); // FIXME: move to alerts modal!
    invalidClick(evt, territory);
    return; // FIXME this is very confusing for the user!
  }
  const territoryMeta: TerritoryMeta = territoriesMeta[territory];

  const clickTerrID = maps.territoryToTerrID[territory];
  let clickUnitID = maps.terrIDToUnit[clickTerrID];
  // Fixup unit for coast!
  if (!clickUnitID) {
    // Note: I think we could use the TerritoryClass stuff to find children like this
    territoryMeta.coastChildIDs.forEach((childID) => {
      const coastUnitID = maps.terrIDToUnit[childID];
      if (
        coastUnitID &&
        (phase !== "Retreats" || ownUnits.includes(coastUnitID)) // on retreats there may be 2 units
      ) {
        clickUnitID = coastUnitID;
      }
    });
  }
  const clickUnit = data.units[clickUnitID];
  const orderUnit = data.units[order.unitID];
  const orderUnitTerrID = maps.unitToTerrID[order.unitID];
  const ownsCurUnit = ownUnits.includes(clickUnitID);

  // ---------------------- BUILD PHASE ---------------------------
  if (phase === "Builds") {
    // FIXME: abstract to a function
    const existingOrder = Object.entries(ordersMeta).find(([, { update }]) => {
      if (!update || !update.toTerrID) return false;
      if (update.toTerrID === clickTerrID) return true;
      const updateTerr = maps.terrIDToTerritory[update.toTerrID];
      const { parent } = TerritoryMap[updateTerr];
      const parentID = parent && maps.territoryToTerrID[parent];
      return parentID === clickTerrID;
    });
    if (existingOrder) {
      const [id] = existingOrder;

      updateOrdersMeta(state, {
        [id]: {
          saved: false,
          update: {
            type: "Wait",
            toTerrID: null,
          },
        },
      });
      return;
    }

    const isDestroy =
      overview.user.member.supplyCenterNo < overview.user.member.unitNo;
    console.log({ isDestroy });
    if (
      member.countryID !== Number(territoryMeta.ownerCountryID) || // FIXME ugh string vs number
      (!territoryMeta.supply && !isDestroy)
    ) {
      invalidClick(evt, territory);
      return;
    }

    const { currentOrders } = data;
    const availableOrder = getAvailableOrder(currentOrders, ordersMeta);
    const territoryHasUnit = !!territoryMeta.unitID;
    const unitValid = isDestroy === territoryHasUnit;
    console.log({ availableOrder, unitValid });
    if (!availableOrder || !unitValid) {
      invalidClick(evt, territory);
      return;
    }
    resetOrder(state);
    updateOrder(state, {
      inProgress: true,
      orderID: availableOrder,
      type: isDestroy ? "Destroy" : "Build",
      toTerrID: clickTerrID,
    });
    return;
  }

  // ---------------------- RETREAT PHASE ---------------------------
  if (phase === "Retreats") {
    if (!order.inProgress) {
      if (clickUnitID && ownsCurUnit) {
        // && clickUnit?.isRetreating) {
        startNewOrder(state, { unitID: clickUnitID, type: "Retreat" });
      } else {
        invalidClick(evt, territory);
      }
    } else if (clickUnitID === order.unitID) {
      updateOrder(state, { type: "Disband" });
    } else {
      // n.b. this already handes standoffs etc.
      const canMove = canUnitMove(ordersMeta[order.orderID], territory);
      if (canMove) {
        updateOrder(state, { toTerrID: clickTerrID });
      } else {
        invalidClick(evt, territory);
      }
    }
    return;
  }

  // ---------------------- MOVE PHASE ---------------------------
  if (!order.inProgress) {
    if (clickUnitID && ownsCurUnit) {
      startNewOrder(state, { unitID: clickUnitID });
    } else {
      invalidClick(evt, territory);
    }
  } else if (!order.type || clickUnitID === order.unitID) {
    // cancel the order
    resetOrder(state);
  } else if (order.type === "Move") {
    if (!order.viaConvoy) {
      // direct move
      const canMove = canUnitMove(ordersMeta[order.orderID], territory);
      if (canMove) {
        updateOrder(state, {
          toTerrID: clickTerrID,
        });
      } else {
        invalidClick(evt, territory);
      }
    } else {
      // via convoy
      const { convoyToChoices } = ordersMeta[order.orderID];
      const canConvoy = !!convoyToChoices?.find(
        (terrID) => maps.terrIDToTerritory[terrID] === territory,
      );
      console.log({ canConvoy, clickUnit, orderUnit, territory });
      if (canConvoy) {
        updateOrder(state, {
          toTerrID: clickTerrID,
          viaConvoy: "Yes",
        });
        if (!processConvoy(state, evt)) {
          invalidClick(evt, territory);
        }
      } else {
        invalidClick(evt, territory);
      }
    }
  } else if (order.type === "Support") {
    if (!order.fromTerrID) {
      // click 1
      if (
        maps.terrIDToUnit[clickTerrID] &&
        canSupportTerritory(ordersMeta[order.orderID], territory)
      ) {
        updateOrder(state, { fromTerrID: clickTerrID });
      }
      // gotta support a unit
      invalidClick(evt, territory);
    } else {
      // click 2
      // eslint-disable-next-line no-lonely-if
      if (canUnitMove(ordersMeta[order.orderID], territory)) {
        updateOrder(state, { toTerrID: clickTerrID });
      } else {
        invalidClick(evt, territory);
      }
    }
  } else if (order.type === "Convoy") {
    if (!order.fromTerrID) {
      // click 1
      // gotta click on an Army
      if (clickUnit?.type === "Army") {
        updateOrder(state, { fromTerrID: clickTerrID });
      } else {
        // gotta support a unit
        invalidClick(evt, territory);
      }
    } else {
      // click 2
      updateOrder(state, { toTerrID: clickTerrID });
      if (!processConvoy(state, evt)) {
        invalidClick(evt, territory);
      }
    }
  }
}
