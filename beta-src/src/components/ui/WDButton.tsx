import * as React from "react";
import Button from "@mui/material/Button";
import { SxProps } from "@mui/material";

interface WDButtonProps {
  children: React.ReactNode;
  color?: "primary" | "secondary";
  disabled?: boolean;
  onClick?: React.MouseEventHandler<HTMLButtonElement> | undefined;
  startIcon?: React.ReactNode | undefined;
  sx?: SxProps | undefined;
  doAnimateGlow?: boolean;
}

const WDButton: React.FC<WDButtonProps> = function ({
  children,
  color,
  disabled,
  onClick,
  startIcon,
  doAnimateGlow,
  sx,
}): React.ReactElement {
  return (
    <Button
      sx={sx}
      color={color}
      disabled={disabled}
      onClick={onClick}
      startIcon={startIcon}
      variant="contained"
      style={{
        animation:
          doAnimateGlow && !disabled ? "glowing 1.5s linear infinite" : "",
      }}
    >
      <style>
        {`
        @keyframes glowing {
          0% {
            background-color: #000000;
            box-shadow: 0 0 5px #000000;
          }
          50% {
            background-color: #447733;
            box-shadow: 0 0 15px #447733;
          }
          100% {
            background-color: #000000;
            box-shadow: 0 0 5px #000000;
          }
        }`}
      </style>
      {children}
    </Button>
  );
};

WDButton.defaultProps = {
  color: "primary",
  disabled: false,
  onClick: undefined,
  startIcon: undefined,
  sx: undefined,
  doAnimateGlow: false,
};

export default WDButton;
