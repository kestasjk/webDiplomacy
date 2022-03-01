import * as React from "react";
import Button from "@mui/material/Button";
import ScrollButtonState from "../../enums/ScrollButton";
import WDPhaseArrowIcon from "../svgr-components/WDPhaseArrowIcon";

interface scrollButtonProps {
  direction: ScrollButtonState;
  disabled?: boolean;
  onClick?: React.MouseEventHandler<HTMLButtonElement> | undefined;
}

const WDScrollButton: React.FC<scrollButtonProps> = function ({
  direction,
  disabled,
  onClick,
}): React.ReactElement {
  return (
    <Button
      className="WDScrollButton"
      disabled={disabled}
      disableRipple
      onClick={onClick}
      variant="contained"
      sx={{
        backgroundColor: "#fff",
        boxShadow: "none",
        borderRadius: "22px",
        padding: "13px 19px 13px 13px",
        "&:hover, &:focus": {
          backgroundColor: "#fff",
          boxShadow: "none",
        },
      }}
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
