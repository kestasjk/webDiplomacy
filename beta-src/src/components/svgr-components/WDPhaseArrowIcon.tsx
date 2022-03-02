import * as React from "react";
import ScrollButtonState from "../../enums/ScrollButton";

interface phaseArrowProps {
  direction: ScrollButtonState;
  disabled?: boolean;
}

const WDPhaseArrowIcon: React.FC<phaseArrowProps> = function ({
  disabled,
  direction,
}): React.ReactElement {
  return (
    <svg fill="none" height={15} width={8} xmlns="http://www.w3.org/2000/svg">
      {direction === ScrollButtonState.BACKWARD && (
        <path d="M0 7.5 8 0v15z" fill="#000" opacity={disabled ? "40%" : ""} />
      )}
      {direction === ScrollButtonState.FORWARD && (
        <path
          d="m0 0 8 7.5L0 15z"
          fill="#000"
          opacity={disabled ? "40%" : ""}
        />
      )}
    </svg>
  );
};

WDPhaseArrowIcon.defaultProps = {
  disabled: false,
};

export default WDPhaseArrowIcon;
