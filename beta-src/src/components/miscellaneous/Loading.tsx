import React, { FC, ReactElement } from "react";
import dagger from "../../assets/png/dagger-pencil-01.png";
import pencil from "../../assets/png/dagger-pencil-02.png";
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
    <div className="popotota absolute w-full h-full loading bg-loading bg-contain z-[9999]">
      <div className="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-fit h-[440px] sm:h-[550px]">
        <img
          src={seasonIcons}
          alt="season icons"
          className="w-[70%] sm:w-full mx-auto"
        />
        <img
          src={dagger}
          alt="diplomacy icon"
          className="absolute top-0 w-[70%] sm:w-full left-1/2 transform -translate-x-1/2"
        />
        <img
          src={pencil}
          alt="diplomacy icon"
          className="absolute top-0 w-[70%] sm:w-full left-1/2 transform -translate-x-1/2"
        />
        <div className="absolute top-[15rem] w-full text-white text-center uppercase text-lg tracking-[0.5rem] font-medium">
          spring <br /> 1916
        </div>
        <div className="text-white absolute bottom-0 left-1/2 transform -translate-x-1/2">
          <WDLoadingBar percentage={percentage} />
          <div className="uppercase pt-4 text-center tracking-[0.5rem]">
            Loading
          </div>
        </div>
      </div>
    </div>
  );
};

export default WDLoading;
