import * as d3 from "d3";
import ArrowColor from "../../enums/ArrowColor";
import ArrowType from "../../enums/ArrowType";
import Territory from "../../enums/map/variants/classic/Territory";
import webDiplomacyTheme from "../../webDiplomacyTheme";

export default function drawArrow(
  /**
   * color code passed from enum ArrowColors based on move type
   */
  arrowIdentifier: string,
  arrowType: ArrowType,
  arrowColor: ArrowColor,
  receiverType: "territory" | "unit" | "arrow",
  receiverIdentifier: Territory | string,
  unitTerritory: Territory,
): void {
  const d3MapSelector = d3.select("#map");

  switch (receiverType) {
    default: {
      const toTerritoryName = Territory[receiverIdentifier];
      const fromTerritoryName = Territory[unitTerritory];
      let unitSlotEl = d3.select(`#${fromTerritoryName}-unit`).node();
      const toTerritoryReceiver: SVGRectElement = d3
        .select(`#${toTerritoryName}-arrow-receiver`)
        .node();
      const fromTerritoryEl: SVGSVGElement = d3
        .select(`#${fromTerritoryName}-territory`)
        .node();
      const toTerritoryEl: SVGSVGElement = d3
        .select(`#${toTerritoryName}-territory`)
        .node();
      if (fromTerritoryEl && toTerritoryEl && toTerritoryName && unitSlotEl) {
        unitSlotEl = unitSlotEl.parentNode;
        const unitSlotElBBox = unitSlotEl.getBBox();
        const unitSlotElX =
          Number(unitSlotEl.getAttribute("x")) + unitSlotElBBox.x;
        let x1 = Number(fromTerritoryEl.getAttribute("x")) + unitSlotElX;
        const x2 =
          Number(toTerritoryEl.getAttribute("x")) +
          Number(toTerritoryReceiver.getAttribute("x"));
        let y1 =
          Number(fromTerritoryEl.getAttribute("y")) +
          Number(unitSlotEl.getAttribute("y")) +
          unitSlotElBBox.y;
        const y2 =
          Number(toTerritoryEl.getAttribute("y")) +
          Number(toTerritoryReceiver.getAttribute("y"));
        const w = unitSlotElBBox.width;
        const h = unitSlotElBBox.height;
        const xDiff = x2 - x1;
        const yDiff = y2 - y1;
        const positionChangeBuffer = 75;
        if (Math.abs(xDiff) < positionChangeBuffer && yDiff < 0) {
          // top center
          x1 += w / 2;
        } else if (Math.abs(xDiff) < positionChangeBuffer && yDiff > 0) {
          // bottom center
          y1 += h;
          x1 += w / 2;
        } else if (
          xDiff > positionChangeBuffer &&
          yDiff < 0 &&
          Math.abs(yDiff) > positionChangeBuffer
        ) {
          // top right
          x1 += w;
        } else if (
          xDiff > positionChangeBuffer &&
          Math.abs(yDiff) < positionChangeBuffer
        ) {
          // center right
          y1 += h / 2;
          x1 += w;
        } else if (
          xDiff > positionChangeBuffer &&
          yDiff > positionChangeBuffer
        ) {
          // bottom right
          y1 += h;
          x1 += w;
        } else if (xDiff < 0 && Math.abs(yDiff) < positionChangeBuffer) {
          // center left
          y1 += h / 2;
        } else if (xDiff < 0 && yDiff > positionChangeBuffer) {
          // bottom left
          y1 += h;
        }
        const arrowClass = `arrow__${arrowIdentifier}`;
        if (arrowType === ArrowType.MOVE) {
          d3.selectAll(`.${arrowClass}`).remove();
        }
        d3MapSelector
          .select("#container")
          .append("line")
          .attr("x1", x1)
          .attr("y1", y1)
          .attr("x2", x2)
          .attr("y2", y2)
          .attr(
            "marker-end",
            `url(#arrowHead__${ArrowType[arrowType]}_${ArrowColor[arrowColor]})`,
          )
          .attr(
            "stroke",
            webDiplomacyTheme.palette.arrowColors[arrowColor].main,
          )
          .attr("stroke-width", "2")
          .attr("class", arrowClass);
      }
      break;
    }
  }
}
