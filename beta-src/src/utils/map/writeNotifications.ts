import { withTheme } from "@emotion/react";
import { rgbToHex } from "@mui/material";
import { current } from "@reduxjs/toolkit";
import { GameState } from "../../state/interfaces/GameState";

/* eslint-disable no-param-reassign */
export default function writeNotifications(state): void {
  const {
    ordersMeta,
    overview: { phase },
  }: {
    // definition
    ordersMeta: GameState["ordersMeta"];
    overview: { phase: GameState["overview"]["phase"] };
  } = current(state);
  if (phase === "Builds") {
    let ordersToGo = 0;
    let totalOrders = 0;
    let toDo;
    // values instead of entries key/value difference
    Object.values(ordersMeta).forEach(({ update }) => {
      if (update?.toTerrID === null) ordersToGo += 1;
      totalOrders += 1;
      if (update?.type === "Destroy") toDo = "Destroy";
    });
    if (toDo === "Destroy") {
      if (ordersToGo > 1) {
        state.notifications[0] = {
          message: `Select ${ordersToGo} units to destroy.`,
          style: {
            borderRadius: "8px",
            backgroundColor: "rgba(0,0,0,.4)",
            color: "#ffffff",
            fontSize: "16px",
            fontWeight: 700,
            height: 40,
            lineHeight: "253%",
            m: 1,
            textAlign: "center",
            width: 200,
          },
        };
      } else if (ordersToGo === 1) {
        state.notifications[0] = {
          message: "Select 1 unit to destroy.",
          style: {
            borderRadius: "8px",
            backgroundColor: "rgba(0,0,0,.4)",
            color: "#ffffff",
            fontSize: "16px",
            fontWeight: 700,
            height: 40,
            lineHeight: "253%",
            m: 1,
            textAlign: "center",
            width: 200,
          },
        };
      } else if (ordersToGo === 0) {
        state.notifications[0] = {
          message: "Orders ready to submit",
          style: {
            borderRadius: "8px",
            backgroundColor: "rgba(0,0,0,.4)",
            color: "#ffffff",
            fontSize: "16px",
            fontWeight: 700,
            height: 40,
            lineHeight: "253%",
            m: 1,
            textAlign: "center",
            width: 200,
          },
        };
      }
      if (ordersToGo < totalOrders) {
        state.notifications[1] = {
          message: "Select units again to cancel",
          style: {
            borderRadius: "8px",
            backgroundColor: "rgba(0,0,0,.4)",
            color: "#ffffff",
            fontSize: "16px",
            fontWeight: 400,
            height: 40,
            lineHeight: "253%",
            m: 1,
            textAlign: "center",
            width: 218,
          },
        };
      } else {
        state.notifications[1] = undefined;
      }
    }
  }
}
