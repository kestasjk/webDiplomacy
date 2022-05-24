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
import invalidClick from "../../../map/invalidClick";
import getAvailableOrder from "../../getAvailableOrder";
import getOrderStates from "../../getOrderStates";
import processConvoy from "../../processConvoy";
import resetOrder from "../../resetOrder";
import startNewOrder from "../../startNewOrder";
import updateOrder from "../../updateOrder";
import updateOrdersMeta from "../../updateOrdersMeta";

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
  console.log("processMapClick");
  console.log({ order });
  const {
    user: { member },
    phase,
  } = overview;
  const { orderStatus } = member;

  const {
    payload: { clickObject, evt, territory },
  } = clickData;

  if (orderStatus.Ready) {
    invalidClick(evt, territory);
    return; // FIXME
  }

  const clickTerrID = maps.territoryToTerrID[territory];
  const clickUnitID = maps.terrIDToUnit[clickTerrID];
  const ownsCurUnit = ownUnits.includes(clickUnitID);
  console.log({
    territory,
    clickTerrID,
    unitID: order.unitID,
    clickUnitID,
    map: maps.terrIDToUnit,
  });

  // build phase
  if (phase === "Builds") {
    const territoryMeta = territoriesMeta[Territory[territory]];

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
      console.log("Existing build order");
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
      console.log("Not a SC");
      console.log({ member, territoryMeta, isDestroy });
      invalidClick(evt, territory);
      return;
    }

    const { currentOrders } = data;
    const availableOrder = getAvailableOrder(currentOrders, ordersMeta);
    const territoryHasUnit = !!territoryMeta.unitID;
    const unitValid = isDestroy === territoryHasUnit;
    if (!availableOrder || !unitValid) {
      console.log({
        availableOrder,
        isDestroy,
        territoryHasUnit,
        overview,
        territoryMeta,
      });
      invalidClick(evt, territory);
      return;
    }

    console.log(
      `trying to start build order: ${availableOrder} ${territoryHasUnit} ${order.inProgress}`,
    );
    resetOrder(state);
    updateOrder(state, {
      inProgress: true,
      orderID: availableOrder,
      type: isDestroy ? "Destroy" : "Build",
      toTerrID: clickTerrID,
    });
    return;
  }

  // move phases
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
    const { allowedBorderCrossings } = ordersMeta[order.orderID];
    const canMove = allowedBorderCrossings?.find((border) => {
      const mappedTerritory = TerritoryMap[border.name];
      return Territory[mappedTerritory.territory] === territory;
    });

    console.log({ canMove });
    if (canMove) {
      updateOrder(state, {
        toTerrID: clickTerrID,
      });
    } else {
      invalidClick(evt, territory);
    }
  } else if (order.type === "Support" || order.type === "Convoy") {
    // FIXME: dedup
    const { allowedBorderCrossings } = ordersMeta[order.orderID];
    const canMove = allowedBorderCrossings?.find((border) => {
      const mappedTerritory = TerritoryMap[border.name];
      return Territory[mappedTerritory.territory] === territory;
    });

    if (!order.fromTerrID) {
      // click 1
      if (!maps.terrIDToUnit[clickTerrID]) {
        // gotta support a unit
        invalidClick(evt, territory);
        return;
      }
      updateOrder(state, { fromTerrID: clickTerrID });
    } else {
      // click 2
      // eslint-disable-next-line no-lonely-if
      if (canMove) {
        updateOrder(state, { toTerrID: clickTerrID });
      } else {
        invalidClick(evt, territory);
      }
    }
  }
}

//       },
//     });
//   updateOrder({})
//   // eslint-disable-next-line no-lonely-if
//   if (clickUnitID === order.unitID) {
//     if (currentOrders) {
//       const orderToUpdate = currentOrders.find((o) => o.unitID === unitID);
//       if (orderToUpdate) {
//         updateOrdersMeta(state, {
//           [orderToUpdate.id]: {
//             saved: false,
//             update: {
//               type: phase === "Retreats" ? "Disband" : "Hold",
//               toTerrID: null,
//             },
//           },
//         });
//       }
//     }
//     state.order.type = phase === "Retreats" ? "disband" : "hold";
//     resetOrder(state);
//     return;
//   }
//   const { allowedBorderCrossings } = ordersMeta[orderID];
//   const canMove = allowedBorderCrossings?.find((border) => {
//     const mappedTerritory = TerritoryMap[border.name];
//     return Territory[mappedTerritory.territory] === territory;
//   });
//   console.log({ canMove });
//   if (canMove) {
//     updateOrdersMeta(state, {
//       [orderID]: {
//         saved: false,
//         update: {
//           type: phase === "Retreats" ? "Retreat" : "Move",
//           toTerrID: canMove.id,
//           viaConvoy: "No",
//         },
//       },
//     });
//     state.order.toTerritory = TerritoryMap[canMove.name].territory;
//     state.order.type = phase === "Retreats" ? "retreat" : "move";
//     resetOrder(state);
//   } else {
//     invalidClick(evt, territory);
//   }
// }
// const truthyToTerritory = toTerritory !== undefined && toTerritory !== null;
// const truthyOnTerritory = onTerritory !== undefined && onTerritory !== null;
// console.log({ clickObject, phase, currentOrders });
// if (inProgress) {
//   // && method === "click") {
//   state.buildPopover = [];
//   const currOrderUnitID = unitID;
//   if (truthyOnTerritory && onTerritory === territory && !type) {
//     console.log(`inProgress Click`);

//     state.unitState[currOrderUnitID] = UIState.HOLD;
//     if (currentOrders) {
//       const orderToUpdate = currentOrders.find(
//         (o) => o.unitID === currOrderUnitID,
//       );
//       if (orderToUpdate) {
//         updateOrdersMeta(state, {
//           [orderToUpdate.id]: {
//             saved: false,
//             update: {
//               type: phase === "Retreats" ? "Disband" : "Hold",
//               toTerrID: null,
//             },
//           },
//         });
//       }
//     }
//     state.order.type = phase === "Retreats" ? "disband" : "hold";
//   } else if (type === "convoy" && !truthyToTerritory) {
//     state.order.toTerritory = Number(Territory[territory]);
//     processConvoy(state, evt);
//   } else if (
//     truthyOnTerritory &&
//     (type === "hold" ||
//       type === "move" ||
//       type === "convoy" ||
//       type === "disband" ||
//       type === "retreat" ||
//       type === "build")
//   ) {
//     resetOrder(state);
//   } else if (truthyToTerritory && type === "build") {
//     resetOrder(state);
//   } else if (
//     clickObject === "territory" &&
//     truthyOnTerritory &&
//     onTerritory !== territory &&
//     !type &&
//     inProgress
//   ) {
//     console.log(`inProgress truthyWtf`);
//     const { allowedBorderCrossings } = ordersMeta[orderID];
//     const canMove = allowedBorderCrossings?.find((border) => {
//       const mappedTerritory = TerritoryMap[border.name];
//       return Territory[mappedTerritory.territory] === territory;
//     });
//     if (canMove) {
//       updateOrdersMeta(state, {
//         [orderID]: {
//           saved: false,
//           update: {
//             type: phase === "Retreats" ? "Retreat" : "Move",
//             toTerrID: canMove.id,
//             viaConvoy: "No",
//           },
//         },
//       });
//       state.order.toTerritory = TerritoryMap[canMove.name].territory;
//       state.order.type = phase === "Retreats" ? "retreat" : "move";
//     } else {
//       invalidClick(evt, territory);
//     }
//   }
//   // eslint-disable-next-line no-dupe-else-if
// } else if (inProgress) {
//   // && method === "dblClick") {
//   console.log(`inProgress dblClick`);
//   if (subsequentClicks.length) {
//     // user is trying to do a support move or a support hold
//     const unitSupporting = ordersMeta[orderID];
//     const unitBeingSupported = subsequentClicks[0];
//     if (territory === unitBeingSupported.onTerritory) {
//       // attemping support hold
//       const match = unitSupporting.supportHoldChoices?.find(
//         ({ unitID: uID }) => uID === unitBeingSupported.unitID,
//       );
//       if (match) {
//         // execute support hold
//         updateOrdersMeta(state, {
//           [orderID]: {
//             saved: false,
//             update: {
//               type: "Support hold",
//               toTerrID: match.id,
//             },
//           },
//         });
//         resetOrder(state);
//         return;
//       }
//     } else {
//       // attempting support move
//       const supportMoveMatch = unitSupporting.supportMoveChoices?.find(
//         ({ supportMoveTo }) =>
//           webdipNameToTerritory[supportMoveTo.name] === territory, // FIXME ugly
//       );
//       if (supportMoveMatch && supportMoveMatch.supportMoveFrom.length) {
//         const match = supportMoveMatch.supportMoveFrom.find(
//           ({ unitID: uID }) => uID === unitBeingSupported.unitID,
//         );
//         if (match) {
//           // execute support move
//           updateOrdersMeta(state, {
//             [orderID]: {
//               saved: false,
//               update: {
//                 type: "Support move",
//                 toTerrID: supportMoveMatch.supportMoveTo.id,
//                 fromTerrID: match.id,
//               },
//             },
//           });
//           resetOrder(state);
//           return;
//         }
//       }
//     }
//     invalidClick(evt, territory);
//   }
// } else if (
//   clickObject === "territory" &&
//   phase === "Builds" &&
//   currentOrders
// ) {
//   const territoryMeta = territoriesMeta[Territory[territory]];

//   if (territoryMeta) {
//     const {
//       coast: territoryCoast,
//       countryID,
//       id: webDipTerritoryID,
//       supply,
//       type: territoryType,
//     } = territoryMeta;

//     if (member.countryID.toString() !== countryID || !supply) {
//       return;
//     }

//     const stp = territoriesMeta[Territory.SAINT_PETERSBURG];
//     const stpnc = territoriesMeta[Territory.SAINT_PETERSBURG_NORTH_COAST];
//     const stpsc = territoriesMeta[Territory.SAINT_PETERSBURG_SOUTH_COAST];

//     const specialIds = {};
//     if (stp) {
//       specialIds[stp.id] = [stpnc?.id, stpsc?.id];
//     }

//     const affectedTerritoryIds = specialIds[webDipTerritoryID]
//       ? [...[webDipTerritoryID], ...specialIds[webDipTerritoryID]]
//       : [webDipTerritoryID];

//     const existingBuildOrder = Object.entries(ordersMeta).find(
//       ([, { update }]) =>
//         update ? affectedTerritoryIds.includes(update.toTerrID) : false,
//     );

//     if (existingBuildOrder) {
//       const [id] = existingBuildOrder;

//       updateOrdersMeta(state, {
//         [id]: {
//           saved: false,
//           update: {
//             type: "Wait",
//             toTerrID: null,
//           },
//         },
//       });
//       return;
//     }

//     const territoryHasUnit = !!territoryMeta.unitID;

//     let availableOrder;
//     for (let i = 0; i < currentOrders.length; i += 1) {
//       const { id } = currentOrders[i];
//       const orderMeta = ordersMeta[id];
//       if (!orderMeta.update || !orderMeta.update?.toTerrID) {
//         availableOrder = id;
//         break;
//       }
//     }
//     console.log(
//       `trying to start order: ${availableOrder} ${territoryHasUnit} ${inProgress}`,
//     );
//     if (availableOrder && !territoryHasUnit && !inProgress) {
//       let canBuild = 0;
//       if (territoryCoast === "Parent" || territoryCoast === "No") {
//         canBuild += BuildUnit.Army;
//       }
//       if (territoryType !== "Land" && territoryCoast !== "Parent") {
//         canBuild += BuildUnit.Fleet;
//       }
//       state.buildPopover = [];
//       state.buildPopover.push({
//         availableOrder,
//         canBuild,
//         territoryMeta,
//         unitSlotName: "main",
//       });
//       if (stp && territoryMeta.territory === Territory.SAINT_PETERSBURG) {
//         state.buildPopover.push({
//           availableOrder,
//           canBuild: BuildUnit.Fleet,
//           territoryMeta: stpnc,
//           unitSlotName: "nc",
//         });
//         state.buildPopover.push({
//           availableOrder,
//           canBuild: BuildUnit.Fleet,
//           territoryMeta: stpsc,
//           unitSlotName: "sc",
//         });
//       }

//       startNewOrder(state, {
//         payload: {
//           inProgress: true,
//           method: "click",
//           orderID: availableOrder,
//           onTerritory: null,
//           subsequentClicks: [],
//           toTerritory: territoryMeta.territory,
//           type: "build",
//           unitID: "",
//         },
//       });
//     }
//   }
// }
