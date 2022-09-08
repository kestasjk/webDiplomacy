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

import {
  gameViewedPhase,
  gameApiSliceActions,
  gameOverview,
} from "../../../state/game/game-api-slice";
import { getGamePhaseSeasonYear } from "../../../utils/state/getPhaseSeasonYear";
import { formatPhaseForDisplay } from "../../../utils/formatPhaseForDisplay";

interface BottomMiddleProps {
  viewedSeason: Season;
  viewedYear: number;
  viewedPhase: string;
  totalPhases: number;
}

const NextPhase = function (): ReactElement {
  const { phase, season, year } = useAppSelector(gameOverview);
  const dispatch = useAppDispatch();

  const {
    phase: gamePhase,
    season: gameSeason,
    year: gameYear,
  } = getGamePhaseSeasonYear(phase, season, year);
  const formattedPhase = formatPhaseForDisplay(gamePhase);

  return (
    <div className="flex display-block px-5 sm:px-10 py-5 mt-1 bg-black rounded-xl text-white items-center select-none w-fit mb-3 mx-auto">
      <div>
        <div className="text-xs">New phase</div>
        <div className="text-sm font-bold uppercase">
          {gameSeason} {gameYear} {formattedPhase}
        </div>
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
  viewedPhase,
  totalPhases,
}: BottomMiddleProps): ReactElement {
  const { viewedPhaseIdx } = useAppSelector(gameViewedPhase);
  const { width } = useWindowSize();
  const [isNewPhase, setIsNewPhase] = useState<boolean>(true);
  const [lastViewedPhase, setLastViewedPhase] =
    useState<number>(viewedPhaseIdx);

  useEffect(() => {
    if (
      viewedPhaseIdx !== lastViewedPhase ||
      viewedPhaseIdx === totalPhases - 1
    ) {
      setIsNewPhase(false);
    }
    setLastViewedPhase(viewedPhaseIdx);
  }, [viewedPhaseIdx]);

  return (
    <WDPositionContainer
      position={Position.BOTTOM_MIDDLE}
      bottom={width < 500 ? 14 : 4}
    >
      <WDBuildCounts />
      {isNewPhase ? (
        <NextPhase />
      ) : (
        <PhaseSelectorSimple
          viewedSeason={viewedSeason}
          viewedYear={viewedYear}
          viewedPhase={viewedPhase}
          totalPhases={totalPhases}
        />
      )}
    </WDPositionContainer>
  );
};

export default BottomMiddle;
