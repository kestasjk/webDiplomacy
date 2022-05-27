import { current } from "@reduxjs/toolkit";
import provincesMapData from "../../../../data/map/ProvincesMapData";
import TerritoryMap, {
  webdipNameToTerritory,
} from "../../../../data/map/variants/classic/TerritoryMap";
import BuildUnit from "../../../../enums/BuildUnit";
import Territory from "../../../../enums/map/variants/classic/Territory";
import { ProvinceMapData } from "../../../../interfaces/map/ProvinceMapData";
import BoardClass from "../../../../models/BoardClass";
import GameDataResponse from "../../../../state/interfaces/GameDataResponse";
import { GameState } from "../../../../state/interfaces/GameState";
import GameStateMaps from "../../../../state/interfaces/GameStateMaps";
import OrderState from "../../../../state/interfaces/OrderState";
import OrdersMeta, {
  OrderMeta,
} from "../../../../state/interfaces/SavedOrders";
import { TerritoryMeta } from "../../../../state/interfaces/TerritoriesState";
import { getTargetXYWH } from "../../../map/drawArrowFunctional";
import invalidClick from "../../../map/invalidClick";
import getAvailableOrder from "../../getAvailableOrder";
import getOrderStates from "../../getOrderStates";
import processConvoy from "../../processConvoy";
import resetOrder from "../../resetOrder";
import startNewOrder from "../../startNewOrder";
import updateOrder from "../../updateOrder";
import updateOrdersMeta from "../../updateOrdersMeta";

function canUnitMoveTo(orderMeta: OrderMeta, territory: Territory): boolean {
  const { allowedBorderCrossings } = orderMeta;
  return !!allowedBorderCrossings?.find(
    (border) => TerritoryMap[border.name].territory === territory,
  );
}

function canUnitMoveToProvince(
  orderMeta: OrderMeta,
  territory: Territory,
): boolean {
  const { allowedBorderCrossings } = orderMeta;
  return !!allowedBorderCrossings?.find(
    (border) =>
      TerritoryMap[border.name].territory === territory ||
      TerritoryMap[border.name].parent === territory,
  );
}

function canSupporteeMoveToOrHoldAtProvince(
  order: OrderState,
  territory: Territory,
  maps: GameStateMaps,
  board: BoardClass,
): boolean {
  if (!order.fromTerrID) {
    return false;
  }
  const supporteeTerr: Territory = maps.terrIDToTerritory[order.fromTerrID];
  const supporteeRegion: Territory =
    TerritoryMap[supporteeTerr].parent || supporteeTerr;
  // Make sure you can find the unit
  const supporteeUnit = maps.provinceIDToUnit[order.fromTerrID];
  if (!supporteeUnit) {
    return false;
  }
  // Make sure the board has the unit
  const supporteeUnitClass = board.findUnitByID(supporteeUnit);
  if (!supporteeUnitClass) {
    return false;
  }

  // Make sure that either the region is where the supportee already is
  // or the region is one where the supportee can move to a territory
  // of that region.
  return (
    territory === supporteeRegion ||
    !!board
      .getMovableTerritories(supporteeUnitClass)
      .find(
        (border) =>
          TerritoryMap[border.name].territory === territory ||
          TerritoryMap[border.name].parent === territory,
      )
  );
}

function getClickPositionInProvince(evt, provinceMapData: ProvinceMapData) {
  const boundingRect = evt.target.getBoundingClientRect();
  const diffX = evt.clientX - boundingRect.x;
  const diffY = evt.clientY - boundingRect.y;
  const scaleX = boundingRect.width / provinceMapData.width;
  const scaleY = boundingRect.height / provinceMapData.height;
  return { x: diffX / scaleX, y: diffY / scaleY };
}

function canSupportTerritory(
  orderMeta: OrderMeta,
  territory: Territory,
): boolean {
  const { supportHoldChoices, supportMoveChoices } = orderMeta;
  // console.log({ supportHoldChoices, supportMoveChoices, territory });
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

// Returns either "nc" or "sc", the one closest to the position of the click.
function getBestCoastalUnitSlot(evt, provinceMapData: ProvinceMapData): string {
  const clickPos = getClickPositionInProvince(evt, provinceMapData);

  // here we've got name => {x, y}
  let bestSlot = "";
  let bestDist2 = 1e100;
  provinceMapData!.labels!.forEach((label) => {
    if (["nc", "sc"].includes(label.name)) {
      const dist2 = (clickPos.x - label.x) ** 2 + (clickPos.y - label.y) ** 2;
      if (dist2 < bestDist2) {
        bestSlot = label.name;
        bestDist2 = dist2;
      }
    }
  });
  return bestSlot;
}

interface MapClickData {
  evt: React.MouseEvent<SVGGElement, MouseEvent>;
  territory: Territory;
}

/* eslint-disable no-param-reassign */
export default function processMapClick(
  state: GameState,
  clickData: { payload: MapClickData },
) {
  const {
    board,
    data: { data },
    order,
    ordersMeta,
    overview,
    territoriesMeta,
    maps,
    ownUnits,
  }: {
    board: GameState["board"];
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
    payload: { evt, territory },
  } = clickData;

  if (orderStatus.Ready) {
    alert("You need to unready your orders to update them"); // FIXME: move to alerts modal!
    invalidClick(evt, territory);
    return; // FIXME this is very confusing for the user!
  }
  const territoryMeta: TerritoryMeta | undefined = territoriesMeta[territory];
  // Click is outside the map entirely?
  if (!territoryMeta) {
    invalidClick(evt, territory);
    return;
  }

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
  const ownsCurUnit = ownUnits.includes(clickUnitID);
  const mapData = provincesMapData[territory];

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
      let clickTerrIDCQ = clickTerrID;
      let territoryCQ = territory;
      if (orderUnit.type === "Fleet" && territoryMeta.coastChildIDs) {
        // have to figure out which coast, oy.
        const bestSlot = getBestCoastalUnitSlot(evt, mapData);
        territoryMeta.coastChildIDs.forEach((childID) => {
          const mTerr = TerritoryMap[maps.terrIDToTerritory[childID]];
          if (mTerr.unitSlotName === bestSlot) {
            territoryCQ = mTerr.territory;
            clickTerrIDCQ = maps.territoryToTerrID[territoryCQ];
          }
        });
      }
      // n.b. this already handes standoffs etc.
      const canMove = canUnitMoveTo(ordersMeta[order.orderID], territoryCQ);
      if (canMove) {
        updateOrder(state, { toTerrID: clickTerrIDCQ });
      } else {
        invalidClick(evt, territoryCQ);
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
    //------------------------------------------------------------
    // tricky: get coast-qualified versions if the dest is a coast
    // FIXME: there should be a single object that has all the data for this
    // -----------------------------------------------------------
    let clickTerrIDCQ = clickTerrID;
    let territoryCQ = territory;
    if (orderUnit.type === "Fleet" && territoryMeta.coastChildIDs) {
      // have to figure out which coast, oy.
      const bestSlot = getBestCoastalUnitSlot(evt, mapData);
      territoryMeta.coastChildIDs.forEach((childID) => {
        const mTerr = TerritoryMap[maps.terrIDToTerritory[childID]];
        if (mTerr.unitSlotName === bestSlot) {
          territoryCQ = mTerr.territory;
          clickTerrIDCQ = maps.territoryToTerrID[territoryCQ];
        }
      });
    }
    // -----------------------------------------------------------

    if (!order.viaConvoy) {
      // direct move
      const canMove = canUnitMoveTo(ordersMeta[order.orderID], territoryCQ);
      if (canMove) {
        updateOrder(state, {
          toTerrID: clickTerrIDCQ,
        });
      } else {
        invalidClick(evt, territory);
      }
    } else {
      // via convoy
      const { convoyToChoices } = ordersMeta[order.orderID];
      const canConvoy = !!convoyToChoices?.find(
        (terrID) => maps.terrIDToTerritory[terrID] === territoryCQ,
      );
      if (canConvoy) {
        updateOrder(state, {
          toTerrID: clickTerrIDCQ,
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
        clickUnitID &&
        canSupportTerritory(ordersMeta[order.orderID], territory)
      ) {
        updateOrder(state, { fromTerrID: clickTerrID });
      }
      // gotta support a unit
      invalidClick(evt, territory);
    } else {
      // click 2
      // eslint-disable-next-line no-lonely-if
      if (
        canUnitMoveToProvince(ordersMeta[order.orderID], territory) &&
        board &&
        canSupporteeMoveToOrHoldAtProvince(order, territory, maps, board)
      ) {
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
