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
}

const WDButton: React.FC<WDButtonProps> = function ({
  children,
  color,
  disabled,
  onClick,
  startIcon,
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
    >
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
};

export default WDButton;
