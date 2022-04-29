import * as React from "react";
import ArrowType from "../../enums/ArrowType";
import webDiplomacyTheme from "../../webDiplomacyTheme";

const WDArrowMarkerDefs = function (): React.ReactElement[] {
  return Object.entries(webDiplomacyTheme.palette.arrowColors).map(
    ([arrowType, config]) => {
      switch (arrowType) {
        case ArrowType.MOVE_SUPPORT:
          return (
            <marker
              id={`arrowHead__${arrowType}`}
              markerWidth={8}
              markerHeight={8}
              refX="2%"
              refY={4}
              orient="auto"
            >
              <polygon
                points="0 0, 8 4, 0 8, 0 7, 6 4, 0 1"
                fill={config.main}
              />
            </marker>
          );
        case ArrowType.HOLD_SUPPORT:
          return (
            <marker
              id={`arrowHead__${arrowType}`}
              markerWidth={8}
              markerHeight={8}
              refX="2%"
              refY={4}
              orient="auto"
            >
              <polygon points="0 0, 0 8, 1 8, 1 0" fill={config.main} />
            </marker>
          );
        default:
          return (
            <marker
              id={`arrowHead__${arrowType}`}
              markerWidth={8}
              markerHeight={8}
              refX={arrowType === ArrowType.FOREIGN_IMPLIED ? 0 : 7.1}
              refY={4}
              orient="auto"
            >
              <polygon points="0 0, 8 4, 0 8" fill={config.main} />
            </marker>
          );
      }
    },
  );
};

export default WDArrowMarkerDefs;
