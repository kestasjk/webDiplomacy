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
import WDCenterCounts from "../WDCenterCounts";

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
            dispatch(gameApiSliceActions.setViewedPhaseToLatest());
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
  const [isNewPhase, setIsNewPhase] = useState<boolean>(false);
  const [lastViewedPhase, setLastViewedPhase] =
    useState<number>(viewedPhaseIdx);
  const [lastPhase, setLastPhase] = useState<number>(-1);
  const { phase } = useAppSelector(gameOverview);

  useEffect(() => {
    if (
      lastPhase !== totalPhases &&
      viewedPhaseIdx !== totalPhases - 1 &&
      lastPhase !== -1
    ) {
      setIsNewPhase(true);
    }
    if (viewedPhaseIdx !== lastViewedPhase) {
      setIsNewPhase(false);
    }
    setLastViewedPhase(viewedPhaseIdx);
    setLastPhase(totalPhases);
  }, [viewedPhaseIdx, totalPhases]);

  return (
    <WDPositionContainer position={Position.BOTTOM_MIDDLE} bottom={8}>
      <WDBuildCounts />
      {width > 650 && phase !== "Pre-game" && (
        <div className="bg-black text-white items-center p-1 m-2 font-medium uppercase text-xs">
          <WDCenterCounts />
        </div>
      )}

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
