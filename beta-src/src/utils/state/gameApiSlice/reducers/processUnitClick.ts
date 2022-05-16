import { current } from "@reduxjs/toolkit";
import GameDataResponse from "../../../../state/interfaces/GameDataResponse";
import GameOverviewResponse from "../../../../state/interfaces/GameOverviewResponse";
import GameStateMaps from "../../../../state/interfaces/GameStateMaps";
import OrderState from "../../../../state/interfaces/OrderState";
import OrdersMeta from "../../../../state/interfaces/SavedOrders";
import highlightMapTerritoriesBasedOnStatuses from "../../../map/highlightMapTerritoriesBasedOnStatuses";
import getAvailableOrder from "../../getAvailableOrder";
import getOrderStates from "../../getOrderStates";
import resetOrder from "../../resetOrder";
import startNewOrder from "../../startNewOrder";
import updateOrdersMeta from "../../updateOrdersMeta";

/* eslint-disable no-param-reassign */
export default function processUnitClick(state, clickData) {
  const {
    data,
    maps,
    order,
    ordersMeta,
    ownUnits,
    overview,
  }: {
    data: GameDataResponse;
    maps: GameStateMaps;
    order: OrderState;
    ordersMeta: OrdersMeta;
    ownUnits: string[];
    overview: GameOverviewResponse;
  } = current(state);
  const {
    data: { currentOrders, contextVars, units },
  } = data;
  const { inProgress, method, onTerritory, orderID, type, unitID } = order;
  const { phase } = overview;
  if (contextVars?.context?.orderStatus) {
    const orderStates = getOrderStates(contextVars?.context?.orderStatus);
    if (orderStates.Ready) {
      return;
    }
  }
  // Destroy Units
  const isDestroy = currentOrders?.some(({ type: t }) => t === "Destroy");
  if (phase === "Builds") {
    if (!ownUnits.includes(clickData.payload.unitID) || !isDestroy) {
      return;
    }

    const existingOrder = Object.entries(ordersMeta).find(([, { update }]) => {
      return (
        update?.toTerrID === maps.unitToTerritory[clickData.payload.unitID]
      );
    });

    if (existingOrder) {
      const [eOrderID, eOrder] = existingOrder;
      if (eOrderID && eOrder) {
        updateOrdersMeta(state, {
          [eOrderID]: {
            saved: false,
            update: {
              type: "Destroy",
              toTerrID: null,
            },
          },
        });
      }
    } else {
      const availableOrder = getAvailableOrder(currentOrders, ordersMeta);
      if (availableOrder) {
        updateOrdersMeta(state, {
          [availableOrder]: {
            saved: false,
            update: {
              type: "Destroy",
              toTerrID: maps.unitToTerritory[clickData.payload.unitID],
            },
          },
        });
      }
    }
  } else if (inProgress) {
    if (unitID === clickData.payload.unitID) {
      resetOrder(state);
      if (type === "disband" || type === "retreat") {
        highlightMapTerritoriesBasedOnStatuses(state);
      }
    } else if ((type === "hold" || type === "move") && onTerritory !== null) {
      highlightMapTerritoriesBasedOnStatuses(state);
    } else if (method === "dblClick" && unitID !== clickData.payload.unitID) {
      state.order.subsequentClicks.push({
        ...{
          inProgress: true,
          order: orderID,
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
            order: orderID,
            toTerritory: null,
          },
          ...clickData.payload,
        });
        highlightMapTerritoriesBasedOnStatuses(state);
      } else {
        startNewOrder(state, clickData);
      }
    }
  } else if (ownUnits.includes(clickData.payload.unitID)) {
    startNewOrder(state, clickData);
  }
}
