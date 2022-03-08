import * as React from "react";
import Button from "@mui/material/Button";

interface WDButtonProps {
  children: React.ReactNode;
  color?: "primary" | "secondary";
  disabled?: boolean;
  onClick?: React.MouseEventHandler<HTMLButtonElement> | undefined;
  startIcon?: React.ReactNode | undefined;
  variant?: "text" | "contained" | "outlined";
}

const WDButton: React.FC<WDButtonProps> = function ({
  children,
  color,
  disabled,
  onClick,
  startIcon,
  variant,
}): React.ReactElement {
  return (
    <Button
      color={color}
      disabled={disabled}
      onClick={onClick}
      startIcon={startIcon}
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
  variant: "contained",
};

export default WDButton;
