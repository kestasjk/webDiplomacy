import { current } from "@reduxjs/toolkit";
import UIState from "../../enums/UIState";
import getDataForOrder from "./getDataForOrder";

/* eslint-disable no-param-reassign */
export default function startNewOrder(state, partialOrderState): string {
  const orderData = getDataForOrder(state, partialOrderState);
  // console.log({ orderData });
  state.order = orderData;
  state.order.inProgress = true;
  return orderData.orderID;
}
