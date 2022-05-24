import { current } from "@reduxjs/toolkit";
import UIState from "../../enums/UIState";
import getDataForOrder from "./getDataForOrder";

/* eslint-disable no-param-reassign */
export default function startNewOrder(state, partialOrderState): string {
  console.log("startNewOrder");
  const {
    order: { unitID: prevUnitID, type },
  } = current(state);
  if (prevUnitID && type !== "hold" && type !== "disband") {
    state.unitState[prevUnitID] = UIState.NONE;
  }
  delete state.order.type;
  const orderData = getDataForOrder(state, partialOrderState);
  state.order = orderData;
  state.order.inProgress = true;
  return orderData.orderID;
}
