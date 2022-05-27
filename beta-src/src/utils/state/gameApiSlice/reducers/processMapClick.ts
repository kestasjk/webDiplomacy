import { current } from "@reduxjs/toolkit";
import provincesMapData from "../../../../data/map/ProvincesMapData";
import TerritoryMap, {
  webdipNameToTerritory,
} from "../../../../data/map/variants/classic/TerritoryMap";
import BuildUnit from "../../../../enums/BuildUnit";
import Territory from "../../../../enums/map/variants/classic/Territory";
import Province from "../../../../enums/map/variants/classic/Province";
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
  provinceMapData: ProvinceMapData,
): boolean {
  const { allowedBorderCrossings } = orderMeta;
  return !!allowedBorderCrossings?.find(
    (border) => TerritoryMap[border.name].province === provinceMapData.province,
  );
}

function canSupporteeMoveToOrHoldAtProvince(
  order: OrderState,
  provinceMapData: ProvinceMapData,
  maps: GameStateMaps,
  board: BoardClass,
): boolean {
  if (!order.fromTerrID) {
    return false;
  }
  const supporteeTerr: Territory = maps.terrIDToTerritory[order.fromTerrID];
  const supporteeProvince: Province = TerritoryMap[supporteeTerr].province;
  // Make sure you can find the unit
  const supporteeUnits = maps.provinceIDToUnits[order.fromTerrID];
  if (!supporteeUnits) {
    return false;
  }
  // Make sure the board has the unit
  const supporteeUnitClass = board.findUnitByID(supporteeUnits[0]);
  if (!supporteeUnitClass) {
    return false;
  }

  // Make sure that either the province is where the supportee already is
  // or the province is one where the supportee can move to a territory
  // of that province.
  return (
    provinceMapData.province === supporteeProvince ||
    !!board
      .getMovableTerritories(supporteeUnitClass)
      .find(
        (border) =>
          TerritoryMap[border.name].province === provinceMapData.province,
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

function canSupportProvince(
  orderMeta: OrderMeta,
  provinceMapData: ProvinceMapData,
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
  return (
    provinceMapData.rootTerritory !== null &&
    all.includes(provinceMapData.rootTerritory)
  );
}

// If the province has special coasts, returns special coastal territory closest
// to the position of the click. Else just returns the single territory corresponding
// to the province.
function getBestCoastalUnitTerritory(
  evt,
  provinceMapData: ProvinceMapData,
): Territory {
  if (provinceMapData.unitSlots.length === 1) {
    return provinceMapData.unitSlots[0].territory;
  }
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
  return provinceMapData.unitSlotsBySlotName[bestSlot].territory;
}

interface MapClickData {
  evt: React.MouseEvent<SVGGElement, MouseEvent>;
  province: Province;
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
    payload: { evt, province },
  } = clickData;

  if (orderStatus.Ready) {
    alert("You need to unready your orders to update them"); // FIXME: move to alerts modal!
    invalidClick(evt, province);
    return; // FIXME this is very confusing for the user!
  }
  const provinceMapData = provincesMapData[province];
  const { rootTerritory } = provinceMapData;
  const territoryMeta: TerritoryMeta | undefined = territoriesMeta[province];
  // Click is outside the map entirely?
  if (!territoryMeta || !rootTerritory) {
    invalidClick(evt, province);
    return;
  }

  const clickTerrID = maps.territoryToTerrID[province];
  let clickUnitID: string | undefined;
  {
    const clickUnitIDs = maps.provinceToUnits[province];
    // Handle multiple units on retreat
    if (clickUnitIDs && clickUnitIDs.length >= 2) {
      clickUnitID = clickUnitIDs.find((unitID) => ownUnits.includes(unitID));
    } else if (clickUnitIDs && clickUnitIDs.length === 1) {
      [clickUnitID] = clickUnitIDs;
    }
  }

  const clickUnit = clickUnitID ? data.units[clickUnitID] : undefined;
  const orderUnit = data.units[order.unitID];
  const ownsCurUnit = clickUnitID && ownUnits.includes(clickUnitID);

  // ---------------------- BUILD PHASE ---------------------------
  if (phase === "Builds") {
    // FIXME: abstract to a function
    const existingOrder = Object.entries(ordersMeta).find(([, { update }]) => {
      if (!update || !update.toTerrID) return false;
      return maps.terrIDToProvince[update.toTerrID] === province;
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
      invalidClick(evt, province);
      return;
    }

    const { currentOrders } = data;
    const availableOrder = getAvailableOrder(currentOrders, ordersMeta);
    const territoryHasUnit = !!territoryMeta.unitID;
    const unitValid = isDestroy === territoryHasUnit;
    if (!availableOrder || !unitValid) {
      invalidClick(evt, province);
      return;
    }

    // Build on the appropriately clicked coast for STP
    let toTerrID = clickTerrID;
    if (!isDestroy) {
      const territory = getBestCoastalUnitTerritory(evt, provinceMapData);
      toTerrID = maps.territoryToTerrID[territory];
    }

    resetOrder(state);
    updateOrder(state, {
      inProgress: true,
      orderID: availableOrder,
      type: isDestroy ? "Destroy" : "Build",
      toTerrID,
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
        invalidClick(evt, province);
      }
    } else if (clickUnitID === order.unitID) {
      updateOrder(state, { type: "Disband" });
    } else {
      let clickTerrIDCQ = clickTerrID;
      let territoryCQ = rootTerritory;
      if (orderUnit.type === "Fleet") {
        // have to figure out which coast, oy.
        territoryCQ = getBestCoastalUnitTerritory(evt, provinceMapData);
        clickTerrIDCQ = maps.territoryToTerrID[territoryCQ];
      }
      // n.b. this already handes standoffs etc.
      const canMove = canUnitMoveTo(ordersMeta[order.orderID], territoryCQ);
      if (canMove) {
        updateOrder(state, { toTerrID: clickTerrIDCQ });
      } else {
        invalidClick(evt, province);
      }
    }
    return;
  }

  // ---------------------- MOVE PHASE ---------------------------
  if (!order.inProgress) {
    if (clickUnitID && ownsCurUnit) {
      startNewOrder(state, { unitID: clickUnitID });
    } else {
      invalidClick(evt, province);
    }
  } else if (!order.type || clickUnitID === order.unitID) {
    // cancel the order
    resetOrder(state);
  } else if (order.type === "Move") {
    //------------------------------------------------------------
    // tricky: get coast-qualified versions if the dest is a coast
    // -----------------------------------------------------------
    let clickTerrIDCQ = clickTerrID;
    let territoryCQ = rootTerritory;
    if (orderUnit.type === "Fleet") {
      territoryCQ = getBestCoastalUnitTerritory(evt, provinceMapData);
      clickTerrIDCQ = maps.territoryToTerrID[territoryCQ];
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
        invalidClick(evt, province);
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
          invalidClick(evt, province);
        }
      } else {
        invalidClick(evt, province);
      }
    }
  } else if (order.type === "Support") {
    if (!order.fromTerrID) {
      // click 1
      if (
        clickUnitID &&
        canSupportProvince(ordersMeta[order.orderID], provinceMapData)
      ) {
        updateOrder(state, { fromTerrID: clickTerrID });
      }
      // gotta support a unit
      invalidClick(evt, province);
    } else {
      // click 2
      // eslint-disable-next-line no-lonely-if
      if (
        canUnitMoveToProvince(ordersMeta[order.orderID], provinceMapData) &&
        board &&
        canSupporteeMoveToOrHoldAtProvince(order, provinceMapData, maps, board)
      ) {
        updateOrder(state, { toTerrID: clickTerrID });
      } else {
        invalidClick(evt, province);
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
        invalidClick(evt, province);
      }
    } else {
      // click 2
      updateOrder(state, { toTerrID: clickTerrID });
      if (!processConvoy(state, evt)) {
        invalidClick(evt, province);
      }
    }
  }
}
