import { current } from "@reduxjs/toolkit";
import { GameState } from "../../state/interfaces/GameState";

const defaultStyle = {
  borderRadius: "8px",
  backgroundColor: "rgba(0,0,0,.4)",
  color: "#ffffff",
  fontSize: "16px",
  fontWeight: 700,
  height: 40,
  lineHeight: 2.5,
  m: 1,
  textAlign: "center",
  width: 200,
};

// Currently, indexes 0 and 1 are being used as the delete labels.
// 0 is the tracker which tells the player how many units need to be destroyed and is always displayed
// when needing to destroy a unit and 1 renders conditionally based on whether the player has
// selected any units so far or not

/* eslint-disable no-param-reassign */
export default function writeBuildNotifications(state): void {
  const {
    ordersMeta,
    overview: {
      phase,
      user: {
        member: { supplyCenterNo, unitNo },
      },
    },
  }: {
    // definition
    ordersMeta: GameState["ordersMeta"];
    overview: {
      phase: GameState["overview"]["phase"];
      pot: GameState["overview"]["pot"];
      user: {
        member: {
          supplyCenterNo: GameState["overview"]["user"]["member"]["supplyCenterNo"];
          unitNo: GameState["overview"]["user"]["member"]["unitNo"];
        };
      };
    };
  } = current(state);
  if (phase === "Builds") {
    let ordersToGo = 0;
    let totalOrders = 0;
    // values instead of entries key/value difference
    Object.values(ordersMeta).forEach(({ update }) => {
      if (update?.toTerrID === null) ordersToGo += 1;
      totalOrders += 1;
    });
    if (supplyCenterNo < unitNo) {
      if (ordersToGo > 1) {
        state.notifications[0] = {
          message: `Select ${ordersToGo} units to destroy.`,
          style: { ...defaultStyle },
        };
        state.notifications.deletetracker = "hello";
      } else if (ordersToGo === 1) {
        state.notifications[0] = {
          message: "Select 1 unit to destroy.",
          style: { ...defaultStyle },
        };
      } else if (ordersToGo === 0) {
        state.notifications[0] = {
          message: "Orders ready to submit",
          style: { ...defaultStyle },
        };
      }
      if (ordersToGo < totalOrders) {
        state.notifications[1] = {
          message: "Select units again to cancel",
          style: {
            ...defaultStyle,
            fontWeight: 400,
            width: 218,
          },
        };
      } else {
        state.notifications[1] = undefined;
      }
    }
  } else {
    state.notifications = [];
  }
}
