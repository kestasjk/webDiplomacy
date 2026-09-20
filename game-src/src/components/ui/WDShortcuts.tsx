import React, { useEffect } from "react";
import useKeyboardJs from "react-use/lib/useKeyboardJs";
import { useAppDispatch } from "../../state/hooks";
import { gameApiSliceActions } from "../../state/game/game-api-slice";

interface WDShortcutsProps {
  onPhaseSelectorShortcut: () => void;
}

const WDShortcuts: React.FC<WDShortcutsProps> = function ({
  onPhaseSelectorShortcut,
}): React.ReactElement {
  // TODO: create a Provider for this with the appropriate
  // element reference once it's accepted after UX validation.
  // Do not access the dom like this.
  const inputMessage = document.getElementById("user-msg");

  const dispatch = useAppDispatch();
  const [left] = useKeyboardJs("shift + left");
  const [right] = useKeyboardJs("shift + right");
  const [up] = useKeyboardJs("shift + up");
  const [down] = useKeyboardJs("shift + down");
  // const [phases] = useKeyboardJs("shift + ctrl + p");

  // gameStatusData.phases.length;

  useEffect(() => {
    const inputMessageActive = inputMessage === document.activeElement;
    if (!inputMessageActive) {
      if (left) {
        dispatch(gameApiSliceActions.changeViewedPhaseIdxBy(-1));
      }
      if (right) {
        dispatch(gameApiSliceActions.changeViewedPhaseIdxBy(1));
      }
      if (up) {
        dispatch(gameApiSliceActions.setViewedPhaseToLatest());
      }
      if (down) {
        dispatch(gameApiSliceActions.setViewedPhase(0));
      }
    }
  }, [left, right, up, down]);

  // eslint-disable-next-line react/jsx-no-useless-fragment
  return <></>;
};

WDShortcuts.defaultProps = {};

export default WDShortcuts;
