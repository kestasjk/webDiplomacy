import * as React from "react";
import Button from "@mui/material/Button";

interface WDButtonProps {
  children: React.ReactNode;
  color?: "primary" | "secondary";
  disabled?: boolean;
  onClick?: React.MouseEventHandler<HTMLButtonElement> | undefined;
  sx?: React.CSSProperties | undefined;
  variant?: "text" | "contained" | "outlined" | undefined;
}

const WDButton: React.FC<WDButtonProps> = function ({
  children,
  color,
  disabled,
  onClick,
  sx,
  variant,
}): React.ReactElement {
  return (
    <Button
      sx={sx}
      color={color}
      disabled={disabled}
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
  sx: undefined,
  variant: undefined,
};

export default WDButton;
