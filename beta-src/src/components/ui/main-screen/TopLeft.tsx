import React, { ReactElement } from "react";
import WDPhaseUI from "../WDPhaseUI";
import Position from "../../../enums/Position";
import WDPositionContainer from "../WDPositionContainer";

const TopLeft = function (): ReactElement {
  return (
    <WDPositionContainer position={Position.TOP_LEFT}>
      <WDPhaseUI />
    </WDPositionContainer>
  );
};

export default TopLeft;
