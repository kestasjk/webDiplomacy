import * as React from "react";
import ArrowColor from "../../enums/ArrowColor";
import ArrowType from "../../enums/ArrowType";
import Territory from "../../enums/map/variants/classic/Territory";
import webDiplomacyTheme from "../../webDiplomacyTheme";
import arrowDispatchReceiveCoordinates from "./arrowDispatchReceiveCoordinates";
import TerritoryMap from "../../data/map/variants/classic/TerritoryMap";
import { UNIT_HEIGHT, UNIT_WIDTH } from "../../components/ui/units/WDUnit";

// Returns the coordinates of the upper left corner of the source or destination
// of an arrow, and the source or destination object's width and height.
// Type and identifier specify what object the arrow is pointing to, which is
// necessary for targeting and spacing the arrow appropriately.
// If type is "arrow", then identifier must be a 4-tuple of the coordinates
// [x1, y1, x2, y2] of that arrow.
// For all other types, identifier must be a Territory.
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
      // If the target of this arrow is itself an arrow, then target a point
      // attachPoint proportion of the way along that arrow.
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
    // If the target of this arrow is a unit, then (regardless of whether the
    // unit drawing code actually draws a unit there), draw an arrow to the
    // slot that the unit would be placed at, spaced appropriately for the size
    // that unit's icon would be.
    case "unit": {
      const toTerritoryName = Territory[identifier as Territory];
      const toTerritoryData = TerritoryMap[toTerritoryName].provinceMapData;
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
    // Units on retreat phases that are dislodging another unit are placed in
    // the arrow receiver slot rather than in the normal unit drawing slot.
    // This is so they don't overlap the drawing of the unit in that territory
    // that needs to perform a retreat.
    // So when drawing an arrow to a dislodger, we need to point at the
    // arrow receiver slot instead.
    case "dislodger": {
      const toTerritoryName = Territory[identifier as Territory];
      const toTerritoryData = TerritoryMap[toTerritoryName].provinceMapData;
      const { unitSlotName } = TerritoryMap[toTerritoryName];

      x = toTerritoryData.x;
      y = toTerritoryData.y;
      if (toTerritoryData.unitSlotsBySlotName[unitSlotName]) {
        x += toTerritoryData.unitSlotsBySlotName[unitSlotName].arrowReceiver.x;
        y += toTerritoryData.unitSlotsBySlotName[unitSlotName].arrowReceiver.y;
      }
      width = UNIT_WIDTH;
      height = UNIT_HEIGHT;
      break;
    }
    // Otherwise, draw an arrow pointing to the arrow receiver slot of the
    // territory. We still draw the arrow as if pointing to an object
    // of a small buffersize many pixels. That way, the arrows stop slightly short
    // of the exact target location. This looks a bit nicer when
    // multiple arrows point to the same territory - the arrow points
    // stop slightly short of each other instead of all overlapping.
    default: {
      const toTerritoryName = Territory[identifier as Territory];
      const toTerritoryData = TerritoryMap[toTerritoryName].provinceMapData;
      const { unitSlotName } = TerritoryMap[toTerritoryName];

      const bufferSize = 12;

      x = toTerritoryData.x;
      y = toTerritoryData.y;
      if (toTerritoryData.unitSlotsBySlotName[unitSlotName]) {
        x += toTerritoryData.unitSlotsBySlotName[unitSlotName].arrowReceiver.x;
        y += toTerritoryData.unitSlotsBySlotName[unitSlotName].arrowReceiver.y;
      }
      width = bufferSize;
      height = bufferSize;
      break;
    }
  }

  return [x, y, width, height];
}

// See getTargetXYWH for a description of the possible types and identifiers.
export function getArrowX1Y1X2Y2(
  sourceType: "territory" | "unit" | "arrow" | "dislodger",
  sourceIdentifier: Territory | [number, number, number, number],
  receiverType: "territory" | "unit" | "arrow" | "dislodger",
  receiverIdentifier: Territory | [number, number, number, number],
): [number, number, number, number] {
  // Source of arrow
  // eslint-disable-next-line prefer-const
  let [sx1, sy1, sourceWidth, sourceHeight] = getTargetXYWH(
    sourceType,
    sourceIdentifier,
  );
  const [rx2, ry2, receiverWidth, receiverHeight] = getTargetXYWH(
    receiverType,
    receiverIdentifier,
  );

  // Draw the arrows slightly closer to a unit than their nominal size for the arrow source, the portion of
  // the unit's nominal size that the unit icon actually covers is a bit smaller and
  // the arrow looks a bit too far away otherwise.
  const UNIT_SOURCE_SHRINK_FACTOR = 0.8;
  if (sourceType === "unit" || sourceType === "dislodger") {
    sourceWidth *= UNIT_SOURCE_SHRINK_FACTOR;
    sourceHeight *= UNIT_SOURCE_SHRINK_FACTOR;
  }

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

// See getTargetXYWH for a description of the possible types and identifiers.
export default function drawArrowFunctional(
  arrowType: ArrowType,
  arrowColor: ArrowColor,
  sourceType: "territory" | "unit" | "arrow" | "dislodger",
  sourceIdentifier: Territory | [number, number, number, number],
  receiverType: "territory" | "unit" | "arrow" | "dislodger",
  receiverIdentifier: Territory | [number, number, number, number],
  offsetArrowSourcePixels = 0.0,
): React.ReactElement {
  // console.log(
  //   `drawArrowFunctional ${sourceIdentifier} ${receiverType} ${receiverIdentifier} `,
  // );
  // eslint-disable-next-line prefer-const
  let [x1, y1, x2, y2] = getArrowX1Y1X2Y2(
    sourceType,
    sourceIdentifier,
    receiverType,
    receiverIdentifier,
  );

  let strokeDasharray: string | undefined;
  if (arrowType === ArrowType.CONVOY) {
    strokeDasharray = "4 3";
  } else if (arrowType === ArrowType.HOLD) {
    strokeDasharray = "12 3";
  }

  let strokeWidth;
  switch (arrowColor) {
    case ArrowColor.MOVE:
    case ArrowColor.CONVOY:
    case ArrowColor.IMPLIED:
    case ArrowColor.IMPLIED_FOREIGN:
    case ArrowColor.RETREAT:
    case ArrowColor.SUPPORT_HOLD:
    case ArrowColor.SUPPORT_MOVE:
      strokeWidth = 3.5;
      break;
    case ArrowColor.MOVE_FAILED:
    case ArrowColor.CONVOY_FAILED:
    case ArrowColor.SUPPORT_HOLD_FAILED:
    case ArrowColor.SUPPORT_MOVE_FAILED:
      strokeWidth = 3;
      break;
    default:
      strokeWidth = 3.5;
  }

  if (offsetArrowSourcePixels > 0) {
    // Offset the source location perpendicular to the direction of travel by
    // offsetArrowSourcePixels distance.
    // Start by finding a unit vector in the direction of the arrows
    const dx = x2 - x1;
    const dy = y2 - y1;
    const length = Math.sqrt(dx * dx + dy * dy);
    if (length > 0) {
      const dxunit = dx / length;
      const dyunit = dy / length;
      // Rotate 90 degrees
      const dxunitRotated = dyunit;
      const dyunitRotated = -dxunit;
      // Add the desired number of pixels
      x1 += offsetArrowSourcePixels * dxunitRotated;
      y1 += offsetArrowSourcePixels * dyunitRotated;
    }
  }

  return (
    <line
      key={`${x1}-${y1}-${x2}-${y2}-${arrowType}-${arrowColor}`}
      x1={x1}
      y1={y1}
      x2={x2}
      y2={y2}
      markerEnd={`url(#arrowHead__${ArrowType[arrowType]}_${ArrowColor[arrowColor]})`}
      stroke={webDiplomacyTheme.palette.arrowColors[arrowColor].main}
      strokeWidth={strokeWidth}
      strokeDasharray={strokeDasharray}
    />
  );
}
