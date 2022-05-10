import TerritoryMap from "../../data/map/variants/classic/TerritoryMap";
import ArrowColor from "../../enums/ArrowColor";
import ArrowType from "../../enums/ArrowType";
import GameDataResponse from "../../state/interfaces/GameDataResponse";
import GameStateMaps from "../../state/interfaces/GameStateMaps";
import OrdersMeta from "../../state/interfaces/SavedOrders";
import drawArrow from "./drawArrow";

export default function drawConvoyOrders(
  data: GameDataResponse["data"],
  maps: GameStateMaps,
  ordersMeta: OrdersMeta,
): void {
  const { currentOrders, territories, units } = data;
  const ordersMetaEntries = Object.entries(ordersMeta);
  if (ordersMetaEntries.length && units && territories) {
    ordersMetaEntries
      .filter(([, { update }]) => update?.type === "Convoy")
      .forEach(([orderID, { update }]) => {
        const originalOrder = currentOrders?.find(({ id }) => id === orderID);
        if (originalOrder && update) {
          const { fromTerrID, toTerrID } = update;
          if (fromTerrID && toTerrID) {
            const { id, unitID } = originalOrder;

            const unitBeingConvoyed = maps.territoryToUnit[fromTerrID];
            const unitBeingConvoyedOrder = maps.unitToOrder[unitBeingConvoyed];

            const supportingTerritory = maps.unitToTerritory[unitID];
            const supportingTerritoryDetails = territories[supportingTerritory];

            const { update: unitBeingSupportedOrderDetails } =
              ordersMeta[unitBeingConvoyedOrder];
            if (unitBeingSupportedOrderDetails) {
              drawArrow(
                id,
                ArrowType.CONVOY,
                ArrowColor.CONVOY,
                "arrow",
                unitBeingConvoyedOrder,
                TerritoryMap[supportingTerritoryDetails.name].territory,
              );
            }
          }
        }
      });
  }
}
