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

  let x1;
  let x2;
  let y1;
  let y2;

  switch (receiverType) {
    case "unit": {
      const toTerritoryName = Territory[receiverIdentifier];
      const fromTerritoryName = Territory[unitTerritory];
      let unitSlotEl = d3.select(`#${fromTerritoryName}-unit`).node();
      let receiverUnit = d3.select(`#${toTerritoryName}-unit`).node();
      const fromTerritoryEl: SVGSVGElement = d3
        .select(`#${fromTerritoryName}-territory`)
        .node();
      const toTerritoryEl: SVGSVGElement = d3
        .select(`#${toTerritoryName}-territory`)
        .node();

      if (fromTerritoryEl && toTerritoryEl && receiverUnit && unitSlotEl) {
        unitSlotEl = unitSlotEl.parentNode;
        receiverUnit = receiverUnit.parentNode;
        const unitSlotElBBox = unitSlotEl.getBBox();
        const receiverUnitBBox = receiverUnit.getBBox();
        const unitSlotElX =
          Number(unitSlotEl.getAttribute("x")) + unitSlotElBBox.x;
        const receiverSlotElX =
          Number(receiverUnit.getAttribute("x")) + receiverUnitBBox.x;
        const receiverSlotElY =
          Number(receiverUnit.getAttribute("y")) + receiverUnitBBox.y;

        x1 = Number(fromTerritoryEl.getAttribute("x")) + unitSlotElX;
        x2 = Number(toTerritoryEl.getAttribute("x")) + receiverSlotElX;

        y1 =
          Number(fromTerritoryEl.getAttribute("y")) +
          Number(unitSlotEl.getAttribute("y")) +
          unitSlotElBBox.y;
        y2 = Number(toTerritoryEl.getAttribute("y")) + receiverSlotElY;

        const w = unitSlotElBBox.width;
        const h = unitSlotElBBox.height;
        const rw = receiverUnitBBox.width;
        const rh = receiverUnitBBox.height;
        const xDiff = x2 - x1;
        const yDiff = y2 - y1;
        const positionChangeBuffer = 75;
        if (Math.abs(xDiff) < positionChangeBuffer && yDiff < 0) {
          // dispatch: top center
          x1 += w / 2;
          // receiver: bottom center
          x2 += rw / 2;
          y2 += rh;
        } else if (Math.abs(xDiff) < positionChangeBuffer && yDiff > 0) {
          // dispatch: bottom center
          y1 += h;
          x1 += w / 2;
          // receiver: top center
          x2 += rw / 2;
        } else if (
          xDiff > positionChangeBuffer &&
          yDiff < 0 &&
          Math.abs(yDiff) > positionChangeBuffer
        ) {
          // dispatch: top right
          x1 += w;
          // receive: bottom left
          y2 += rh;
        } else if (
          xDiff > positionChangeBuffer &&
          Math.abs(yDiff) < positionChangeBuffer
        ) {
          // dispatch: center right
          y1 += h / 2;
          x1 += w;
          // receive: center left
          y2 += rh / 2;
        } else if (
          xDiff > positionChangeBuffer &&
          yDiff > positionChangeBuffer
        ) {
          // dispatch: bottom right
          y1 += h;
          x1 += w;
          // receive: top left
          // nothing needs to be done for top left
        } else if (xDiff < 0 && Math.abs(yDiff) < positionChangeBuffer) {
          // dispatch: center left
          y1 += h / 2;
          // receive: center right
          x2 += rw;
          y2 += rh / 2;
        } else if (xDiff < 0 && yDiff > positionChangeBuffer) {
          // dispatch: bottom left
          y1 += h;
          // receive: top right
          x2 += rw;
        }
      }
      break;
    }
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
        x1 = Number(fromTerritoryEl.getAttribute("x")) + unitSlotElX;
        x2 =
          Number(toTerritoryEl.getAttribute("x")) +
          Number(toTerritoryReceiver.getAttribute("x"));
        y1 =
          Number(fromTerritoryEl.getAttribute("y")) +
          Number(unitSlotEl.getAttribute("y")) +
          unitSlotElBBox.y;
        y2 =
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
      }
      break;
    }
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
    .attr("stroke", webDiplomacyTheme.palette.arrowColors[arrowColor].main)
    .attr("stroke-width", "2")
    .attr("class", arrowClass);
}
