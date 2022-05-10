import OrderClass from "../../models/OrderClass";
import TerritoryClass from "../../models/TerritoryClass";
import GameDataResponse from "../../state/interfaces/GameDataResponse";
import { GameState } from "../../state/interfaces/GameState";
import {
  EditOrderMeta,
  SupportMoveChoice,
} from "../../state/interfaces/SavedOrders";

interface Props {
  [key: string]: EditOrderMeta;
}

export default function getOrdersMeta(
  data: GameDataResponse["data"],
  board: GameState["board"],
  phase: GameState["overview"]["phase"],
): Props {
  const { contextVars, currentOrders, territories } = data;

  const updateOrdersMeta = {};
  if (contextVars?.context) {
    if (phase === "Builds") {
      currentOrders?.forEach(({ id, toTerrID, type }) => {
        updateOrdersMeta[id] = {
          saved: true,
          update: {
            type: toTerrID ? type : "Wait",
            toTerrID,
          },
        };
      });
    } else {
      const newOrders: OrderClass[] = [];

      if (!board) {
        return updateOrdersMeta;
      }

      currentOrders?.forEach((o) => {
        const { id, unitID, type, toTerrID, fromTerrID, viaConvoy } = o;
        const orderUnit = board.findUnitByID(unitID);
        if (orderUnit) {
          newOrders.push(new OrderClass(board, o, orderUnit));
          updateOrdersMeta[id] = {
            update: {
              type,
              toTerrID,
              fromTerrID,
              viaConvoy,
            },
          };
        }
      });

      newOrders.forEach((o) => {
        const moveChoices = o.getMoveChoices();
        const supportMoveToChoices = o.getSupportMoveToChoices();
        const supportHoldChoices = o.getSupportHoldChoices();
        const supportMoveChoices: SupportMoveChoice[] = [];
        const convoyToChoices = o.getConvoyToChoices();
        const convoyToNames: string[] = [];
        convoyToChoices.forEach((convoyTo) => {
          const terr = territories[convoyTo];
          convoyToNames.push(terr.name);
        });
        supportMoveToChoices.forEach((supportMoveTo) => {
          const supportMoveFrom = o.getSupportMoveFromChoices(supportMoveTo);
          if (supportMoveFrom.length) {
            supportMoveChoices.push({
              supportMoveTo,
              supportMoveFrom,
            });
          }
        });
        const orderUnit = board.findUnitByID(o.unit.id);
        let allowedBorderCrossings: TerritoryClass[] = [];
        if (orderUnit) {
          allowedBorderCrossings = moveChoices.filter((choice) => {
            const { Borders } = choice;
            const from = Borders.find(
              (border) => border.id === orderUnit.terrID,
            );
            if (from && orderUnit.canCrossBorder(from)) {
              return true;
            }
            return false;
          });
          updateOrdersMeta[o.orderData.id] = {
            ...{ saved: true },
            ...updateOrdersMeta[o.orderData.id],
            allowedBorderCrossings,
            supportMoveChoices,
            supportHoldChoices,
            convoyToChoices,
          };
        }
      });
    }
  }
  return updateOrdersMeta;
}
