import React, { ReactElement, FunctionComponent } from "react";
import { useAppSelector } from "../../../state/hooks";
import Position from "../../../enums/Position";
import WDPositionContainer from "../WDPositionContainer";
import WDOrderStatusControls from "../WDOrderStatusControls";
import { gameOverview } from "../../../state/game/game-api-slice";
import AutoSaveToggle from "./buttons/AutoSaveToggle";

interface BottomLeftProps {
  phaseSelectorOpen: boolean;
}

const BottomLeft: FunctionComponent<BottomLeftProps> = function ({
  phaseSelectorOpen,
}: BottomLeftProps): ReactElement {
  const { user, phase } = useAppSelector(gameOverview);

  return (
    // eslint-disable-next-line react/jsx-no-useless-fragment
    <>
      {user && phase !== "Pre-game" && (
        <WDPositionContainer
          position={Position.BOTTOM_LEFT}
          bottom={phaseSelectorOpen ? 40 : 4}
        >
          <AutoSaveToggle className="hidden mb-3 sm:block" />
          <WDOrderStatusControls orderStatus={user?.member.orderStatus} />
          <AutoSaveToggle className="sm:hidden mt-3 sm:mt-0" />
        </WDPositionContainer>
      )}
    </>
  );
};

export default BottomLeft;
