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
  if (user && gameID) {
    const dispatch = useAppDispatch();
    dispatch(
      fetchGameData({
        gameID: gameID as unknown as string,
        countryID: user.member.countryID as unknown as string,
      }),
    );
    for (let i = 0; i < members.length; i += 1) {
      dispatch(
        fetchGameMessages({
          gameID: gameID as unknown as string,
          countryID: user.member.countryID as unknown as string,
          toCountryID: members[i].countryID as unknown as string,
          limit: "25",
        }),
      );
    }
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
