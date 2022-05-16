import { current } from "@reduxjs/toolkit";
import startNewOrder from "../../startNewOrder";

export default function processUnitDoubleClick(state, clickData): void {
  const {
    overview: {
      user: {
        member: { orderStatus },
      },
    },
    ownUnits,
  } = current(state);
  if (orderStatus.Ready || !ownUnits.includes(clickData.payload.unitID)) {
    return;
  }
  startNewOrder(state, clickData);
}
