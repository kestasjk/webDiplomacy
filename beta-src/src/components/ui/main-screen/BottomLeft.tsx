import React, { ReactElement, FunctionComponent } from "react";
import { useAppSelector } from "../../../state/hooks";
import Position from "../../../enums/Position";
import WDPositionContainer from "../WDPositionContainer";
import WDOrderStatusControls from "../WDOrderStatusControls";
import { gameOverview } from "../../../state/game/game-api-slice";

interface BottomLeftProps {
  phaseSelectorOpen: boolean;
}

const BottomLeft: FunctionComponent<BottomLeftProps> = function ({
  phaseSelectorOpen,
}: BottomLeftProps): ReactElement {
  const { user } = useAppSelector(gameOverview);

  return (
    // eslint-disable-next-line react/jsx-no-useless-fragment
    <>
      {user && (
        <WDPositionContainer
          position={Position.BOTTOM_LEFT}
          bottom={phaseSelectorOpen ? 40 : 4}
        >
          <WDOrderStatusControls orderStatus={user?.member.orderStatus} />
        </WDPositionContainer>
      )}
    </>
  );
};

export default BottomLeft;
