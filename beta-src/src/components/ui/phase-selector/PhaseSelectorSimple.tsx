import React, { ReactElement, FunctionComponent } from "react";
import { useAppDispatch, useAppSelector } from "../../../state/hooks";

import { ReactComponent as StepOneIcon } from "../../../assets/svg/icons/stepOne.svg";
import { ReactComponent as StepTwoIcon } from "../../../assets/svg/icons/stepTwo.svg";
import {
  gameApiSliceActions,
  gameViewedPhase,
} from "../../../state/game/game-api-slice";

interface PhaseSelectorSimpleProps {
  viewedSeason: string;
  viewedYear: number;
  viewedPhase: string;
  totalPhases: number;
}

const PhaseSelectorSimple: FunctionComponent<PhaseSelectorSimpleProps> =
  function ({
    viewedSeason,
    viewedYear,
    viewedPhase,
    totalPhases,
  }: PhaseSelectorSimpleProps): ReactElement {
    const { viewedPhaseIdx } = useAppSelector(gameViewedPhase);
    const dispatch = useAppDispatch();
    const notAllowed = "text-gray-600 cursor-not-allowed";
    const rightArrowsClassName =
      viewedPhaseIdx < totalPhases - 1 ? "text-white" : notAllowed;
    const leftArrowsClassName = `scale-x-[-1] ${
      viewedPhaseIdx === 0 ? notAllowed : "text-white"
    }`;

    return (
      <div
        className="has-tooltip bg-[#1C2B33] flex text-white items-center h-14 px-4 sm:px-8 rounded-full space-x-3 sm:space-x-8 z-20 select-none"
        title="Change phases with Shift-Left and Shift-Right."
      >
        <button
          type="button"
          className="h-full outline-0"
          onClick={() => dispatch(gameApiSliceActions.setViewedPhase(0))}
        >
          <StepTwoIcon className={leftArrowsClassName} />
        </button>
        <button
          type="button"
          className="h-full outline-0"
          onClick={() =>
            dispatch(gameApiSliceActions.changeViewedPhaseIdxBy(-1))
          }
        >
          <StepOneIcon className={leftArrowsClassName} />
        </button>
        <div className="text-center uppercase text-xs font-medium">
          <div>{viewedSeason}</div>
          <div>
            {viewedYear}
            {viewedPhase === "Retreats" ? " (R)" : ""}
          </div>
        </div>
        <button
          type="button"
          className="h-full outline-0"
          onClick={() =>
            dispatch(gameApiSliceActions.changeViewedPhaseIdxBy(1))
          }
        >
          <StepOneIcon className={rightArrowsClassName} />
        </button>
        <button
          type="button"
          className="h-full outline-0"
          onClick={() => dispatch(gameApiSliceActions.setViewedPhaseToLatest())}
        >
          <StepTwoIcon className={rightArrowsClassName} />
        </button>
      </div>
    );
  };

export default PhaseSelectorSimple;
