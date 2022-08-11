import React, {
  ReactElement,
  FunctionComponent,
  useState,
  useEffect,
} from "react";
import wait from "waait";
import { useAppSelector, useAppDispatch } from "../../../state/hooks";
import Position from "../../../enums/Position";

import WDBuildCounts from "../WDBuildCounts";
import Season from "../../../enums/Season";
import WDPositionContainer from "../WDPositionContainer";
import { ReactComponent as BtnArrowIcon } from "../../../assets/svg/btnArrow.svg";

import {
  gameViewedPhase,
  gameStatus,
  gameApiSliceActions,
} from "../../../state/game/game-api-slice";

interface BottomMiddleProps {
  phaseSelectorOpen: boolean;
  viewedSeason: Season;
  viewedYear: number;
}

const BottomMiddle: FunctionComponent<BottomMiddleProps> = function ({
  phaseSelectorOpen,
  viewedSeason,
  viewedYear,
}: BottomMiddleProps): ReactElement {
  const [nextPhase, setNextPhase] = useState<any>("");
  const { viewedPhaseIdx } = useAppSelector(gameViewedPhase);
  const gameStatusData = useAppSelector(gameStatus);
  const dispatch = useAppDispatch();

  // eslint-disable-next-line consistent-return
  const getNextPhase = () => {
    if (viewedSeason === Season.SPRING) {
      return `Autumn ${viewedYear}`;
    }
    if (viewedSeason === Season.AUTUMN) {
      return `Winter ${viewedYear}`;
    }
    if (viewedSeason === Season.WINTER) {
      return `Spring ${viewedYear + 1}`;
    }
  };

  useEffect(() => {
    if (
      viewedPhaseIdx < gameStatusData.phases.length - 1 &&
      !phaseSelectorOpen
    ) {
      setNextPhase(getNextPhase());
    }
  }, [viewedPhaseIdx, gameStatusData.phases, phaseSelectorOpen]);

  return (
    <WDPositionContainer
      position={Position.BOTTOM_MIDDLE}
      bottom={phaseSelectorOpen ? 40 : 4}
    >
      <WDBuildCounts />
      {viewedPhaseIdx < gameStatusData.phases.length - 1 && !phaseSelectorOpen && (
        <div className="flex display-block px-5 sm:px-10 py-5 mt-1 bg-black rounded-xl text-white items-center select-none">
          <div>
            <div className="text-xs">Next phase</div>
            <div className="text-sm font-bold uppercase">{nextPhase}</div>
          </div>
          <div className="ml-4">
            <button
              type="button"
              onClick={async () => {
                dispatch(gameApiSliceActions.changeViewedPhaseIdxBy(1));
              }}
            >
              <BtnArrowIcon
                className="text-white stroke-black cursor-pointer rotate-90"
                onClick={() =>
                  dispatch(gameApiSliceActions.changeViewedPhaseIdxBy(1))
                }
              />
            </button>
          </div>
        </div>
      )}
    </WDPositionContainer>
  );
};

export default BottomMiddle;
