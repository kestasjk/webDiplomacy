import React, { ReactElement, FunctionComponent } from "react";
import { useAppSelector } from "../../../state/hooks";
import Position from "../../../enums/Position";
import WDPositionContainer from "../WDPositionContainer";
import RightButton from "./RightButton";
import Season from "../../../enums/Season";
import { gameOverview } from "../../../state/game/game-api-slice";
import WDYearSelector from "../fast-foward-selector/WDYearSelector";

interface BottomRightProps {
  phaseSelectorOpen: boolean;
  onPhaseSelectorClick: () => void;
}

const BottomLeft: FunctionComponent<BottomRightProps> = function ({
  phaseSelectorOpen,
  onPhaseSelectorClick,
}: BottomRightProps): ReactElement {
  return (
    <>
      <WDPositionContainer
        position={Position.BOTTOM_RIGHT}
        bottom={phaseSelectorOpen ? 40 : 4}
      >
        <div>
          <RightButton
            image="action"
            text="eng"
            onClick={() => console.log("clicked")}
            className="mb-6"
          />
          <RightButton
            image="phase"
            text="a1916"
            onClick={onPhaseSelectorClick}
          />
        </div>
      </WDPositionContainer>
      {phaseSelectorOpen && (
        <WDYearSelector
          defaultYear={1908}
          defaultSeason={Season.SPRING}
          onSelected={(seasonSelected: Season, yearSelected: number) =>
            console.log(seasonSelected, yearSelected)
          }
        />
      )}
    </>
  );
};

export default BottomLeft;
