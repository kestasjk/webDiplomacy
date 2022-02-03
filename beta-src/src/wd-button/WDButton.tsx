import * as React from "react";
import Button from "@mui/material/Button";

interface WDButtonProps {
  children: React.ReactNode;
  color?: "primary" | "secondary";
  disabled?: boolean;
  onClick?: (event: React.MouseEvent<HTMLButtonElement>) => void;
}

const WDButton: React.FC<WDButtonProps> = function ({
  children,
  color,
  disabled,
  onClick,
}): React.ReactElement {
  return (
    <Button
      color={color}
      disabled={disabled}
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
};

export default WDButton;
