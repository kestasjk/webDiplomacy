import * as React from "react";
import WDMainController from "../controllers/WDMainController";
import {
  fetchGameData,
  fetchGameMessages,
  gameOverview,
} from "../../state/game/game-api-slice";
import { useAppDispatch, useAppSelector } from "../../state/hooks";
import WDUI from "./WDUI";

const WDMapController = React.lazy(
  () => import("../controllers/WDMapController"),
);

const WDMain: React.FC = function (): React.ReactElement {
  const { user, gameID, members } = useAppSelector(gameOverview);
  const dispatch = useAppDispatch();

  if (user && gameID) {
    dispatch(
      fetchGameData({
        gameID: gameID as unknown as string,
        countryID: user.member.countryID as unknown as string,
      }),
    );
  }

  // FIXME: for now, crazily fetch all messages every 1sec
  React.useEffect(() => {
    setInterval(() => {
      if (user && gameID) {
        dispatch(
          fetchGameMessages({
            gameID: gameID as unknown as string,
            countryID: user.member.countryID as unknown as string,
          }),
        );
      }
    }, 1000);
  });

  return (
    <React.Suspense fallback={<div>Loading...</div>}>
      <WDMainController>
        <WDMapController />
        <WDUI />
      </WDMainController>
    </React.Suspense>
  );
};

export default WDMain;
