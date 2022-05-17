import { current } from "@reduxjs/toolkit";
import { GameCommand } from "../../state/interfaces/GameCommands";
import setCommand from "../state/setCommand";
import drawBuilds from "./drawBuilds";
import drawConvoyOrders from "./drawConvoyOrders";
import drawMoveOrders from "./drawMoveOrders";
import drawRetreatOrders from "./drawRetreatOrders";
import drawSupportHoldOrders from "./drawSupportHoldOrders";
import drawSupportMoveOrders from "./drawSupportMoveOrders";
import removeAllArrows from "./removeAllArrows";
import updateUnitsRetreat from "./updateUnitsRetreat";

export default function drawOrders(state): void {
  const {
    board,
    data: { data },
    maps,
    ordersMeta,
    overview: { phase },
    ownUnits,
  } = current(state);
  removeAllArrows();
  drawMoveOrders(data, maps, ordersMeta, board);
  drawSupportMoveOrders(data, maps, ordersMeta, ownUnits);
  drawSupportHoldOrders(data, ordersMeta);
  drawConvoyOrders(data, maps, ordersMeta);
  drawBuilds(state);
  if (phase === "Retreats") {
    updateUnitsRetreat(state);
    drawRetreatOrders(data, ordersMeta);
  }
  const command: GameCommand = {
    command: "TOGGLE_POPOVER",
  };
  setCommand(state, command, "mapCommands", "ui");
}
