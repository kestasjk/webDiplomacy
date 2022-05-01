import TerritoryMap from "../../data/map/variants/classic/TerritoryMap";
import ArrowColor from "../../enums/ArrowColor";
import ArrowType from "../../enums/ArrowType";
import GameDataResponse from "../../state/interfaces/GameDataResponse";
import GameStateMaps from "../../state/interfaces/GameStateMap";
import OrdersMeta from "../../state/interfaces/SavedOrders";
import drawArrow from "./drawArrow";

export default function drawSupportMoveOrders(
  data: GameDataResponse["data"],
  maps: GameStateMaps,
  ordersMeta: OrdersMeta,
): void {
  const { currentOrders, territories, units } = data;
  const ordersMetaEntries = Object.entries(ordersMeta);
  if (ordersMetaEntries.length && units && territories) {
    ordersMetaEntries
      .filter(([, { update }]) => update?.type === "Support move")
      .forEach(([orderID, { update }]) => {
        const originalOrder = currentOrders?.find(({ id }) => {
          return id === orderID;
        });
        if (originalOrder && update?.fromTerrID) {
          const { id, unitID } = originalOrder;
          const unitBeingSupported = maps.territoryToUnit[update.fromTerrID];
          const unitBeingSupportedMoveOrder =
            maps.unitToOrder[unitBeingSupported];
          const supportingTerritory = maps.unitToTerritory[unitID];
          const supportingTerritoryDetails = territories[supportingTerritory];
          const supportingTerritoryEnum =
            TerritoryMap[supportingTerritoryDetails.name].territory;
          drawArrow(
            id,
            ArrowType.SUPPORT,
            ArrowColor.SUPPORT_MOVE,
            "arrow",
            unitBeingSupportedMoveOrder,
            supportingTerritoryEnum,
          );
        }
      });
  }
}
