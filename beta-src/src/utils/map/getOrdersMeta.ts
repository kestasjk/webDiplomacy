import BoardClass from "../../models/BoardClass";
import OrderClass from "../../models/OrderClass";
import TerritoryClass from "../../models/TerritoryClass";
import { EditOrderMeta } from "../../state/interfaces/SavedOrders";

interface Props {
  [key: string]: EditOrderMeta;
}

export default function getOrdersMeta(data, phase): Props {
  const { contextVars, currentOrders, territories, territoryStatuses, units } =
    data;

  const { context } = contextVars;
  const updateOrdersMeta = {};
  if (context) {
    if (phase === "Builds") {
      currentOrders.forEach(({ id, toTerrID, type }) => {
        updateOrdersMeta[id] = {
          saved: true,
          update: {
            type,
            toTerrID,
          },
        };
      });
    } else {
      const newBoard = new BoardClass(
        context,
        Object.values(territories),
        territoryStatuses,
        Object.values(units),
      );

      const newOrders: OrderClass[] = [];

      currentOrders.forEach((o) => {
        const orderUnit = newBoard.findUnitByID(o.unitID);
        if (orderUnit) {
          newOrders.push(new OrderClass(newBoard, o, orderUnit));
        }
      });

      newOrders.forEach((o) => {
        const moveChoices = o.getMoveChoices();
        const orderUnit = newBoard.findUnitByID(o.unit.id);
        let allowedBorderCrossings: TerritoryClass[] = [];
        if (orderUnit) {
          allowedBorderCrossings = moveChoices.filter((choice) => {
            const { Borders } = choice;
            const from = Borders.find((border) => {
              return border.id === orderUnit.terrID;
            });
            if (from && orderUnit.canCrossBorder(from)) {
              return true;
            }
            return false;
          });
          updateOrdersMeta[o.orderData.id] = {
            allowedBorderCrossings,
          };
        }
      });
    }
  }
  return updateOrdersMeta;
}
