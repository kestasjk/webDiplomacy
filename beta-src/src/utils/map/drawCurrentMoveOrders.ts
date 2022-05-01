import TerritoryMap from "../../data/map/variants/classic/TerritoryMap";
import ArrowColor from "../../enums/ArrowColor";
import ArrowType from "../../enums/ArrowType";
import OrdersMeta from "../../state/interfaces/SavedOrders";
import drawArrow from "./drawArrow";

export default function drawCurrentMoveOrders(
  data,
  ordersMeta: OrdersMeta,
): void {
  const { currentOrders, territories, units } = data;
  const ordersMetaEntries = Object.entries(ordersMeta);
  if (ordersMetaEntries.length && units && territories) {
    ordersMetaEntries.forEach(([orderID, { update }]) => {
      const originalOrder = currentOrders.find(({ id }) => {
        return id === orderID;
      });

      if (originalOrder) {
        const { id, unitID } = originalOrder;

        if (update) {
          const { toTerrID, type } = update;
          if (type === "Move" && toTerrID) {
            const unitData = units[unitID];
            const onTerrDetails = territories[unitData.terrID];
            const toTerrDetails = territories[toTerrID];
            const fromTerr = TerritoryMap[onTerrDetails.name].territory;
            const toTerr = TerritoryMap[toTerrDetails.name].territory;
            drawArrow(
              id,
              ArrowType.MOVE,
              ArrowColor.MOVE,
              "territory",
              toTerr,
              fromTerr,
            );
          }
        }
      }
    });
  }
}
