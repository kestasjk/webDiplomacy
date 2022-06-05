import { IOrderData, IUnit } from "../../models/Interfaces";
import GameDataResponse from "../../state/interfaces/GameDataResponse";
import { GameState } from "../../state/interfaces/GameState";
import { EditOrderMeta } from "../../state/interfaces/SavedOrders";

interface Props {
  [key: string]: EditOrderMeta;
}

export default function getOrdersMeta(
  data: GameDataResponse["data"],
  phase: GameState["overview"]["phase"],
): EditOrderMeta {
  const { contextVars, currentOrders, units } = data;

  const updateOrdersMeta: EditOrderMeta = {};
  if (contextVars?.context && currentOrders?.length) {
    if (phase === "Builds") {
      currentOrders?.forEach(({ id, toTerrID, type }) => {
        updateOrdersMeta[id] = {
          saved: true,
          update: {
            type: toTerrID ? type || "" : "Wait",
            toTerrID,
          },
        };
      });
    } else {
      const newOrders: [IOrderData, IUnit][] = [];

      currentOrders.forEach((o) => {
        const { id, unitID, type, toTerrID, fromTerrID, viaConvoy } = o;
        if (units[unitID]?.id) {
          newOrders.push([o, units[unitID]]);
          updateOrdersMeta[id] = {
            saved: true,
            update: {
              type: type || "",
              toTerrID,
              fromTerrID,
              viaConvoy,
            },
          };
        }
      });
    }
  }
  return updateOrdersMeta;
}
