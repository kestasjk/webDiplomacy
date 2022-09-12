import React, { FunctionComponent, ReactElement } from "react";

import { ReactComponent as BtnActionButton } from "../../../../assets/svg/icons/btnPanel.svg";
import { ReactComponent as BtnPhaseButton } from "../../../../assets/svg/icons/phaseButton.svg";

interface RightButtonProps {
  image: string;
  text: string;
  onClick: () => void;
  className?: string;
}

const RightButton: FunctionComponent<RightButtonProps> = function ({
  image,
  text,
  onClick,
  className,
}): ReactElement {
  return (
    <div className={className}>
      <button onClick={onClick} type="button" className="w-full outline-0">
        {image === "action" ? (
          <BtnActionButton className="mx-auto" />
        ) : (
          <BtnPhaseButton className="mx-auto" />
        )}
      </button>
    </div>
  );
};

RightButton.defaultProps = { className: "" };

export default RightButton;
