import * as React from "react";
import Button from "@mui/material/Button";
import ScrollButtonState from "../../enums/ScrollButton";
import WDPhaseArrowIcon from "./icons/WDPhaseArrowIcon";

interface ScrollButtonProps {
  className: string;
  direction: ScrollButtonState;
  disabled?: boolean;
  doAnimateGlow?: boolean;
  onClick?: React.MouseEventHandler<HTMLButtonElement> | undefined;
}

const WDScrollButton: React.FC<ScrollButtonProps> = function ({
  className,
  direction,
  disabled,
  doAnimateGlow,
  onClick,
}): React.ReactElement {
  return (
    <Button
      sx={{
        backgroundColor: "secondary.main",
        boxShadow: "none",
        borderRadius: "22px",
        padding: "13px 19px 12px 13px",
        "&:hover, &:focus, &:disabled": {
          backgroundColor: "#fff",
          boxShadow: "none",
        },
        animation:
          doAnimateGlow && !disabled ? "scrollglowing 1.5s ease infinite" : "",
        pointerEvents: "auto",
      }}
      className={className}
      disabled={disabled}
      disableRipple
      onClick={onClick}
      variant="contained"
    >
      <style>
        {`
        @keyframes scrollglowing {
          0% {
            background-color: #66ff55;
          }
          50% {
            background-color: #ffffff;
          }
          100% {
            background-color: #66ff55;
          }
        }`}
      </style>
      <WDPhaseArrowIcon direction={direction} disabled={disabled} />
    </Button>
  );
};

WDScrollButton.defaultProps = {
  disabled: false,
  doAnimateGlow: false,
  onClick: undefined,
};

export default WDScrollButton;
