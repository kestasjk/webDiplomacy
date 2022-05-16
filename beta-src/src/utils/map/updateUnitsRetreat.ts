import { current } from "@reduxjs/toolkit";
import { GameCommand } from "../../state/interfaces/GameCommands";
import GameDataResponse from "../../state/interfaces/GameDataResponse";
import GameOverviewResponse from "../../state/interfaces/GameOverviewResponse";
import OrderState from "../../state/interfaces/OrderState";
import OrdersMeta from "../../state/interfaces/SavedOrders";
import getOrderStates from "../state/getOrderStates";
import setCommand from "../state/setCommand";

export default function updateUnitsRetreat(state): void {
  const {
    data: {
      data: { contextVars, currentOrders },
    },
    order,
    ordersMeta,
    overview: { phase },
  }: {
    data: GameDataResponse;
    order: OrderState;
    ordersMeta: OrdersMeta;
    overview: GameOverviewResponse;
  } = current(state);
  let orderStates;

  if (contextVars) {
    orderStates = getOrderStates(contextVars.context.orderStatus);
  }

  currentOrders?.forEach(({ id, unitID }) => {
    const { update } = ordersMeta[id];
    const { allowedBorderCrossings } = ordersMeta[id];
    const toTerrID = update?.toTerrID;
    const type = update?.type;

    if (phase !== "Retreats") {
      return;
    }

    let command: GameCommand = {
      command: "HOLD",
    };

    if (type === "Retreat" && !toTerrID) {
      command = {
        command: "DISLODGED",
      };
    } else if (type === "Retreat" && toTerrID) {
      command = {
        command: "NONE",
      };
    } else if (type === "Disband" && order.orderID !== id) {
      command = {
        command: "DISBAND",
      };
    }
    setCommand(state, command, "unitCommands", unitID);

    if (
      currentOrders.length === 1 &&
      type === "Disband" &&
      !allowedBorderCrossings?.length &&
      !orderStates?.Completed
    ) {
      command = {
        command: "SAVE_ORDERS",
      };
      setCommand(state, command, "mapCommands", "save");
    }
  });
}
