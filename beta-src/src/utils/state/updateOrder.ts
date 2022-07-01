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
  convoyPath?: any;
}

function isOrderReady(order: OrderUpdate): boolean {
  if (!order.type) return false;
  if (["Hold", "Destroy", "Disband"].includes(order.type)) return true;
  if (order.type === "Build") return false; // handled by popup
  if (order.toTerrID) {
    if (order.type === "Convoy" || order.viaConvoy === "Yes")
      return order.convoyPath;
    return true;
  }
  return false;
}
/* eslint-disable no-param-reassign */
export default function updateOrder(state, update: OrderUpdate): void {
  const { order } = current(state);
  // console.log({ order, update });
  const newOrder = { ...state.order, ...update };
  if (!newOrder.inProgress) throw Error(newOrder);
  // gotta do this carefully because the proxying
  // system gets confused if I update state.order
  // and then commit state.order
  if (isOrderReady(newOrder)) {
    commitOrder(state, newOrder);
  } else {
    state.order = newOrder;
  }
}
