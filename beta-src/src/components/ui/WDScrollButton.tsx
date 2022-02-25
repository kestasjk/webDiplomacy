import * as React from "react";
import Button from "@mui/material/Button";
import { scrollButtonProps } from "../../interfaces/PhaseScroll";
import WDPhaseArrowIcon from "../svgr-components/WDPhaseArrowIcon";

const WDScrollButton: React.FC<scrollButtonProps> = function ({
  onClick = undefined,
  direction,
  disabled = false,
}): React.ReactElement {
  return (
    <Button
      className="WDScrollButton"
      disabled={disabled}
      disableRipple
      onClick={onClick}
      variant="contained"
    >
      <WDPhaseArrowIcon direction={direction} />
    </Button>
  );
};

export default WDScrollButton;
