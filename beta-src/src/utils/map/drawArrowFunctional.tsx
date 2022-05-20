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
  receiverIdentifier: Territory | React.ReactElement,
  unitTerritory: Territory,
): React.ReactElement {
  console.log(
    `drawArrowFunctional ${receiverType} ${receiverIdentifier} ${unitTerritory}`,
  );

  let positionChangeBuffer;

  // Source of arrow
  let x1;
  let y1;
  {
    const fromTerritoryName = Territory[unitTerritory];
    const fromTerritoryData = TerritoryMap[fromTerritoryName].territoryMapData;
    const { unitSlotName } = TerritoryMap[fromTerritoryName];
    const fromTerritoryX = fromTerritoryData.x;
    const fromTerritoryY = fromTerritoryData.y;
    let unitX = fromTerritoryX;
    let unitY = fromTerritoryY;
    if (fromTerritoryData.unitSlotsBySlotName[unitSlotName]) {
      unitX += fromTerritoryData.unitSlotsBySlotName[unitSlotName].x;
      unitY += fromTerritoryData.unitSlotsBySlotName[unitSlotName].y;
    }
    x1 = unitX;
    y1 = unitY;
  }

  // Switch to find the receiver (i.e. destination) of arrow
  // And the receiver's width and height
  let x2;
  let y2;
  let receiverWidth;
  let receiverHeight;
  switch (receiverType) {
    case "arrow": {
      const receiverArrow = receiverIdentifier as React.ReactElement;
      const attachPoint = 0.75;
      const arrowX1 = receiverArrow.props.x1;
      const arrowY1 = receiverArrow.props.y1;
      const arrowX2 = receiverArrow.props.x2;
      const arrowY2 = receiverArrow.props.y2;
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

      const toTerritoryX = toTerritoryData.x;
      const toTerritoryY = toTerritoryData.y;
      x2 = toTerritoryX;
      y2 = toTerritoryY;
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

      const toTerritoryX = toTerritoryData.x;
      const toTerritoryY = toTerritoryData.y;
      x2 = toTerritoryX;
      y2 = toTerritoryY;
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
