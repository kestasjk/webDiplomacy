import { Box } from "@mui/material";
import * as React from "react";
import { useEffect } from "react";
import {
  fetchGameOverview,
  gameApiSliceActions,
  gameOverview,
  gameData,
  gameStatus,
  gameViewedPhase,
  loadGameData,
} from "../../state/game/game-api-slice";
import { useAppDispatch, useAppSelector } from "../../state/hooks";
import getPhaseKey from "../../utils/state/getPhaseKey";
import WDGameProgressOverlay from "../ui/WDGameProgressOverlay";
import WDAlertModal from "../ui/WDAlertModal";
import { store } from "../../state/store";
import useInterval from "../../hooks/useInterval";

const WDMainController: React.FC = function ({ children }): React.ReactElement {
  const [displayedPhaseKey, setDisplayedPhaseKey] = React.useState<
    string | null
  >(null);
  const dispatch = useAppDispatch();
  const overview = useAppSelector(gameOverview);
  const { data } = useAppSelector(gameData);
  const status = useAppSelector(gameStatus);
  const viewedPhaseState = useAppSelector(gameViewedPhase);

  const { countryID } = overview.user.member;

  const overviewKey = getPhaseKey(overview, "<BAD OVERVIEW_KEY>");
  const statusKey = getPhaseKey(status, "<BAD STATUS_KEY>");
  const dataKey = getPhaseKey(data.contextVars?.context, "<BAD DATA_KEY>");

  const dispatchFetchOverview = () => {
    const { game } = store.getState();
    const { outstandingOverviewRequests } = game;
    // console.log({ outstandingOverviewRequests });
    if (!outstandingOverviewRequests) {
      dispatch(
        fetchGameOverview({
          gameID: String(overview.gameID),
        }),
      );
    }
  };

  // FIXME: for now, crazily fetch all messages every 5sec
  useInterval(dispatchFetchOverview, 5000);

  const needsGameData = useAppSelector(({ game }) => game.needsGameData);
  const noPhase = ["Error", "Pre-game"].includes(overview.phase);
  const consistentPhase =
    noPhase || (overviewKey === statusKey && overviewKey === dataKey);

  if (needsGameData && !noPhase) {
    dispatch(gameApiSliceActions.setNeedsGameData(false));
    dispatch(loadGameData(String(overview.gameID), String(countryID)));
  }

  const { name, gameID } = overview;
  useEffect(() => {
    document.title = `${name} - webDiplomacy Game ${gameID}`;
  }, [name, gameID]);

  if (!consistentPhase) {
    return <Box>Loading...</Box>;
  }

  const showOverlay =
    noPhase || (displayedPhaseKey && overviewKey !== displayedPhaseKey);
  if (displayedPhaseKey === null && overview.phase) {
    setDisplayedPhaseKey(overviewKey);
  }
  return (
    <div>
      {!noPhase && children}
      {showOverlay && (
        <WDGameProgressOverlay
          overview={overview}
          status={status}
          viewedPhaseState={viewedPhaseState}
          clickHandler={() => {
            setDisplayedPhaseKey(overviewKey);
            // When the user clicks on the overlay, we jump them to the latest phase
            // that they've seen so far. If the game has moved on one or more phases past that
            // then this phase will now be filled with the latest orders that the user has
            // *not* yet seen (i.e. presumably they saw this phase when they were entering
            // orders for it, before that phase ended and other powers' orders appeared).
            dispatch(gameApiSliceActions.setViewedPhaseToLatestPhaseViewed());
          }}
        />
      )}
      <WDAlertModal />
    </div>
  );
};

export default WDMainController;
