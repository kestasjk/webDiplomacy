import { current } from "@reduxjs/toolkit";
import { GameCommand } from "../../state/interfaces/GameCommands";
import GameDataResponse from "../../state/interfaces/GameDataResponse";
import GameOverviewResponse from "../../state/interfaces/GameOverviewResponse";
import OrderState from "../../state/interfaces/OrderState";
import OrdersMeta from "../../state/interfaces/SavedOrders";
import setCommand from "../state/setCommand";

export default function updateUnitsRetreat(state): void {
  const {
    data: {
      data: { currentOrders },
    },
    order,
    ordersMeta,
    overview: {
      phase,
      user: {
        member: { orderStatus },
      },
    },
  }: {
    data: GameDataResponse;
    order: OrderState;
    ordersMeta: OrdersMeta;
    overview: GameOverviewResponse;
  } = current(state);

  if (phase !== "Retreats") {
    return;
  }

  const nonForcedDisbandingUnit = Object.values(ordersMeta).find(
    (o) => o.allowedBorderCrossings?.length && o.update?.type !== "Disband",
  );
  let command: GameCommand = {
    command: "HOLD",
  };

  currentOrders?.forEach(({ id, unitID }) => {
    const { update } = ordersMeta[id];
    const toTerrID = update?.toTerrID;
    const type = update?.type;

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
  });

  if (!nonForcedDisbandingUnit && !orderStatus.Completed) {
    command = {
      command: "SAVE_ORDERS",
    };
    setCommand(state, command, "mapCommands", "save");
  }
}
