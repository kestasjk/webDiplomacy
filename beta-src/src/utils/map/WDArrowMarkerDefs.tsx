import * as React from "react";
import ArrowType from "../../enums/ArrowType";
import webDiplomacyTheme from "../../webDiplomacyTheme";

const WDArrowMarkerDefs = function (): React.ReactElement[] {
  return Object.entries(webDiplomacyTheme.palette.arrowColors).map(
    ([arrowType, config]) => {
      switch (arrowType) {
        case ArrowType.HOLD_SUPPORT:
          return (
            <marker
              id={`arrowHead__${arrowType}`}
              markerWidth={8}
              markerHeight={8}
              refX={11}
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
              refX={7.1}
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
