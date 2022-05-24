import { current } from "@reduxjs/toolkit";
import { EditOrder, OrderMetaUpdate } from "../../state/interfaces/SavedOrders";
import commitOrder from "./commitOrder";
import UIState from "../../enums/UIState";

interface OrderUpdate {
  type?: string;
  fromTerrID?: string | null;
  toTerrID?: string | null;
  viaConvoy?: string | null;
  orderID?: string | null;
  inProgress?: boolean;
}
/* eslint-disable no-param-reassign */
export default function updateOrder(state, update: OrderUpdate): void {
  console.log("updateOrder");
  const { order } = current(state);
  console.log({ order, update });
  const newOrder = { ...state.order, ...update };
  if (!newOrder.inProgress) throw Error(newOrder);
  // gotta do this carefully because the proxying
  // system gets confused if I update state.order
  // and then commit state.order
  if (
    ["Hold", "Destroy", "Disband"].includes(newOrder.type) ||
    (newOrder.type && newOrder.type !== "Build" && newOrder.toTerrID)
  ) {
    commitOrder(state, newOrder);
  } else {
    state.order = newOrder;
  }
}
