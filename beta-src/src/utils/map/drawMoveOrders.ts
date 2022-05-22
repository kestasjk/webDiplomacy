import TerritoryMap from "../../data/map/variants/classic/TerritoryMap";
import ArrowColor from "../../enums/ArrowColor";
import ArrowType from "../../enums/ArrowType";
import Territory from "../../enums/map/variants/classic/Territory";
import { GameState } from "../../state/interfaces/GameState";
import OrdersMeta from "../../state/interfaces/SavedOrders";
import drawArrow from "./drawArrow";

export default function drawMoveOrders(
  data: GameState["data"]["data"],
  maps: GameState["maps"],
  ordersMeta: OrdersMeta,
  board: GameState["board"],
): void {
  console.log("drawMoveOrders");
  console.log(data);
  const { currentOrders, territories, units } = data;
  const ordersMetaEntries = Object.entries(ordersMeta);
  if (ordersMetaEntries.length && units && territories) {
    ordersMetaEntries
      .filter(([, { update }]) => update?.type === "Move")
      .forEach(([orderID, { update }]) => {
        const originalOrder = currentOrders?.find(({ id }) => id === orderID);
        if (originalOrder && update) {
          const { convoyPath, toTerrID, viaConvoy } = update;
          if (toTerrID) {
            const { id, unitID } = originalOrder;
            const unitData = units[unitID];
            const onTerrDetails = territories[unitData.terrID];
            const toTerrDetails = territories[toTerrID];
            const fromTerr = TerritoryMap[onTerrDetails.name].territory;
            const toTerr = TerritoryMap[toTerrDetails.name].territory;
            console.log(`Drawing MOVE arrow from ${toTerr} to ${fromTerr}`);
            drawArrow(
              id,
              ArrowType.MOVE,
              ArrowColor.MOVE,
              "territory",
              toTerr,
              fromTerr,
            );
            if (viaConvoy === "Yes" && board) {
              // implied convoy arrow
              let path = convoyPath;
              if (!convoyPath) {
                const onTerritory = board.findTerritoryByID(unitData.terrID);
                path = board
                  .findUnitByID(unitID)
                  ?.ConvoyGroup.pathArmyToCoastWithoutFleet(
                    onTerritory,
                    board.findTerritoryByID(toTerrID),
                    onTerritory,
                  );
              }
              if (path) {
                path
                  .filter((p) => p !== unitData.terrID)
                  .forEach((p) => {
                    const convoyingUnitID = maps.terrIDToUnit[p];
                    const convoyingUnitOrder =
                      maps.unitToOrder[convoyingUnitID];
                    const unitOrderMeta = ordersMeta[convoyingUnitOrder].update;
                    if (
                      unitOrderMeta?.fromTerrID !== unitData.terrID ||
                      unitOrderMeta?.toTerrID !== update.toTerrID
                    ) {
                      drawArrow(
                        `${id}-implied`,
                        ArrowType.CONVOY,
                        ArrowColor.IMPLIED,
                        "arrow",
                        id,
                        maps.terrIDToTerritory[
                          maps.unitToTerrID[convoyingUnitID]
                        ],
                      );
                    }
                  });
              }
            }
          }
        }
      });
  }
}
