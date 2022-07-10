import React, { useState } from "react";
import Season from "../../enums/Season";
import WDGamePhaseIcon from "./icons/WDGamePhaseIcon2";
import { ReactComponent as MiniArrowIcon } from "../../assets/svg/miniArrow.svg";

interface WDSeasonSelectorProps {
  onSelected: (season: Season, year: number) => void;
  year: number;
  defaultSeason: Season;
}

const years = [
  1901, 1902, 1903, 1904, 1905, 1906, 1907, 1908, 1909, 1910, 1911, 1912, 1913,
  1914, 1915, 1916,
];

const WDSeasonSelector: React.FC<WDSeasonSelectorProps> = function ({
  onSelected,
  year,
  defaultSeason,
}): React.ReactElement {
  const [selected, setSelected] = useState<Season>(defaultSeason);

  return (
    <div className="px-8 items-center flex space-x-2 justify-center pt-4">
      {(Object.keys(Season) as Array<keyof typeof Season>).map((key) => (
        <WDGamePhaseIcon
          season={Season[key]}
          active={selected === Season[key]}
          year={year}
          onClick={(season: Season) => {
            setSelected(season);
            onSelected(season, year);
          }}
        />
      ))}
    </div>
  );
};

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

  return (
    <div className="h-40 bg-black w-full absolute bottom-0 left-0 items-center flex space-x-2 justify-center overflow-x-auto no-scrollbar px-12">
      <ul className="flex space-x-2 items-center justify-center mt-[-10px]">
        {years.map((year) => (
          <li>
            {year === yearSelected ? (
              <WDSeasonSelector
                onSelected={onSelected}
                year={year}
                defaultSeason={defaultSeason}
              />
            ) : (
              <button type="button" onClick={() => setYearSelected(year)}>
                <div className="text-white text-xs mb-2 text-left">{year}</div>
                <div className="w-24 h-16 bg-[#171717] rounded-md" />
              </button>
            )}
          </li>
        ))}
      </ul>
      <div className="flex text-gray-400 text-xss fixed bottom-3 w-full left-[-8px] px-7">
        <div className="flex-1">
          <button type="button" className="flex items-center">
            <MiniArrowIcon className="mr-1 scale-x-[-1]" />
            1901
          </button>
        </div>
        <div>
          <button type="button" className="flex items-center">
            1916
            <MiniArrowIcon className="ml-1" />
          </button>
        </div>
      </div>
    </div>
  );
};

export default WDYearSelector;
