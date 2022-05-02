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

  const fromTerritoryName = Territory[unitTerritory];
  const fromTerritoryEl: SVGSVGElement = d3
    .select(`#${fromTerritoryName}-territory`)
    .node();
  let unit = d3.select(`#${fromTerritoryName}-unit`).node();
  let unitX;
  let unitY;
  let unitW;
  let unitH;

  if (unit && fromTerritoryEl) {
    unit = unit.parentNode;
    const fromTerritoryX = Number(fromTerritoryEl.getAttribute("x"));
    const fromTerritoryY = Number(fromTerritoryEl.getAttribute("y"));
    ({ x: unitX, y: unitY, width: unitW, height: unitH } = unit.getBBox());
    unitX = fromTerritoryX + Number(unit.getAttribute("x")) + unitX;
    unitY = fromTerritoryY + Number(unit.getAttribute("y")) + unitY;
    switch (receiverType) {
      case "arrow": {
        const arrow: SVGLineElement = d3
          .selectAll(`.arrow__${receiverIdentifier}`)
          .node();

        if (arrow) {
          x1 = unitX;
          y1 = unitY;
          const attachPoint = 0.6;
          const arrowX1 = Number(arrow.getAttribute("x1"));
          const arrowY1 = Number(arrow.getAttribute("y1"));
          const arrowX2 = Number(arrow.getAttribute("x2"));
          const arrowY2 = Number(arrow.getAttribute("y2"));
          const run = arrowX2 - arrowX1;
          const rise = arrowY2 - arrowY1;
          x2 = arrowX1 + run * attachPoint;
          y2 = arrowY1 + rise * attachPoint;
        }
        break;
      }
      case "unit": {
        const toTerritoryName = Territory[receiverIdentifier];
        let receiverUnit = d3.select(`#${toTerritoryName}-unit`).node();
        const toTerritoryEl: SVGSVGElement = d3
          .select(`#${toTerritoryName}-territory`)
          .node();

        if (toTerritoryEl && receiverUnit) {
          receiverUnit = receiverUnit.parentNode;

          const {
            x: rUnitX,
            y: rUnitY,
            width: rUnitW,
            height: rUnitH,
          } = receiverUnit.getBBox();

          const receiverSlotElX =
            Number(receiverUnit.getAttribute("x")) + rUnitX;
          const receiverSlotElY =
            Number(receiverUnit.getAttribute("y")) + rUnitY;

          x1 = unitX;
          x2 = Number(toTerritoryEl.getAttribute("x")) + receiverSlotElX;

          y1 = unitY;
          y2 = Number(toTerritoryEl.getAttribute("y")) + receiverSlotElY;

          const rw = rUnitW;
          const rh = rUnitH;
          const xDiff = x2 - x1;
          const yDiff = y2 - y1;
          const positionChangeBuffer = 75;
          if (Math.abs(xDiff) < positionChangeBuffer && yDiff < 0) {
            // dispatch: top center
            x1 += unitW / 2;
            // receiver: bottom center
            x2 += rw / 2;
            y2 += rh;
          } else if (Math.abs(xDiff) < positionChangeBuffer && yDiff > 0) {
            // dispatch: bottom center
            y1 += unitH;
            x1 += unitW / 2;
            // receiver: top center
            x2 += rw / 2;
          } else if (
            xDiff > positionChangeBuffer &&
            yDiff < 0 &&
            Math.abs(yDiff) > positionChangeBuffer
          ) {
            // dispatch: top right
            x1 += unitW;
            // receive: bottom left
            y2 += rh;
          } else if (
            xDiff > positionChangeBuffer &&
            Math.abs(yDiff) < positionChangeBuffer
          ) {
            // dispatch: center right
            y1 += unitH / 2;
            x1 += unitW;
            // receive: center left
            y2 += rh / 2;
          } else if (
            xDiff > positionChangeBuffer &&
            yDiff > positionChangeBuffer
          ) {
            // dispatch: bottom right
            y1 += unitH;
            x1 += unitW;
            // receive: top left
            // nothing needs to be done for top left
          } else if (xDiff < 0 && Math.abs(yDiff) < positionChangeBuffer) {
            // dispatch: center left
            y1 += unitH / 2;
            // receive: center right
            x2 += rw;
            y2 += rh / 2;
          } else if (xDiff < 0 && yDiff > positionChangeBuffer) {
            // dispatch: bottom left
            y1 += unitH;
            // receive: top right
            x2 += rw;
          }
        }
        break;
      }
      default: {
        const toTerritoryName = Territory[receiverIdentifier];
        const toTerritoryReceiver: SVGRectElement = d3
          .select(`#${toTerritoryName}-arrow-receiver`)
          .node();
        const toTerritoryEl: SVGSVGElement = d3
          .select(`#${toTerritoryName}-territory`)
          .node();
        if (toTerritoryEl && toTerritoryName) {
          x1 = unitX;
          x2 =
            Number(toTerritoryEl.getAttribute("x")) +
            Number(toTerritoryReceiver.getAttribute("x"));
          y1 = unitY;
          y2 =
            Number(toTerritoryEl.getAttribute("y")) +
            Number(toTerritoryReceiver.getAttribute("y"));

          const xDiff = x2 - x1;
          const yDiff = y2 - y1;
          const positionChangeBuffer = 75;
          if (Math.abs(xDiff) < positionChangeBuffer && yDiff < 0) {
            // top center
            x1 += unitW / 2;
          } else if (Math.abs(xDiff) < positionChangeBuffer && yDiff > 0) {
            // bottom center
            y1 += unitH;
            x1 += unitW / 2;
          } else if (
            xDiff > positionChangeBuffer &&
            yDiff < 0 &&
            Math.abs(yDiff) > positionChangeBuffer
          ) {
            // top right
            x1 += unitW;
          } else if (
            xDiff > positionChangeBuffer &&
            Math.abs(yDiff) < positionChangeBuffer
          ) {
            // center right
            y1 += unitH / 2;
            x1 += unitW;
          } else if (
            xDiff > positionChangeBuffer &&
            yDiff > positionChangeBuffer
          ) {
            // bottom right
            y1 += unitH;
            x1 += unitW;
          } else if (xDiff < 0 && Math.abs(yDiff) < positionChangeBuffer) {
            // center left
            y1 += unitH / 2;
          } else if (xDiff < 0 && yDiff > positionChangeBuffer) {
            // bottom left
            y1 += unitH;
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
}
