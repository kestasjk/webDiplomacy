import * as React from "react";
import ArrowColor from "../../enums/ArrowColor";
import ArrowType from "../../enums/ArrowType";
import webDiplomacyTheme from "../../webDiplomacyTheme";

const WDArrowMarkerColors = function (
  arrowType: ArrowType,
): React.ReactElement {
  switch (arrowType) {
    case ArrowType.SUPPORT:
      return (
        <>
          {Object.entries(webDiplomacyTheme.palette.arrowColors).map(
            ([arrowColor, config]) => (
              <marker
                id={`arrowHead__${ArrowType[arrowType]}_${ArrowColor[arrowColor]}`}
                key={`arrowHead__${ArrowType[arrowType]}_${ArrowColor[arrowColor]}`}
                markerWidth={8}
                markerHeight={8}
                refX="16"
                refY={4}
                orient="auto"
              >
                <polygon
                  points="0 0, 8 4, 0 8, 0 7, 6 4, 0 1"
                  fill={config.main}
                />
              </marker>
            ),
          )}
        </>
      );
    case ArrowType.HOLD:
      return (
        <>
          {Object.entries(webDiplomacyTheme.palette.arrowColors).map(
            ([arrowColor, config]) => (
              <marker
                id={`arrowHead__${ArrowType[arrowType]}_${ArrowColor[arrowColor]}`}
                key={`arrowHead__${ArrowType[arrowType]}_${ArrowColor[arrowColor]}`}
                markerWidth={30}
                markerHeight={30}
                refX={5}
                refY={15}
                orient="auto"
              >
                <path
                  d=" M 9 22 A 10 10 180 0 1 9 8"
                  stroke={config.main}
                  strokeWidth={1}
                />
              </marker>
            ),
          )}
        </>
      );
    default:
      return (
        <>
          {Object.entries(webDiplomacyTheme.palette.arrowColors).map(
            ([arrowColor, config]) => (
              <marker
                id={`arrowHead__${ArrowType[arrowType]}_${ArrowColor[arrowColor]}`}
                key={`arrowHead__${ArrowType[arrowType]}_${ArrowColor[arrowColor]}`}
                markerWidth={8}
                markerHeight={8}
                refX={
                  (arrowColor as unknown as number) ===
                  ArrowColor.IMPLIED_FOREIGN
                    ? 0
                    : 7.1
                }
                refY={4}
                orient="auto"
              >
                <polygon points="0 0, 8 4, 0 8" fill={config.main} />
              </marker>
            ),
          )}
        </>
      );
  }
};

const WDArrowMarkerDefs = function (): React.ReactElement {
  return (
    <>
      {WDArrowMarkerColors(ArrowType.HOLD)}
      {WDArrowMarkerColors(ArrowType.MOVE)}
      {WDArrowMarkerColors(ArrowType.SUPPORT)}
    </>
  );
};

export default WDArrowMarkerDefs;
