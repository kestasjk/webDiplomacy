import * as React from "react";
import Box from "@mui/material/Box";
import { useMemo } from "react";
import Position from "../../enums/Position";

interface WDPositionContainerProps {
  children: React.ReactNode;
  position?: Position;
  bottom?: number;
}

// const MOBILE_DISTANCE = 16;
// const TABLET_UP_DISTANCE = 24;
const Z_INDEX = 2;

// const responsiveDistance = {
//   mobile: MOBILE_DISTANCE,
//   tablet: TABLET_UP_DISTANCE,
//   desktop: TABLET_UP_DISTANCE,
// } as const;

/**
 * This component is used to position a box and its children absolute
 */
const WDPositionContainer: React.FC<WDPositionContainerProps> = function ({
  children,
  position,
  bottom,
}): React.ReactElement {
  const placement = useMemo(() => {
    switch (position) {
      case Position.BOTTOM_LEFT:
        return `bottom-${bottom} left-3`;
      case Position.BOTTOM_RIGHT:
        return `bottom-${bottom} right-3`;
      case Position.TOP_RIGHT:
        return "right-3 top-3";
      case Position.TOP_LEFT:
      default:
        return "left-3 top-3";
    }
  }, [position, bottom]);

  return <div className={`absolute ${placement} z-20`}>{children}</div>;
};

WDPositionContainer.defaultProps = {
  position: Position.TOP_LEFT,
  bottom: 2,
};

export default WDPositionContainer;

// style={{
//   touchAction: "none",
//   zIndex: Z_INDEX,
//   pointerEvents: "none", // this component is for layout alone, it shouldn't mask out clicks behind it
//   ...placement,
// }}
