import React, { ReactElement } from "react";
import Position from "../../../enums/Position";
import WDPositionContainer from "../WDPositionContainer";
import WDHomeIcon from "../icons/WDHomeIcon";

const TopRight = function (): ReactElement {
  return (
    <WDPositionContainer position={Position.TOP_RIGHT}>
      <a href="/">
        <WDHomeIcon />
      </a>
    </WDPositionContainer>
  );
};

export default TopRight;
