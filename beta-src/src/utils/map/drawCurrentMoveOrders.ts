import TerritoryMap from "../../data/map/variants/classic/TerritoryMap";
import ArrowType from "../../enums/ArrowType";
import drawArrow from "./drawArrow";

export default function drawCurrentMoveOrders(data): void {
  const { currentOrders, territories, units } = data;

  if (currentOrders && units && territories) {
    currentOrders
      .filter((order) => order.type === "Move")
      .forEach(({ id, toTerrID, unitID }) => {
        const unitData = units[unitID];
        const onTerrDetails = territories[unitData.terrID];
        const toTerrDetails = territories[toTerrID];
        const fromTerr = TerritoryMap[onTerrDetails.name].territory;
        const toTerr = TerritoryMap[toTerrDetails.name].territory;
        const arrowIdentifier = `${id}`;
        drawArrow(arrowIdentifier, ArrowType.MOVE, toTerr, fromTerr);
      });
  }
}
