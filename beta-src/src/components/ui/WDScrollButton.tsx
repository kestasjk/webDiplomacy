import * as React from "react";
import Button from "@mui/material/Button";
import ScrollButtonState from "../../enums/ScrollButton";
import WDPhaseArrowIcon from "../svgr-components/WDPhaseArrowIcon";

interface ScrollButtonProps {
  className: string;
  direction: ScrollButtonState;
  disabled?: boolean;
  onClick?: React.MouseEventHandler<HTMLButtonElement> | undefined;
}

const WDScrollButton: React.FC<ScrollButtonProps> = function ({
  className,
  direction,
  disabled,
  onClick,
}): React.ReactElement {
  return (
    <Button
      sx={{
        backgroundColor: "secondary.main",
        boxShadow: "none",
        borderRadius: "22px",
        padding: "13px 19px 13px 13px",
        "&:hover, &:focus, &:disabled": {
          backgroundColor: "#fff",
          boxShadow: "none",
        },
      }}
      disabled={disabled}
      disableRipple
      onClick={onClick}
      variant="contained"
    >
      <WDPhaseArrowIcon direction={direction} disabled={disabled} />
    </Button>
  );
};

WDScrollButton.defaultProps = {
  disabled: false,
  onClick: undefined,
};

export default WDScrollButton;
