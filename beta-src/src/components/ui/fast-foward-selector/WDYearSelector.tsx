import React, { useState } from "react";
import { scroller } from "react-scroll";

import Season from "../../../enums/Season";
import WDSeasonSelector from "./WDSeasonSelector";
import { ReactComponent as MiniArrowIcon } from "../../../assets/svg/miniArrow.svg";

const years = [
  1901, 1902, 1903, 1904, 1905, 1906, 1907, 1908, 1909, 1910, 1911, 1912, 1913,
  1914, 1915, 1916,
];
interface WDYearSelectorProps {
  defaultYear: number;
  defaultSeason: Season;
  onSelected: (season: Season, year: number) => void;
}

const WDYearSelector: React.FC<WDYearSelectorProps> = function ({
  defaultYear,
  defaultSeason,
  onSelected,
}): React.ReactElement {
  const [yearSelected, setYearSelected] = useState<number>(defaultYear);

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

  return (
    <div
      id="yearsContainer"
      className="h-[140px] bg-black w-full absolute bottom-0 left-0 items-center flex space-x-2 overflow-x-auto no-scrollbar px-12"
    >
      <ul className="flex space-x-2 items-center justify-center mt-[-20px] relative">
        {years.map((year, index) => (
          <li className={`year${year}Container`}>
            <WDSeasonSelector
              onSelected={(season: Season, y: number) => {
                setYearSelected(y);
                onSelected(season, y);
              }}
              year={year}
              yearSelected={yearSelected}
              defaultSeason={defaultSeason}
              version={year === yearSelected ? "rounded" : "square"}
              isFirstItem={index === 0}
              isLastItem={index === years.length - 1}
            />
          </li>
        ))}
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
            onClick={() => scrollToSection("year1916Container", 0)}
          >
            1916
            <MiniArrowIcon className="ml-1" />
          </button>
        </div>
      </div>
    </div>
  );
};

export default WDYearSelector;
