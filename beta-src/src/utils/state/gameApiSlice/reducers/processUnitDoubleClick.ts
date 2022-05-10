import { current } from "@reduxjs/toolkit";
import getOrderStates from "../../getOrderStates";
import startNewOrder from "../../startNewOrder";

export default function processUnitDoubleClick(state, clickData): void {
  const {
    data: {
      data: { contextVars },
    },
    ownUnits,
  } = current(state);
  if (!ownUnits.includes(clickData.payload.unitID)) {
    return;
  }
  if (contextVars?.context?.orderStatus) {
    const orderStates = getOrderStates(contextVars?.context?.orderStatus);
    if (orderStates.Ready) {
      return;
    }
  }
  startNewOrder(state, clickData);
}
