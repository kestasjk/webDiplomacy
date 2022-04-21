import TerritoryMap from "../../data/map/variants/classic/TerritoryMap";
import ArrowType from "../../enums/ArrowType";
import drawArrow from "./drawArrow";

export default function drawCurrentMoveOrders(data): boolean {
  const { currentOrders, territories, units } = data;
  console.log({ currentOrders, territories, units });
  let wasSuccessful = false;
  if (currentOrders.length && units && territories) {
    wasSuccessful = true;
    currentOrders // dont use current orders, use ordersMeta
      .filter((order) => order.type === "Move")
      .forEach(({ id, toTerrID, unitID }) => {
        const unitData = units[unitID];
        const onTerrDetails = territories[unitData.terrID];
        const toTerrDetails = territories[toTerrID];
        const fromTerr = TerritoryMap[onTerrDetails.name].territory;
        const toTerr = TerritoryMap[toTerrDetails.name].territory;
        const arrowIdentifier = `${id}`;
        const drewArrow = drawArrow(
          arrowIdentifier,
          ArrowType.MOVE,
          toTerr,
          fromTerr,
        );
        if (!drewArrow) {
          wasSuccessful = false;
        }
      });
  }
  return wasSuccessful;
}
