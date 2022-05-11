import { current } from "@reduxjs/toolkit";
import { GameCommand } from "../../state/interfaces/GameCommands";
import setCommand from "./setCommand";

/* eslint-disable no-param-reassign */
export default function resetOrder(state): void {
  const {
    order: { unitID, type },
    overview: { phase },
  } = current(state);
  if (type !== "hold" && type !== "retreat") {
    const command: GameCommand = {
      command: phase === "Retreats" ? "DISLODGED" : "NONE",
    };
    setCommand(state, command, "unitCommands", unitID);
  }
  if (type === "disband") {
    const command: GameCommand = {
      command: "DISBAND",
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
