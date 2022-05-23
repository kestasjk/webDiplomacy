import * as React from "react";
import WDMainController from "../controllers/WDMainController";

import WDUI from "./WDUI";

const WDMapController = React.lazy(
  () => import("../controllers/WDMapController"),
);

const WDMain: React.FC = function (): React.ReactElement {
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
