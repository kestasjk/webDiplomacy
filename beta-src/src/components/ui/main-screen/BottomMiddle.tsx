import React, { ReactElement, FunctionComponent } from "react";
import { useAppSelector, useAppDispatch } from "../../../state/hooks";
import Position from "../../../enums/Position";
import WDBuildCounts from "../WDBuildCounts";
import WDPositionContainer from "../WDPositionContainer";
import { ReactComponent as BtnArrowIcon } from "../../../assets/svg/btnArrow.svg";

import {
  gameViewedPhase,
  gameStatus,
  gameApiSliceActions,
} from "../../../state/game/game-api-slice";

interface BottomMiddleProps {
  phaseSelectorOpen: boolean;
}

const BottomMiddle: FunctionComponent<BottomMiddleProps> = function ({
  phaseSelectorOpen,
}: BottomMiddleProps): ReactElement {
  const { viewedPhaseIdx } = useAppSelector(gameViewedPhase);
  const gameStatusData = useAppSelector(gameStatus);
  const dispatch = useAppDispatch();

  return (
    <WDPositionContainer
      position={Position.BOTTOM_MIDDLE}
      bottom={phaseSelectorOpen ? 40 : 4}
    >
      <WDBuildCounts />
      {!(viewedPhaseIdx === gameStatusData.phases.length - 1) && (
        <div className="flex display-block px-10 py-5 mt-1 bg-black rounded-xl text-white items-center">
          <div>
            <div className="text-xs">Next phase</div>
            <div className="text-sm font-bold uppercase">winter 1916</div>
          </div>
          <div className="ml-4">
            <button
              type="button"
              onClick={() =>
                dispatch(gameApiSliceActions.changeViewedPhaseIdxBy(1))
              }
            >
              <BtnArrowIcon className="text-white stroke-black cursor-not-allowed rotate-90" />
            </button>
          </div>
        </div>
      )}
    </WDPositionContainer>
  );
};

export default BottomMiddle;
