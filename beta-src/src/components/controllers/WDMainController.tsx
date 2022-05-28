import { Box } from "@mui/material";
import * as React from "react";
import {
  fetchGameOverview,
  gameApiSliceActions,
  gameOverview,
  gameUserActivity,
  gameData,
  gameStatus,
  loadGameData,
} from "../../state/game/game-api-slice";
import { useAppDispatch, useAppSelector } from "../../state/hooks";
import debounce from "../../utils/debounce";
import getPhaseKey from "../../utils/state/getPhaseKey";
import WDGameProgressOverlay from "../ui/WDGameProgressOverlay";

const WDMainController: React.FC = function ({ children }): React.ReactElement {
  const [displayedPhaseKey, setDisplayedPhaseKey] = React.useState<
    string | null
  >(null);
  const dispatch = useAppDispatch();
  const userActivity = useAppSelector(gameUserActivity);
  const overview = useAppSelector(gameOverview);
  const { data } = useAppSelector(gameData);
  const status = useAppSelector(gameStatus);

  const { countryID } = overview.user.member;

  const overviewKey = getPhaseKey(overview);
  const statusKey = getPhaseKey(status);
  const dataKey = data.contextVars
    ? getPhaseKey(data.contextVars.context)
    : "<BAD>";

  if (userActivity.makeNewCall) {
    dispatch(fetchGameOverview({ gameID: String(overview.gameID) }));
  }
  const activity = useAppSelector(gameUserActivity);
  const isPregame = ["", "Pre-game"].includes(overview.phase);
  const consistentPhase =
    isPregame || (overviewKey === statusKey && overviewKey === dataKey);

  if (activity.needsGameData && !isPregame) {
    dispatch(gameApiSliceActions.setNeedsGameData(false));
    dispatch(loadGameData(String(overview.gameID), String(countryID)));
  }

  const activityHandler = debounce(() => {
    dispatch(
      gameApiSliceActions.updateUserActivity({
        // eslint-disable-next-line no-bitwise
        lastActive: (Date.now() / 1000) | 0,
      }),
    );
  }, 500);

  if (!consistentPhase) {
    return <Box>Loading...</Box>;
  }

  const phaseProgressed =
    displayedPhaseKey && overviewKey !== displayedPhaseKey;
  if (displayedPhaseKey === null && overview.phase) {
    setDisplayedPhaseKey(overviewKey);
  }
  return (
    <div onMouseMove={activityHandler[0]} onClickCapture={activityHandler[0]}>
      {!isPregame && children}
      {phaseProgressed && (
        <WDGameProgressOverlay
          overview={overview}
          clickHandler={() => setDisplayedPhaseKey(overviewKey)}
        />
      )}
    </div>
  );
};

export default WDMainController;
