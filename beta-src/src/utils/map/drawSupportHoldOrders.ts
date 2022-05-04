import TerritoryMap from "../../data/map/variants/classic/TerritoryMap";
import ArrowColor from "../../enums/ArrowColor";
import ArrowType from "../../enums/ArrowType";
import OrdersMeta from "../../state/interfaces/SavedOrders";
import drawArrow from "./drawArrow";

export default function drawSupportHoldOrders(
  data,
  ordersMeta: OrdersMeta,
): void {
  const { currentOrders, territories, units } = data;
  const ordersMetaEntries = Object.entries(ordersMeta);
  if (ordersMetaEntries.length && units && territories) {
    ordersMetaEntries
      .filter(([, { update }]) => update?.type === "Support hold")
      .forEach(([orderID, { update }]) => {
        const originalOrder = currentOrders.find(({ id }) => id === orderID);
        if (originalOrder && update && update.toTerrID) {
          const { id, unitID } = originalOrder;
          const unitData = units[unitID];
          const onTerrDetails = territories[unitData.terrID];
          const toTerrDetails = territories[update.toTerrID];
          const fromTerr = TerritoryMap[onTerrDetails.name].territory;
          const toTerr = TerritoryMap[toTerrDetails.name].territory;
          drawArrow(
            id,
            ArrowType.HOLD,
            ArrowColor.SUPPORT_HOLD,
            "unit",
            toTerr,
            fromTerr,
          );
        }
      });
  }
}
