import * as React from "react";
import * as d3 from "d3";
import { useRef } from "react";
import Box from "@mui/material/Box";
import WDArrow from "./WDArrow";

interface WDArrowControllerProps {
  actionType: string;
  // containerElement: React.ReactNode;
  // isActive: boolean;
}

const WDArrowController: React.FC<WDArrowControllerProps> = function ({
  actionType,
  // containerElement,
  // isActive,
}) {
  const anchorEl = useRef(null);

  const actionTypeColors = {
    moveOrder: "#FFFFFF",
    move: "#000000",
    moveConvoy: "#2042B8",
    moveFailed: "#BB0000",
    moveSupport: "#F8F83D",
    holdSupport: "#3FC621",
    retreat: "#BD2894",
  };

  const arrowColor = actionTypeColors[actionType];

  return (
    <Box
      id="arrow-svg-container"
      sx={{
        position: "absolute",
      }}
    >
      <WDArrow color={arrowColor} />
    </Box>
  );
};

export default WDArrowController;
