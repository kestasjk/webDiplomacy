import { current } from "@reduxjs/toolkit";
import provincesMapData from "../../../../data/map/ProvincesMapData";
import TerritoryMap, {
  webdipNameToTerritory,
} from "../../../../data/map/variants/classic/TerritoryMap";
import BuildUnit from "../../../../enums/BuildUnit";
import Territory from "../../../../enums/map/variants/classic/Territory";
import Province from "../../../../enums/map/variants/classic/Province";
import { ProvinceMapData } from "../../../../interfaces/map/ProvinceMapData";
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
import resetOrder from "../../resetOrder";
import startNewOrder from "../../startNewOrder";
import updateOrder from "../../updateOrder";
import updateOrdersMeta from "../../updateOrdersMeta";
import { LegalOrders } from "../extraReducers/fetchGameData/precomputeLegalOrders";
import { gameApiSliceActions } from "../../../../state/game/game-api-slice";
import { useAppDispatch } from "../../../../state/hooks";
import { setAlert } from "../../../../state/interfaces/GameAlert";

function getClickPositionInProvince(evt, provinceMapData: ProvinceMapData) {
  const boundingRect = evt.target.getBoundingClientRect();
  const diffX = evt.clientX - boundingRect.x;
  const diffY = evt.clientY - boundingRect.y;
  const scaleX = boundingRect.width / provinceMapData.width;
  const scaleY = boundingRect.height / provinceMapData.height;
  return { x: diffX / scaleX, y: diffY / scaleY };
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
  clickProvince: Province;
}

/* eslint-disable no-param-reassign */
export default function processMapClick(
  state: GameState,
  clickData: { payload: MapClickData },
) {
  const currentState: GameState = current(state);
  const {
    data: { data },
    order,
    ordersMeta,
    overview,
    territoriesMeta,
    maps,
    ownUnits,
    legalOrders,
    viewedPhaseState,
    status,
  } = currentState;
  // ---------------------- PREPARATION ---------------------------

  const {
    payload: { evt, clickProvince },
  } = clickData;

  if (!overview.user) {
    invalidClick(evt, clickProvince);
    return;
  }
  const { phase } = overview;
  const { member } = overview.user!;
  const { orderStatus } = member;

  if (phase === "Finished") {
    return;
  }
  if (viewedPhaseState.viewedPhaseIdx < status.phases.length - 1) {
    setAlert(
      state.alert,
      "You need to switch to the current phase to enter orders.",
    );
    invalidClick(evt, clickProvince);
    return;
  }
  if (orderStatus.Ready) {
    setAlert(state.alert, "You need to unready your orders to update them.");
    invalidClick(evt, clickProvince);
    return;
  }
  const clickProvinceMapData = provincesMapData[clickProvince];
  const clickRootTerritory = clickProvinceMapData.rootTerritory;
  const territoryMeta: TerritoryMeta | undefined =
    territoriesMeta[clickProvince];
  // Click is outside the map entirely?
  if (!territoryMeta || !clickRootTerritory) {
    invalidClick(evt, clickProvince);
    return;
  }
  let clickUnitID: string | undefined;
  {
    const clickUnitIDs = maps.provinceToUnits[clickProvince];
    // Handle multiple units on retreat, pick the one that we own if there are multiple
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
      return maps.terrIDToProvince[update.toTerrID] === clickProvince;
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
    const isBuild =
      overview.user.member.supplyCenterNo > overview.user.member.unitNo;
    // Cannot click on other people's units, and on builds you can only
    // click on your own supply centers.
    // FIXME ugh string vs number
    if (member.countryID !== Number(territoryMeta.ownerCountryID)) {
      invalidClick(evt, clickProvince);
      return;
    }

    const { currentOrders } = data;
    const availableOrder = getAvailableOrder(currentOrders, ordersMeta);
    if (!availableOrder) {
      invalidClick(evt, clickProvince);
      return;
    }

    let toTerrID;
    if (isDestroy) {
      if (!clickUnit) {
        invalidClick(evt, clickProvince);
        return;
      }
      // Webdip API expects that destroy actions are in terms of province ID, not territory ID!
      toTerrID = maps.terrIDToProvinceID[clickUnit.terrID];
    } else if (isBuild) {
      // Build on the appropriately clicked coast for STP
      const territory = getBestCoastalUnitTerritory(evt, clickProvinceMapData);
      if (!legalOrders.possibleBuildDests.includes(territory)) {
        invalidClick(evt, clickProvince);
        return;
      }
      toTerrID = maps.territoryToTerrID[territory];
    }

    const isCoast = clickProvinceMapData.type === "Coast";
    resetOrder(state);
    updateOrder(state, {
      inProgress: true,
      orderID: availableOrder,
      // eslint-disable-next-line no-nested-ternary
      type: isDestroy ? "Destroy" : isCoast ? "Build" : "Build Army",
      toTerrID,
    });
    return;
  }

  // ---------------------- RETREAT PHASE ---------------------------
  if (phase === "Retreats") {
    if (!order.inProgress) {
      const isRetreating = data.currentOrders?.find(
        (o) => o.unitID === clickUnitID,
      );
      if (clickUnitID && ownsCurUnit && isRetreating) {
        startNewOrder(state, { unitID: clickUnitID, type: "Retreat" });
      } else {
        invalidClick(evt, clickProvince);
      }
    } else if (clickUnitID === order.unitID) {
      // Clicking again on the same unit itself disbands it.
      updateOrder(state, { type: "Disband" });
    } else {
      const territory =
        orderUnit.type === "Fleet"
          ? getBestCoastalUnitTerritory(evt, clickProvinceMapData)
          : clickRootTerritory;
      if (
        legalOrders.legalRetreatDestsByUnitID[order.unitID].includes(territory)
      ) {
        updateOrder(state, { toTerrID: maps.territoryToTerrID[territory] });
      } else {
        invalidClick(evt, clickProvince);
      }
    }
    return;
  }

  // ---------------------- MOVE PHASE ---------------------------
  if (!order.inProgress) {
    if (clickUnitID && ownsCurUnit) {
      startNewOrder(state, { unitID: clickUnitID });
    } else {
      invalidClick(evt, clickProvince);
    }
  } else if (!order.type || clickUnitID === order.unitID) {
    // Clicking on the same territory a second time cancels the order.
    resetOrder(state);
  } else if (order.type === "Move") {
    const territory =
      orderUnit.type === "Fleet"
        ? getBestCoastalUnitTerritory(evt, clickProvinceMapData)
        : clickRootTerritory;
    // -----------------------------------------------------------

    if (!order.viaConvoy) {
      // direct move
      if (
        legalOrders.legalMoveDestsByUnitID[order.unitID].includes(territory)
      ) {
        updateOrder(state, {
          toTerrID: maps.territoryToTerrID[territory],
        });
      } else {
        invalidClick(evt, clickProvince);
      }
    } else {
      // via convoy
      const via = legalOrders.legalViasByUnitID[order.unitID].find(
        (v) => v.dest === territory,
      );
      if (via) {
        updateOrder(state, {
          toTerrID: maps.territoryToTerrID[territory],
          viaConvoy: "Yes",
          convoyPath: via.provIDPaths[0],
        });
      } else {
        invalidClick(evt, clickProvince);
      }
    }
  } else if (order.type === "Support") {
    if (!order.fromTerrID) {
      // click 1
      if (
        clickUnitID &&
        legalOrders.legalSupportsByUnitID[order.unitID][clickProvince]?.length >
          0
      ) {
        updateOrder(state, {
          fromTerrID: maps.territoryToTerrID[clickRootTerritory],
        });
      } else {
        // gotta support a unit
        invalidClick(evt, clickProvince);
      }
    } else {
      // click 2
      // eslint-disable-next-line no-lonely-if
      const fromProvince = maps.terrIDToProvince[order.fromTerrID];
      const foundSupport = legalOrders.legalSupportsByUnitID[order.unitID][
        fromProvince
      ].find((support) => support.dest === clickProvince);
      if (foundSupport) {
        updateOrder(state, {
          toTerrID: maps.territoryToTerrID[clickRootTerritory],
          convoyPath: foundSupport.convoyProvIDPath,
        });
      } else {
        invalidClick(evt, clickProvince);
      }
    }
  } else if (order.type === "Convoy") {
    if (!order.fromTerrID) {
      // click 1
      // gotta click on an Army that is convoyable
      if (
        clickUnit?.type === "Army" &&
        legalOrders.legalConvoysByUnitID[order.unitID][clickProvince]
      ) {
        updateOrder(state, {
          fromTerrID: maps.territoryToTerrID[clickRootTerritory],
        });
      } else {
        // gotta support a unit
        invalidClick(evt, clickProvince);
      }
    } else {
      // click 2
      const fromProvince = maps.terrIDToProvince[order.fromTerrID];
      const convoy =
        legalOrders.legalConvoysByUnitID[order.unitID][fromProvince][
          clickProvince
        ];
      if (convoy) {
        updateOrder(state, {
          toTerrID: maps.territoryToTerrID[clickRootTerritory],
          convoyPath: [...convoy.provIDPath1, ...convoy.provIDPath2],
        });
      }
    }
  }
}
