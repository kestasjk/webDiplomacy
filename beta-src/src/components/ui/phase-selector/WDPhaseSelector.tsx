import React from "react";
import { scroller } from "react-scroll";

import Season from "../../../enums/Season";
import WDPhaseSelectorSeasons from "./WDPhaseSelectorGroup";
import { ReactComponent as MiniArrowIcon } from "../../../assets/svg/miniArrow.svg";

interface WDPhaseSelectorProps {
  currentSeason: Season;
  currentYear: number;
  totalPhases: number;
  onSelected?: (season: Season, year: number) => void;
}

const WDPhaseSelector: React.FC<WDPhaseSelectorProps> = function ({
  currentSeason,
  currentYear,
  totalPhases,
  onSelected,
}): React.ReactElement {
  const scrollToSection = (whereTo: string, offset: number) => {
    scroller.scrollTo(whereTo, {
      containerId: "yearsContainer",
      duration: 1500,
      delay: 0,
      smooth: "easeInOutQuart",
      horizontal: true,
      offset,
    });
  };

  const totalYears = Math.ceil(totalPhases / 3);
  const years = Array.from(Array(totalYears), (_, index) => 1901 + index);

  return (
    <div
      id="yearsContainer"
      className="h-[140px] bg-black w-full absolute bottom-0 left-0 items-center flex space-x-2 overflow-x-auto no-scrollbar px-12"
    >
      <ul className="flex space-x-2 items-center justify-center mt-[-20px] relative">
        {years.map((year, index) => (
          <li className={`year${year}Container`}>
            <WDPhaseSelectorSeasons
              onSelected={(season: Season, y: number) => {
                if (onSelected) onSelected(season, y);
              }}
              year={year}
              yearSelected={currentYear}
              defaultSeason={currentSeason}
              version={year === currentYear ? "rounded" : "square"}
              isFirstItem={index === 0}
              isLastItem={index === years.length - 1}
              totalPhases={totalPhases}
            />
          </li>
        ))}
        <li className="lastYear" />
      </ul>
      <div className="flex text-gray-400 text-xss fixed bottom-3 w-full left-[-8px] px-7">
        <div className="flex-1">
          <button
            type="button"
            className="flex items-center"
            onClick={() => scrollToSection("year1901Container", -50)}
          >
            <MiniArrowIcon className="mr-1 scale-x-[-1]" />
            1901
          </button>
        </div>
        <div>
          <button
            type="button"
            className="flex items-center"
            onClick={() => scrollToSection("lastYear", 0)}
          >
            {1900 + totalYears}
            <MiniArrowIcon className="ml-1" />
          </button>
        </div>
      </div>
    </div>
  );
};

WDPhaseSelector.defaultProps = {
  onSelected: undefined,
};

export default WDPhaseSelector;
