import { current } from "@reduxjs/toolkit";
import UIState from "../../enums/UIState";
import NewOrderPayload from "../../interfaces/state/NewOrderPayload";
import getDataForOrder from "./getDataForOrder";

/* eslint-disable no-param-reassign */
export default function startNewOrder(state, action: NewOrderPayload): void {
  console.log("startNewOrder");
  const {
    order: { unitID: prevUnitID, type },
  } = current(state);
  if (prevUnitID && type !== "hold" && type !== "disband") {
    state.unitState[prevUnitID] = UIState.NONE;
  }
  delete state.order.type;
  const orderData = getDataForOrder(state, action.payload);
  state.order = orderData;
  const { unitID } = orderData;
  // really the state should probably come directly from the order object
  if (unitID) {
    console.log(`Set ${unitID} to SELECTED`);
    state.unitState[unitID] = UIState.SELECTED;
  }
}
