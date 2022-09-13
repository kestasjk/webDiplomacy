/* eslint-disable no-nested-ternary */
import React, { useState, useRef } from "react";
import Season from "../../../enums/Season";
import WDPhaseSelectorIcon from "./WDPhaseSelectorIcon";
import { useAppDispatch } from "../../../state/hooks";
import { gameApiSliceActions } from "../../../state/game/game-api-slice";
import { ReactComponent as ThickArrowPhaseIcon } from "../../../assets/svg/thickArrowPhase.svg";

interface WDPhaseSelectorGroupProps {
  onSelected: (season: Season, year: number) => void;
  yearSelected: number;
  year: number;
  defaultSeason: Season;
  version: string;
  isFirstItem: boolean;
  isLastItem: boolean;
  totalPhases: number;
}

const WDPhaseSelectorGroup: React.FC<WDPhaseSelectorGroupProps> = function ({
  onSelected,
  year,
  yearSelected,
  defaultSeason,
  version,
  isFirstItem,
  isLastItem,
  totalPhases,
}): React.ReactElement {
  const dispatch = useAppDispatch();
  const boxRef = useRef<any>();

  const getPosition = () => {
    // I have the filing that we will need this later. Living here just in case
    const x = boxRef?.current.offsetLeft;
  };

  const currentYear = year - 1901;

  return (
    <>
      <div
        ref={boxRef}
        className="text-white uppercase text-xss font-bold mb-4 text-center"
      >
        {year}
        {version === "rounded" ? (
          <span className="ml-1">{defaultSeason}</span>
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
            onClick={() =>
              dispatch(gameApiSliceActions.changeViewedPhaseIdxBy(-1))
            }
          >
            <ThickArrowPhaseIcon
              className={`mr-1 ${isFirstItem ? "text-gray-800" : "text-white"}`}
            />
          </button>
        )}
        {(Object.keys(Season) as Array<keyof typeof Season>).map(
          (key, index) => (
            <WDPhaseSelectorIcon
              key={key}
              season={Season[key]}
              active={defaultSeason === Season[key] && year === yearSelected}
              onClick={(season: Season) => {
                getPosition();
                onSelected(season, year);
                dispatch(
                  gameApiSliceActions.setViewedPhase(currentYear * 3 + index),
                );
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
              disabled={
                isLastItem && totalPhases + index < (currentYear + index) * 3
              }
            />
          ),
        )}
        {version === "rounded" && (
          <button
            disabled={isLastItem}
            type="button"
            className="flex items-center"
            onClick={() =>
              dispatch(gameApiSliceActions.changeViewedPhaseIdxBy(1))
            }
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

export default WDPhaseSelectorGroup;
