import * as React from "react";
import WDMainController from "../controllers/WDMainController";
import { fetchGameData, gameOverview } from "../../state/game/game-api-slice";
import { useAppDispatch, useAppSelector } from "../../state/hooks";
import WDUI from "./WDUI";

const WDMapController = React.lazy(
  () => import("../controllers/WDMapController"),
);

const WDMain: React.FC = function (): React.ReactElement {
  console.log("WDMain rerendered");
  const { user, gameID } = useAppSelector(gameOverview);
  const dispatch = useAppDispatch();
  if (user && gameID) {
    dispatch(
      fetchGameData({
        gameID: gameID as unknown as string,
        countryID: user.member.countryID as unknown as string,
      }),
    );
  }

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
