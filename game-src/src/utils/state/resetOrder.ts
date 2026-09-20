import { current } from "@reduxjs/toolkit";
import UIState from "../../enums/UIState";

/* eslint-disable no-param-reassign */
export default function resetOrder(state): void {
  state.order.inProgress = false;
  state.order.unitID = "";
  state.order.orderID = "";
  state.order.fromTerrID = "";
  state.order.toTerrID = "";
  state.order.subsequentClicks = [];
  state.buildPopover = [];
  delete state.order.type;
  // console.log("RESET");
  // console.log(state);
}
