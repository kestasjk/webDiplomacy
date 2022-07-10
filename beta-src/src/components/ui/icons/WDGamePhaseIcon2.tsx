import React, { useEffect, useState } from "react";
import UIState from "../../../enums/UIState";
import Season from "../../../enums/Season";
import { ReactComponent as AutumnIcon } from "../../../assets/svg/phases/autumn.svg";
import { ReactComponent as SpringIcon } from "../../../assets/svg/phases/spring.svg";
import { ReactComponent as WinterIcon } from "../../../assets/svg/phases/winter.svg";

interface GamePhaseIconProps {
  active?: boolean;
  disabled?: boolean;
  season: Season;
  onClick: (season: Season) => void;
  year: number;
}

const WDGamePhaseIcon: React.FC<GamePhaseIconProps> = function ({
  active,
  disabled,
  season,
  onClick,
  year,
}): React.ReactElement {
  const [className, setClassName] = useState<string>("");
  const [containerBgColor, setContainerBgColor] = useState<string>("[#3D3D3D]");

  useEffect(() => {
    let color = "white";
    let tempContainerBgColor = containerBgColor;
    if (!active) {
      color = "[#8F8F8F]";
      tempContainerBgColor = "[#1F1F1F]";
    }
    if (disabled) {
      color = "[#272727]";
      tempContainerBgColor = "[#0F0F0F]";
    }

    setContainerBgColor(tempContainerBgColor);
    setClassName(`text-${color} mx-auto ${active ? "h-7" : "h-5"}`);
  }, [active, disabled]);

  return (
    <div className="items-center justify-center">
      {active && (
        <div className="text-white uppercase text-xss font-bold mb-2 text-center">
          {year}
        </div>
      )}
      <button
        type="button"
        disabled={disabled}
        onClick={() => onClick(season)}
        className={`${active ? "w-12 h-12 bg-[#3D3D3D]" : "w-10 h-10"}
        } rounded-full bg-${containerBgColor} text-center items-center ${
          disabled ? "cursor-not-allowed" : "hover:bg-[#3D3D3D]"
        }`}
      >
        {season === Season.AUTUMN && <AutumnIcon className={className} />}
        {season === Season.SPRING && <SpringIcon className={className} />}
        {season === Season.WINTER && <WinterIcon className={className} />}
      </button>
      {active && (
        <div className="text-white uppercase text-xss mt-3 font-bold text-center">
          {season.toString()}
        </div>
      )}
      <div className="hidden bg-[#0F0F0F] bg-[#3D3D3D] text-[#272727] bg-[#1F1F1F] text-[#8F8F8F] h-12 h-10 w-12 w-10" />
    </div>
  );
};

WDGamePhaseIcon.defaultProps = {
  active: false,
  disabled: false,
};

export default WDGamePhaseIcon;
