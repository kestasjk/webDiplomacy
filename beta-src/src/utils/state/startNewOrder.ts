import { current } from "@reduxjs/toolkit";
import NewOrderPayload from "../../interfaces/state/NewOrderPayload";
import { GameCommand } from "../../state/interfaces/GameCommands";
import getDataForOrder from "./getDataForOrder";
import setCommand from "./setCommand";

/* eslint-disable no-param-reassign */
export default function startNewOrder(state, action: NewOrderPayload): void {
  const {
    order: { unitID: prevUnitID, type },
  } = current(state);
  if (prevUnitID && type !== "hold" && type !== "disband") {
    const command: GameCommand = {
      command: "NONE",
    };
    setCommand(state, command, "unitCommands", prevUnitID);
  }
  delete state.order.type;
  const orderData = getDataForOrder(state, action.payload);
  state.order = orderData;
  const { unitID } = orderData;
  if (unitID) {
    const command: GameCommand = {
      command: "SELECTED",
    };
    setCommand(state, command, "unitCommands", unitID);
  }
}
