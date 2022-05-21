import * as React from "react";
import useInterval from "../../hooks/useInterval";
import {
  fetchGameData,
  fetchGameOverview,
  gameApiSliceActions,
  gameOverview,
  userActivity,
} from "../../state/game/game-api-slice";
import { useAppDispatch, useAppSelector } from "../../state/hooks";
import debounce from "../../utils/debounce";
import getCurrentUnixTimestamp from "../../utils/getCurrentUnixTimestamp";

const WDMainController: React.FC = function ({ children }): React.ReactElement {
  const dispatch = useAppDispatch();
  const { season, year, processTime, makeNewCall, lastActive } =
    useAppSelector(userActivity);
  const {
    season: newSeason,
    year: newYear,
    processTime: newProcessTime,
    gameID,
    user: {
      member: { countryID },
    },
  } = useAppSelector(gameOverview);
  const poll = () => dispatch(fetchGameOverview({ gameID: String(gameID) }));
  if (makeNewCall) {
    poll();
  }
  if (
    processTime &&
    (season !== newSeason || year !== newYear || processTime !== newProcessTime)
  ) {
    dispatch(
      fetchGameData({ gameID: String(gameID), countryID: String(countryID) }),
    );
  }
  useInterval(() => {
    const now = getCurrentUnixTimestamp();
    if (!makeNewCall && now === lastActive + 10) {
      poll();
    }
  }, 1000);
  const activityHandler = debounce(() => {
    dispatch(
      gameApiSliceActions.updateUserActivity({
        lastActive: getCurrentUnixTimestamp(),
      }),
    );
  }, 100);
  return (
    <div onMouseMove={activityHandler[0]} onClickCapture={activityHandler[0]}>
      {children}
    </div>
  );
};

export default WDMainController;
