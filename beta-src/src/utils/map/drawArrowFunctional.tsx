import * as React from "react";
import * as d3 from "d3";
import ArrowColor from "../../enums/ArrowColor";
import ArrowType from "../../enums/ArrowType";
import Territory from "../../enums/map/variants/classic/Territory";
import webDiplomacyTheme from "../../webDiplomacyTheme";
import arrowDispatchReceiveCoordinates from "./arrowDispatchReceiveCoordinates";
import TerritoryMap from "../../data/map/variants/classic/TerritoryMap";
import { UNIT_HEIGHT, UNIT_WIDTH } from "../../components/ui/units/WDUnit";

export function getArrowX1Y1X2Y2(
  sourceType: "unit",
  sourceIdentifier: Territory,
  receiverType: "territory" | "unit" | "arrow",
  receiverIdentifier: Territory | [number, number, number, number],
): [number, number, number, number] {
  let positionChangeBuffer;

  // Source of arrow
  let x1;
  let y1;
  {
    const unitTerritory = sourceIdentifier;
    const fromTerritoryName = Territory[unitTerritory];
    const fromTerritoryData = TerritoryMap[fromTerritoryName].territoryMapData;
    const { unitSlotName } = TerritoryMap[fromTerritoryName];
    const fromTerritoryX = fromTerritoryData.x;
    const fromTerritoryY = fromTerritoryData.y;
    x1 = fromTerritoryX;
    y1 = fromTerritoryY;
    if (fromTerritoryData.unitSlotsBySlotName[unitSlotName]) {
      x1 += fromTerritoryData.unitSlotsBySlotName[unitSlotName].x;
      y1 += fromTerritoryData.unitSlotsBySlotName[unitSlotName].y;
    }
  }

  // Switch to find the receiver (i.e. destination) of arrow
  // And the receiver's width and height
  let x2;
  let y2;
  let receiverWidth;
  let receiverHeight;
  switch (receiverType) {
    case "arrow": {
      const [arrowX1, arrowY1, arrowX2, arrowY2] = receiverIdentifier as [
        number,
        number,
        number,
        number,
      ];
      const attachPoint = 0.75;
      const run = arrowX2 - arrowX1;
      const rise = arrowY2 - arrowY1;

      x2 = arrowX1 + run * attachPoint;
      y2 = arrowY1 + rise * attachPoint;
      receiverWidth = 0;
      receiverHeight = 0;
      positionChangeBuffer = 60;
      break;
    }
    case "unit": {
      const toTerritoryName = Territory[receiverIdentifier as Territory];
      const toTerritoryData = TerritoryMap[toTerritoryName].territoryMapData;
      const { unitSlotName } = TerritoryMap[toTerritoryName];

      x2 = toTerritoryData.x;
      y2 = toTerritoryData.y;
      if (toTerritoryData.unitSlotsBySlotName[unitSlotName]) {
        x2 += toTerritoryData.unitSlotsBySlotName[unitSlotName].x;
        y2 += toTerritoryData.unitSlotsBySlotName[unitSlotName].y;
      }
      receiverWidth = UNIT_WIDTH;
      receiverHeight = UNIT_HEIGHT;
      positionChangeBuffer = 75;
      break;
    }
    default: {
      const toTerritoryName = Territory[receiverIdentifier as Territory];
      const toTerritoryData = TerritoryMap[toTerritoryName].territoryMapData;

      x2 = toTerritoryData.x;
      y2 = toTerritoryData.y;
      if (toTerritoryData.arrowReceiver) {
        x2 += toTerritoryData.arrowReceiver.x;
        y2 += toTerritoryData.arrowReceiver.y;
      }
      receiverWidth = 0;
      receiverHeight = 0;
      positionChangeBuffer = 75;
      break;
    }
  }

  const xDiff = x2 - x1;
  const yDiff = y2 - y1;

  ({ x1, x2, y1, y2 } = arrowDispatchReceiveCoordinates(
    positionChangeBuffer,
    UNIT_HEIGHT,
    UNIT_WIDTH,
    receiverHeight,
    receiverWidth,
    xDiff,
    yDiff,
    x1,
    x2,
    y1,
    y2,
  ));

  return [x1, y1, x2, y2];
}

export default function drawArrowFunctional(
  arrowType: ArrowType,
  arrowColor: ArrowColor,
  sourceType: "unit",
  sourceIdentifier: Territory,
  receiverType: "territory" | "unit" | "arrow",
  receiverIdentifier: Territory | [number, number, number, number],
): React.ReactElement {
  console.log(
    `drawArrowFunctional ${sourceIdentifier} ${receiverType} ${receiverIdentifier} `,
  );
  const [x1, y1, x2, y2] = getArrowX1Y1X2Y2(
    sourceType,
    sourceIdentifier,
    receiverType,
    receiverIdentifier,
  );

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
