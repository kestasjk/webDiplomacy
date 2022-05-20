import * as React from "react";
import * as d3 from "d3";
import ArrowColor from "../../enums/ArrowColor";
import ArrowType from "../../enums/ArrowType";
import Territory from "../../enums/map/variants/classic/Territory";
import webDiplomacyTheme from "../../webDiplomacyTheme";
import arrowDispatchReceiveCoordinates from "./arrowDispatchReceiveCoordinates";
import TerritoryMap from "../../data/map/variants/classic/TerritoryMap";
import { UNIT_HEIGHT, UNIT_WIDTH } from "../../components/ui/units/WDUnit";

export default function drawArrowFunctional(
  /**
   * color code passed from enum ArrowColors based on move type
   */
  arrowType: ArrowType,
  arrowColor: ArrowColor,
  receiverType: "territory" | "unit" | "arrow",
  receiverIdentifier: Territory | string,
  unitTerritory: Territory,
): React.ReactElement | null {
  console.log(
    `drawArrowFunctional ${receiverType} ${receiverIdentifier} ${unitTerritory}`,
  );
  let x1;
  let x2;
  let y1;
  let y2;

  const fromTerritoryName = Territory[unitTerritory];
  const fromTerritoryData = TerritoryMap[fromTerritoryName].territoryMapData;
  const { unitSlotName } = TerritoryMap[fromTerritoryName];
  const unitW = UNIT_WIDTH;
  const unitH = UNIT_HEIGHT;

  let positionChangeBuffer;
  let xDiff;
  let yDiff;

  let rw;
  let rh;

  const fromTerritoryX = fromTerritoryData.x;
  const fromTerritoryY = fromTerritoryData.y;
  let unitX = fromTerritoryX;
  let unitY = fromTerritoryY;
  if (fromTerritoryData.unitSlotsBySlotName[unitSlotName]) {
    unitX += fromTerritoryData.unitSlotsBySlotName[unitSlotName].x;
    unitY += fromTerritoryData.unitSlotsBySlotName[unitSlotName].y;
  }

  switch (receiverType) {
    case "arrow": {
      const arrow: SVGLineElement = d3
        .selectAll(`.arrow__${receiverIdentifier}`)
        .node();

      if (arrow) {
        x1 = unitX;
        y1 = unitY;
        const attachPoint = 0.75;
        const arrowX1 = Number(arrow.getAttribute("x1"));
        const arrowY1 = Number(arrow.getAttribute("y1"));
        const arrowX2 = Number(arrow.getAttribute("x2"));
        const arrowY2 = Number(arrow.getAttribute("y2"));
        const run = arrowX2 - arrowX1;
        const rise = arrowY2 - arrowY1;
        x2 = arrowX1 + run * attachPoint;
        y2 = arrowY1 + rise * attachPoint;
        xDiff = x2 - x1;
        yDiff = y2 - y1;
        positionChangeBuffer = 60;
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

        const receiverSlotElX = Number(receiverUnit.getAttribute("x")) + rUnitX;
        const receiverSlotElY = Number(receiverUnit.getAttribute("y")) + rUnitY;

        x1 = unitX;
        x2 = Number(toTerritoryEl.getAttribute("x")) + receiverSlotElX;

        y1 = unitY;
        y2 = Number(toTerritoryEl.getAttribute("y")) + receiverSlotElY;

        rw = rUnitW;
        rh = rUnitH;
        xDiff = x2 - x1;
        yDiff = y2 - y1;
        positionChangeBuffer = 75;
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

        xDiff = x2 - x1;
        yDiff = y2 - y1;
        positionChangeBuffer = 75;
      }
      break;
    }
  }

  ({ x1, x2, y1, y2 } = arrowDispatchReceiveCoordinates(
    positionChangeBuffer,
    unitH,
    unitW,
    rh,
    rw,
    xDiff,
    yDiff,
    x1,
    x2,
    y1,
    y2,
  ));

  return (
    <line
      x1={x1}
      y1={y1}
      x2={x2}
      y2={y2}
      markerEnd={`url(#arrowHead__${ArrowType[arrowType]}_${ArrowColor[arrowColor]})`}
      stroke={webDiplomacyTheme.palette.arrowColors[arrowColor].main}
      strokeWidth={3}
    />
  );
}
