import React, { FunctionComponent, ReactElement } from "react";
import Season from "../../../../enums/Season";
import { formatPhaseForDisplay } from "../../../../utils/formatPhaseForDisplay";
import { ReactComponent as AutumnIcon } from "../../../../assets/svg/phases/autumn.svg";
import { ReactComponent as SpringIcon } from "../../../../assets/svg/phases/spring.svg";
import { ReactComponent as WinterIcon } from "../../../../assets/svg/phases/winter.svg";

interface RightButtonProps {
  season: Season;
  text: string;
  onClick: () => void;
  className?: string;
  viewedPhase: string;
}

const RightButton: FunctionComponent<RightButtonProps> = function ({
  season,
  text,
  onClick,
  className,
  viewedPhase,
}): ReactElement {
  const iconClassName = "text-white mx-auto h-7";
  const formattedPhase = formatPhaseForDisplay(viewedPhase);

  return (
    <div className={className}>
      <div className="w-full text-center">
        <button
          onClick={onClick}
          type="button"
          className="bg-black w-12 h-12 rounded-full mx-auto"
        >
          {season === Season.AUTUMN && <AutumnIcon className={iconClassName} />}
          {season === Season.SPRING && <SpringIcon className={iconClassName} />}
          {season === Season.WINTER && <WinterIcon className={iconClassName} />}
        </button>
      </div>
      <div className="bg-black uppercase text-white text-center py-0.5 w-full text-xs font-bold rounded-md mt-1 px-2">
        {text}
        {formattedPhase ? formattedPhase.charAt(0) : ""}
      </div>
    </div>
  );
};

RightButton.defaultProps = { className: "" };

export default RightButton;
