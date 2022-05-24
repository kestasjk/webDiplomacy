import * as React from "react";
import ArrowColor from "../../enums/ArrowColor";
import ArrowType from "../../enums/ArrowType";
import Territory from "../../enums/map/variants/classic/Territory";
import webDiplomacyTheme from "../../webDiplomacyTheme";
import arrowDispatchReceiveCoordinates from "./arrowDispatchReceiveCoordinates";
import TerritoryMap from "../../data/map/variants/classic/TerritoryMap";
import { UNIT_HEIGHT, UNIT_WIDTH } from "../../components/ui/units/WDUnit";

// Returns the coordinates of the upper left corner
// of the source or destination of an arrow, and the source or destination
// object's width and height.
export function getTargetXYWH(
  type: "territory" | "unit" | "arrow" | "dislodger",
  identifier: Territory | [number, number, number, number],
): [number, number, number, number] {
  let x;
  let y;
  let width;
  let height;
  switch (type) {
    case "arrow": {
      const [arrowX1, arrowY1, arrowX2, arrowY2] = identifier as [
        number,
        number,
        number,
        number,
      ];
      const attachPoint = 0.75;
      const run = arrowX2 - arrowX1;
      const rise = arrowY2 - arrowY1;

      x = arrowX1 + run * attachPoint;
      y = arrowY1 + rise * attachPoint;
      width = 0;
      height = 0;
      break;
    }
    case "unit": {
      const toTerritoryName = Territory[identifier as Territory];
      const toTerritoryData = TerritoryMap[toTerritoryName].territoryMapData;
      const { unitSlotName } = TerritoryMap[toTerritoryName];

      x = toTerritoryData.x;
      y = toTerritoryData.y;
      if (toTerritoryData.unitSlotsBySlotName[unitSlotName]) {
        x += toTerritoryData.unitSlotsBySlotName[unitSlotName].x;
        y += toTerritoryData.unitSlotsBySlotName[unitSlotName].y;
      }
      width = UNIT_WIDTH;
      height = UNIT_HEIGHT;
      break;
    }
    case "dislodger": {
      const toTerritoryName = Territory[identifier as Territory];
      const toTerritoryData = TerritoryMap[toTerritoryName].territoryMapData;

      x = toTerritoryData.x - UNIT_WIDTH / 2;
      y = toTerritoryData.y - UNIT_HEIGHT / 2;
      if (toTerritoryData.arrowReceiver) {
        x += toTerritoryData.arrowReceiver.x;
        y += toTerritoryData.arrowReceiver.y;
      }
      width = UNIT_WIDTH;
      height = UNIT_HEIGHT;
      break;
    }
    default: {
      const toTerritoryName = Territory[identifier as Territory];
      const toTerritoryData = TerritoryMap[toTerritoryName].territoryMapData;

      x = toTerritoryData.x;
      y = toTerritoryData.y;
      if (toTerritoryData.arrowReceiver) {
        x += toTerritoryData.arrowReceiver.x;
        y += toTerritoryData.arrowReceiver.y;
      }
      width = 0;
      height = 0;
      break;
    }
  }
  return [x, y, width, height];
}

export function getArrowX1Y1X2Y2(
  sourceType: "territory" | "unit" | "arrow" | "dislodger",
  sourceIdentifier: Territory | [number, number, number, number],
  receiverType: "territory" | "unit" | "arrow" | "dislodger",
  receiverIdentifier: Territory | [number, number, number, number],
): [number, number, number, number] {
  // Source of arrow
  const [sx1, sy1, sourceWidth, sourceHeight] = getTargetXYWH(
    sourceType,
    sourceIdentifier,
  );
  const [rx2, ry2, receiverWidth, receiverHeight] = getTargetXYWH(
    receiverType,
    receiverIdentifier,
  );

  const { x1, x2, y1, y2 } = arrowDispatchReceiveCoordinates(
    sourceHeight,
    sourceWidth,
    receiverHeight,
    receiverWidth,
    sx1,
    rx2,
    sy1,
    ry2,
  );

  return [x1, y1, x2, y2];
}

export default function drawArrowFunctional(
  arrowType: ArrowType,
  arrowColor: ArrowColor,
  sourceType: "territory" | "unit" | "arrow" | "dislodger",
  sourceIdentifier: Territory | [number, number, number, number],
  receiverType: "territory" | "unit" | "arrow" | "dislodger",
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
      key={`${x1}-${y1}-${x2}-${y2}-${arrowType}-${arrowColor}`}
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
