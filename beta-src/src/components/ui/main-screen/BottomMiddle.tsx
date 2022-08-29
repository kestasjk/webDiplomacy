import React, {
  ReactElement,
  FunctionComponent,
  useState,
  useEffect,
} from "react";
import { useWindowSize } from "react-use";
import { useAppSelector, useAppDispatch } from "../../../state/hooks";
import Position from "../../../enums/Position";

import WDBuildCounts from "../WDBuildCounts";
import Season from "../../../enums/Season";
import WDPositionContainer from "../WDPositionContainer";
import PhaseSelectorSimple from "../phase-selector/PhaseSelectorSimple";
import { ReactComponent as BtnArrowIcon } from "../../../assets/svg/btnArrow.svg";
import useSettings from "../../../hooks/useSettings";

import {
  gameViewedPhase,
  gameStatus,
  gameApiSliceActions,
} from "../../../state/game/game-api-slice";

interface BottomMiddleProps {
  viewedSeason: Season;
  viewedYear: number;
  totalPhases: number;
}

interface NextPhaseProps {
  viewedSeason: Season;
  viewedYear: number;
}

const NextPhase = function ({
  viewedSeason,
  viewedYear,
}: NextPhaseProps): ReactElement {
  const [nextPhase, setNextPhase] = useState<any>("");
  const { viewedPhaseIdx } = useAppSelector(gameViewedPhase);
  const gameStatusData = useAppSelector(gameStatus);
  const dispatch = useAppDispatch();
  const { setSetting } = useSettings();

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
    if (viewedPhaseIdx < gameStatusData.phases.length - 1) {
      setNextPhase(getNextPhase());
    }
  }, [viewedPhaseIdx, gameStatusData.phases]);

  return (
    <div className="flex display-block px-5 sm:px-10 py-5 mt-1 bg-black rounded-xl text-white items-center select-none w-fit mb-3 mx-auto">
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
            onClick={() => {
              dispatch(gameApiSliceActions.changeViewedPhaseIdxBy(1));
              setSetting("lastPhaseClicked", viewedPhaseIdx);
            }}
          />
        </button>
      </div>
    </div>
  );
};

const BottomMiddle: FunctionComponent<BottomMiddleProps> = function ({
  viewedSeason,
  viewedYear,
  totalPhases,
}: BottomMiddleProps): ReactElement {
  const { viewedPhaseIdx } = useAppSelector(gameViewedPhase);
  const { width } = useWindowSize();
  const { settings } = useSettings();

  return (
    <WDPositionContainer
      position={Position.BOTTOM_MIDDLE}
      bottom={width < 500 ? 14 : 4}
    >
      <WDBuildCounts />
      {viewedPhaseIdx === totalPhases - 2 &&
      viewedPhaseIdx > settings.lastPhaseClicked ? (
        <NextPhase viewedSeason={viewedSeason} viewedYear={viewedYear} />
      ) : (
        <PhaseSelectorSimple
          viewedSeason={viewedSeason}
          viewedYear={viewedYear}
          totalPhases={totalPhases}
        />
      )}
    </WDPositionContainer>
  );
};

export default BottomMiddle;
