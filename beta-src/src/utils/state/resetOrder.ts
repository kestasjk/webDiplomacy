import { current } from "@reduxjs/toolkit";
import { GameCommand } from "../../state/interfaces/GameCommands";
import setCommand from "./setCommand";

/* eslint-disable no-param-reassign */
export default function resetOrder(state): void {
  const {
    order: { unitID, type },
  } = current(state);
  if (type !== "hold") {
    const command: GameCommand = {
      command: "NONE",
    };
    setCommand(state, command, "unitCommands", unitID);
  }
  state.order.inProgress = false;
  state.order.unitID = "";
  state.order.orderID = "";
  state.order.onTerritory = 0;
  state.order.toTerritory = 0;
  state.order.subsequentClicks = [];
  delete state.order.type;
}
