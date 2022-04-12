import BoardClass from "../../models/BoardClass";
import OrderClass from "../../models/OrderClass";
import TerritoryClass from "../../models/TerritoryClass";
import { EditOrderMeta } from "../../state/interfaces/SavedOrders";

interface Props {
  [key: string]: EditOrderMeta;
}

export default function getValidUnitBorderCrossings(data): Props {
  const { contextVars, currentOrders, territories, territoryStatuses, units } =
    data;

  if ("contextVars" in data && contextVars.context) {
    console.log({ contextVars });
    const newBoard = new BoardClass(
      JSON.parse(contextVars.context),
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

    const updateOrdersMeta = {};
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
    return updateOrdersMeta;
  }
  return {};
}
