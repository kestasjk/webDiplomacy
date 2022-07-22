import React, { ReactElement, FunctionComponent } from "react";
import Position from "../../../enums/Position";
import WDBuildCounts from "../WDBuildCounts";
import WDPositionContainer from "../WDPositionContainer";

interface BottomMiddleProps {
  phaseSelectorOpen: boolean;
}

const BottomMiddle: FunctionComponent<BottomMiddleProps> = function ({
  phaseSelectorOpen,
}: BottomMiddleProps): ReactElement {
  return (
    <WDPositionContainer
      position={Position.BOTTOM_MIDDLE}
      bottom={phaseSelectorOpen ? 40 : 4}
    >
      <WDBuildCounts />
    </WDPositionContainer>
  );
};

export default BottomMiddle;
