import * as React from "react";
import {
  fetchGameData,
  fetchGameOverview,
  gameApiSliceActions,
  gameOverview,
  userActivity,
} from "../../state/game/game-api-slice";
import { useAppDispatch, useAppSelector } from "../../state/hooks";
import debounce from "../../utils/debounce";
import WDUI from "./WDUI";

const WDMapController = React.lazy(() => import("../map/WDMapController"));

const WDMain: React.FC = function (): React.ReactElement {
  const dispatch = useAppDispatch();
  const { season, year, processTime, makeNewCall } =
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
  if (makeNewCall) {
    dispatch(fetchGameOverview({ gameID: String(gameID) }));
  }
  if (
    processTime &&
    (season !== newSeason || year !== newYear || processTime !== newProcessTime)
  ) {
    dispatch(
      fetchGameData({ gameID: String(gameID), countryID: String(countryID) }),
    );
  }
  const activityHandler = debounce(() => {
    dispatch(
      gameApiSliceActions.updateUserActivity({
        // eslint-disable-next-line no-bitwise
        lastActive: (Date.now() / 1000) | 0,
      }),
    );
  }, 500);
  return (
    <React.Suspense fallback={<div>Loading...</div>}>
      <div onMouseMove={activityHandler[0]} onClickCapture={activityHandler[0]}>
        <WDMapController />
        <WDUI />
      </div>
    </React.Suspense>
  );
};

export default WDMain;
