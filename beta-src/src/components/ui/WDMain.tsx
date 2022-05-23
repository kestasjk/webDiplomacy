import * as React from "react";
import WDMainController from "../controllers/WDMainController";
import WDTransition from "./WDTransition";
import WDUI from "./WDUI";
import { gameTransition } from "../../state/game/game-api-slice";
import { useAppSelector } from "../../state/hooks";

const WDMapController = React.lazy(
  () => import("../controllers/WDMapController"),
);

const WDMain: React.FC = function (): React.ReactElement {
  const transition = useAppSelector(gameTransition);
  return (
    <React.Suspense fallback={<div>Loading...</div>}>
      <WDMainController>
        <WDMapController />
        {!transition && <WDUI />}
        {transition && <WDTransition />}
      </WDMainController>
    </React.Suspense>
  );
};

export default WDMain;
