import * as React from "react";
import WDUI from "./WDUI";

const WDMapController = React.lazy(() => import("../map/WDMapController"));

const WDMain: React.FC = function (): React.ReactElement {
  return (
    <React.Suspense fallback={<div>Loading...</div>}>
      <WDMapController />
      <WDUI />
    </React.Suspense>
  );
};

export default WDMain;
