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
  const dispatch = useAppDispatch();
  const [left] = useKeyboardJs("shift + left");
  const [right] = useKeyboardJs("shift + right");
  const [up] = useKeyboardJs("shift + up");
  const [down] = useKeyboardJs("shift + down");
  const [phases] = useKeyboardJs("shift + ctrl + p");

  useEffect(() => {
    if (left) {
      dispatch(gameApiSliceActions.changeViewedPhaseIdxBy(-1));
    }
    if (right) {
      dispatch(gameApiSliceActions.changeViewedPhaseIdxBy(1));
    }
    if (up) {
      dispatch(gameApiSliceActions.setViewedPhaseToLatestPhaseViewed());
    }
    if (down) {
      dispatch(gameApiSliceActions.setViewedPhase(0));
    }
    if (phases) {
      onPhaseSelectorShortcut();
    }
  }, [left, right, up, down, phases]);

  // eslint-disable-next-line react/jsx-no-useless-fragment
  return <></>;
};

WDShortcuts.defaultProps = {};

export default WDShortcuts;
