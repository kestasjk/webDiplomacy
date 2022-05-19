import { current } from "@reduxjs/toolkit";
import UIState from "../../enums/UIState";

/* eslint-disable no-param-reassign */
export default function resetOrder(state): void {
  const {
    order: { unitID, type },
    overview: { phase },
  } = current(state);
  if (type !== "hold" && type !== "retreat") {
    state.unitState[unitID] =
      phase === "Retreats" ? UIState.DISLODGED : UIState.NONE;
  }
  if (type === "disband") {
    state.unitState[unitID] = UIState.DISBANDED;
  }
  state.order.inProgress = false;
  state.order.unitID = "";
  state.order.orderID = "";
  state.order.onTerritory = 0;
  state.order.toTerritory = 0;
  state.order.subsequentClicks = [];
  state.buildPopover = [];
  delete state.order.type;
}
