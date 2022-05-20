import { current } from "@reduxjs/toolkit";
import { GameCommand } from "../../state/interfaces/GameCommands";
import setCommand from "./setCommand";
import Territory from "../../enums/map/variants/classic/Territory";

/* eslint-disable no-param-reassign */
export default function resetOrder(state): void {
  const {
    data,
    maps,
    order: { unitID, type },
    overview: { phase },
  } = current(state);
  const {
    data: { units },
  } = data;
  const unitType = units[unitID].type;
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

  if (unitType === "Fleet") {
    const command: GameCommand = {
      command: "DISABLE_COAST",
    };

    maps.coastalTerritories.forEach((coast) => {
      setCommand(state, command, "territoryCommands", Territory[coast]);
    });
  }

  state.order.inProgress = false;
  state.order.unitID = "";
  state.order.orderID = "";
  state.order.onTerritory = 0;
  state.order.toTerritory = 0;
  state.order.subsequentClicks = [];
  delete state.order.type;
}
