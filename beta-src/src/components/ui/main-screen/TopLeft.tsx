import React, { ReactElement, FunctionComponent } from "react";
import WDPhaseUI from "../WDPhaseUI";
import Position from "../../../enums/Position";
import Season from "../../../enums/Season";
import WDPositionContainer from "../WDPositionContainer";

interface TopLeftProps {
  gamePhase: string;
  gameSeason: Season;
  gameYear: number;
  viewedPhase: string;
  viewedSeason: Season;
  viewedYear: number;
}

const TopLeft: FunctionComponent<TopLeftProps> = function ({
  gamePhase,
  gameSeason,
  gameYear,
  viewedPhase,
  viewedSeason,
  viewedYear,
}: TopLeftProps): ReactElement {
  return (
    <WDPositionContainer position={Position.TOP_LEFT}>
      <WDPhaseUI
        gamePhase={gamePhase}
        gameSeason={gameSeason}
        gameYear={gameYear}
        viewedPhase={viewedPhase}
        viewedSeason={viewedSeason}
        viewedYear={viewedYear}
      />
    </WDPositionContainer>
  );
};

export default TopLeft;
