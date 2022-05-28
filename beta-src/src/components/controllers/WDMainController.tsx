import { Box, Button, Grid, Stack } from "@mui/material";
import * as React from "react";
import Position from "../../enums/Position";
import { IContext } from "../../models/Interfaces";
import {
  fetchGameData,
  fetchGameOverview,
  gameApiSliceActions,
  gameOverview,
  gameUserActivity,
  gameData,
  gameStatus,
  loadGameData,
} from "../../state/game/game-api-slice";
import { useAppDispatch, useAppSelector } from "../../state/hooks";
import GameOverviewResponse from "../../state/interfaces/GameOverviewResponse";
import GameStatusResponse from "../../state/interfaces/GameStatusResponse";
import debounce from "../../utils/debounce";
import WDGameProgressOverlay from "../ui/WDGameProgressOverlay";

const getPhaseKey = function (
  data: GameOverviewResponse | GameStatusResponse | IContext,
): string {
  return `${data.turn}.${data.phase}`;
};

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

  const consistentPhase = overviewKey === statusKey && overviewKey === dataKey;
  const staleData = userActivity.processTime !== overview.processTime;

  if (!consistentPhase || userActivity.makeNewCall) {
    console.log({
      overviewKey,
      statusKey,
      dataKey,
    });
    dispatch(fetchGameOverview({ gameID: String(overview.gameID) }));
  }
  const outstandingRequests = useAppSelector(
    ({ game: { outstandingGameRequests } }) => outstandingGameRequests,
  );
  console.log({
    consistentPhase,
    staleData,
    overviewKey,
    statusKey,
    dataKey,
    activityTime: userActivity.processTime,
    overviewTime: overview.processTime,
    outstandingRequests,
  });

  if (
    (!consistentPhase || staleData) &&
    overview.processTime !== null &&
    outstandingRequests === 0
  ) {
    dispatch(
      gameApiSliceActions.updateUserActivityProcessTime(overview.processTime),
    );
    dispatch(gameApiSliceActions.updateOutstandingGameRequests(2));
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
  if (displayedPhaseKey === null) {
    setDisplayedPhaseKey(overviewKey);
  }
  return (
    <div onMouseMove={activityHandler[0]} onClickCapture={activityHandler[0]}>
      {children}
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
