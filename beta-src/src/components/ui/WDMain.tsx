import * as React from "react";
import {
  fetchGameData,
  gameApiSliceActions,
  gameData,
  gameOverview,
} from "../../state/game/game-api-slice";
import { useAppDispatch, useAppSelector } from "../../state/hooks";
import WDUI from "./WDUI";

const WDMapController = React.lazy(() => import("../map/WDMapController"));

const WDMain: React.FC = function (): React.ReactElement {
  const { user, gameID } = useAppSelector(gameOverview);
  if (user && gameID) {
    const dispatch = useAppDispatch();
    dispatch(
      fetchGameData({
        gameID: gameID as unknown as string,
        countryID: user.member.countryID as unknown as string,
      }),
    );
  }
  return (
    <React.Suspense fallback={<div>Loading...</div>}>
      <WDMapController />
      <WDUI />
    </React.Suspense>
  );
};

export default WDMain;
