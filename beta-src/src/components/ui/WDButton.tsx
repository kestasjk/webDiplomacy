import * as React from "react";
import Button from "@mui/material/Button";

interface WDButtonProps {
  children: React.ReactNode;
  color?: "primary" | "secondary";
  disabled?: boolean;
  onClick?: React.MouseEventHandler<HTMLButtonElement> | undefined;
  startIcon?: React.ReactNode | undefined;
}

const WDButton: React.FC<WDButtonProps> = function ({
  children,
  color,
  disabled,
  onClick,
  startIcon,
}): React.ReactElement {
  return (
    <Button
      color={color}
      disabled={disabled}
      startIcon={startIcon}
      onClick={onClick}
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
};

export default WDButton;
