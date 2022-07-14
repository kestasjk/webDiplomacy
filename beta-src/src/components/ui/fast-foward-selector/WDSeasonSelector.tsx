/* eslint-disable no-nested-ternary */
import React, { useState, useRef, useEffect } from "react";
import Season from "../../../enums/Season";
import WDPhaseSelector from "./WDPhaseSelector";
import { ReactComponent as ThickArrowPhaseIcon } from "../../../assets/svg/thickArrowPhase.svg";

interface WDSeasonSelectorProps {
  onSelected: (season: Season, year: number) => void;
  yearSelected: number;
  year: number;
  defaultSeason: Season;
  version: string;
  isFirstItem: boolean;
  isLastItem: boolean;
}

const WDSeasonSelector: React.FC<WDSeasonSelectorProps> = function ({
  onSelected,
  year,
  yearSelected,
  defaultSeason,
  version,
  isFirstItem,
  isLastItem,
}): React.ReactElement {
  const [seasonSelected, setSeasonSelected] = useState<Season | null>(
    version === "rounded" ? defaultSeason : null,
  );
  const boxRef = useRef<any>();

  const getPosition = () => {
    // I have the filing that we will need this later. Living here just in case
    const x = boxRef?.current.offsetLeft;
  };

  return (
    <>
      <div
        ref={boxRef}
        className="text-white uppercase text-xss font-bold mb-4 text-center"
      >
        {year}
        {version === "rounded" ? (
          <span className="ml-1">{seasonSelected}</span>
        ) : (
          ""
        )}
      </div>
      <div
        className={`${
          version === "rounded" ? "px-5 space-x-2" : "px-0"
        }  items-center flex justify-center`}
      >
        {version === "rounded" && (
          <button
            disabled={isFirstItem}
            type="button"
            className="flex items-center"
          >
            <ThickArrowPhaseIcon
              className={`mr-1 ${isFirstItem ? "text-gray-800" : "text-white"}`}
            />
          </button>
        )}
        {(Object.keys(Season) as Array<keyof typeof Season>).map(
          (key, index) => (
            <WDPhaseSelector
              season={Season[key]}
              active={seasonSelected === Season[key] && year === yearSelected}
              onClick={(season: Season) => {
                getPosition();
                setSeasonSelected(season);
                onSelected(season, year);
              }}
              version={version}
              roundness={
                version === "rounded"
                  ? "rounded-full"
                  : index === 0
                  ? "rounded-l-lg"
                  : index === 2
                  ? "rounded-r-md"
                  : ""
              }
            />
          ),
        )}
        {version === "rounded" && (
          <button
            disabled={isLastItem}
            type="button"
            className="flex items-center"
          >
            <ThickArrowPhaseIcon
              className={`ml-1 scale-x-[-1] ${
                isLastItem ? "text-gray-800" : "text-white"
              }`}
            />
          </button>
        )}
      </div>
    </>
  );
};

export default WDSeasonSelector;
