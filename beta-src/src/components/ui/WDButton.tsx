import * as React from "react";
import Button from "@mui/material/Button";

interface WDButtonProps {
  children: React.ReactNode;
  color?: "primary" | "secondary";
  disabled?: boolean;
  onClick?: React.MouseEventHandler<HTMLButtonElement> | undefined;
  startIcon?: React.ReactNode | undefined;
  sx?: React.CSSProperties | undefined;
  variant?: "text" | "contained" | "outlined";
}

const WDButton: React.FC<WDButtonProps> = function ({
  children,
  color,
  disabled,
  onClick,
  startIcon,
  sx,
  variant,
}): React.ReactElement {
  return (
    <Button
      sx={sx}
      color={color}
      disabled={disabled}
      startIcon={startIcon}
      onClick={onClick}
      variant={variant}
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
  variant: "contained",
};

export default WDButton;
