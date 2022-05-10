import { current } from "@reduxjs/toolkit";
import highlightMapTerritoriesBasedOnStatuses from "../../../map/highlightMapTerritoriesBasedOnStatuses";
import getOrderStates from "../../getOrderStates";
import resetOrder from "../../resetOrder";
import startNewOrder from "../../startNewOrder";

/* eslint-disable no-param-reassign */
export default function processUnitClick(state, clickData) {
  const {
    data: {
      data: { contextVars, units },
    },
    order: { inProgress, method, onTerritory, orderID, type, unitID },
    ownUnits,
  } = current(state);
  if (contextVars?.context?.orderStatus) {
    const orderStates = getOrderStates(contextVars?.context?.orderStatus);
    if (orderStates.Ready) {
      return;
    }
  }
  if (inProgress) {
    if (unitID === clickData.payload.unitID) {
      resetOrder(state);
    } else if ((type === "hold" || type === "move") && onTerritory !== null) {
      highlightMapTerritoriesBasedOnStatuses(state);
    } else if (method === "dblClick" && unitID !== clickData.payload.unitID) {
      state.order.subsequentClicks.push({
        ...{
          inProgress: true,
          orderID,
          toTerritory: null,
        },
        ...clickData.payload,
      });
    } else if (ownUnits.includes(clickData.payload.unitID)) {
      const currentOrderUnitType = units[unitID].type;
      const newClickUnitType = units[clickData.payload.unitID].type;
      if (currentOrderUnitType === "Army" && newClickUnitType === "Fleet") {
        // this is a convoy move
        state.order.type = "convoy";
        state.order.subsequentClicks.push({
          ...{
            inProgress: true,
            orderID,
            toTerritory: null,
          },
          ...clickData.payload,
        });
      } else {
        startNewOrder(state, clickData);
      }
    }
  } else if (ownUnits.includes(clickData.payload.unitID)) {
    startNewOrder(state, clickData);
  }
}
