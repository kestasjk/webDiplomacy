import { current } from "@reduxjs/toolkit";
import { EditOrder, OrderMetaUpdate } from "../../state/interfaces/SavedOrders";
import commitOrder from "./commitOrder";
import UIState from "../../enums/UIState";

interface OrderUpdate {
  type?: string;
  fromTerrID?: string | null;
  toTerrID?: string | null;
  viaConvoy?: string | null;
}
/* eslint-disable no-param-reassign */
export default function updateOrder(state, update: OrderUpdate): void {
  console.log("updateOrder");
  const { order } = current(state);
  console.log({ order, update });
  if (!state.order.inProgress) throw Error("");
  const newOrder = { ...state.order, ...update };

  // gotta do this carefully because the proxying
  // system gets confused if I update state.order
  // and then commit state.order
  if (
    newOrder.type === "Hold" ||
    newOrder.type === "Destroy" ||
    newOrder.type === "Disband" ||
    (newOrder.type && newOrder.toTerrID)
  ) {
    commitOrder(state, newOrder);
  } else {
    state.order = newOrder;
  }
}
