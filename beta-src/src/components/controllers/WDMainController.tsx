import { Box } from "@mui/material";
import * as React from "react";
import { useEffect } from "react";
import {
  fetchGameOverview,
  gameApiSliceActions,
  gameOverview,
  gameUserActivity,
  gameData,
  gameStatus,
  loadGameData,
  fetchGameMessages,
  gameOutstandingMessageRequests,
} from "../../state/game/game-api-slice";
import { useAppDispatch, useAppSelector } from "../../state/hooks";
import debounce from "../../utils/debounce";
import getPhaseKey from "../../utils/state/getPhaseKey";
import WDGameProgressOverlay from "../ui/WDGameProgressOverlay";
import WDAlertModal from "../ui/WDAlertModal";

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

  const { name, user, gameID } = overview;
  useEffect(() => {
    document.title = `${name} - webDiplomacy Game ${gameID}`;
  }, [name, gameID]);

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
          clickHandler={() => {
            setDisplayedPhaseKey(overviewKey);
            // When the user clicks on the overlay to show the latest
            // phase, this makes it also jump forward to show them the
            // latest phase.
            dispatch(gameApiSliceActions.changeViewedPhaseIdxBy(Infinity));
          }}
        />
      )}
      <WDAlertModal />
    </div>
  );
};

export default WDMainController;
