import { current } from "@reduxjs/toolkit";
import drawBuilds from "./drawBuilds";
import drawConvoyOrders from "./drawConvoyOrders";
import drawMoveOrders from "./drawMoveOrders";
import drawSupportHoldOrders from "./drawSupportHoldOrders";
import drawSupportMoveOrders from "./drawSupportMoveOrders";
import removeAllArrows from "./removeAllArrows";

export default function drawOrders(state): void {
  const {
    board,
    data: { data },
    maps,
    ordersMeta,
  } = current(state);
  removeAllArrows();
  drawMoveOrders(data, maps, ordersMeta, board);
  drawSupportMoveOrders(data, maps, ordersMeta);
  drawSupportHoldOrders(data, ordersMeta);
  drawConvoyOrders(data, maps, ordersMeta);
  drawBuilds(state);
}
