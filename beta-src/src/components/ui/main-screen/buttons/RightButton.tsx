import React, { FunctionComponent, ReactElement } from "react";

import { ReactComponent as BtnActionButton } from "../../../../assets/svg/icons/actionButton.svg";
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
      <button onClick={onClick} type="button" className="w-full">
        {image === "action" ? (
          <BtnActionButton className="mx-auto" />
        ) : (
          <BtnPhaseButton className="mx-auto" />
        )}
      </button>
      <div className="bg-black uppercase text-white text-center py-0.5 w-full text-xs font-bold rounded-md">
        {text}
      </div>
    </div>
  );
};

RightButton.defaultProps = { className: "" };

export default RightButton;
