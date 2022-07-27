import React, { FC, ReactElement } from "react";
import diplomacyIcon from "../../assets/png/diplomacy-icon.png";
import seasonIcons from "../../assets/png/season-icons.png";

interface WDUIProps {
  percentage: number;
}

interface WDUIProps2 {
  percentage: number;
}

const WDLoadingBar: FC<WDUIProps2> = function ({ percentage }): ReactElement {
  return (
    <div className="h-[7px] w-[200px] bg-[#565656] rounded-full">
      <div
        className="h-[7px] rounded-full bg-gradient-to-r from-[#323E34] via-[#C8B897] to-white"
        style={{ width: `${percentage}%` }}
      />
    </div>
  );
};

const WDLoading: FC<WDUIProps> = function ({ percentage }): ReactElement {
  return (
    <div className="absolute w-full z-30 h-full loading bg-loading bg-contain z-[9999]">
      <div className="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-fit h-[550px]">
        <img src={seasonIcons} alt="season icons" />
        <img
          src={diplomacyIcon}
          alt="diplomacy icon"
          className="absolute top-0"
        />
        <div className="text-white absolute bottom-0 left-1/2 transform -translate-x-1/2">
          <div className="uppercase pb-4 text-center">Loading</div>
          <WDLoadingBar percentage={percentage} />
        </div>
      </div>
    </div>
  );
};

export default WDLoading;
