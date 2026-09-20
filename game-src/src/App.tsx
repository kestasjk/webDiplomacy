import * as React from "react";

import "./assets/css/App.css";
import WDAds from "./components/ui/WDAds";
import WDMain from "./components/ui/WDMain";
import useAdPlacement from "./hooks/useAdPlacement";
import { useAppDispatch } from "./state/hooks";
import { loadGame } from "./state/game/game-api-slice";

const App: React.FC = function (): React.ReactElement {
  const urlParams = new URLSearchParams(window.location.search);
  const currentGameID = urlParams.get("gameID");
  const dispatch = useAppDispatch();
  React.useEffect(() => {
    dispatch(loadGame(String(currentGameID)));
  }, [dispatch, currentGameID]);
  const adPlacement = useAdPlacement();
  return (
    <div className="App">
      {/* The following line prevents the UI from being scaled down when the viewport is small.
      That leads to a very bad experience for this UI, with part of the map cut off. */}
      <meta name="viewport" content="width=device-width, user-scalable=no" />
      <WDAds placement={adPlacement} />
      {/* The game area is a positioned box inset by the dedicated ad areas,
      so the absolutely-positioned UI overlays anchor to it (not the window)
      and stay clear of the ads. useViewport subtracts the same insets. */}
      <div
        style={{
          position: "fixed",
          top: adPlacement.insets.top,
          left: adPlacement.insets.left,
          right: adPlacement.insets.right,
          bottom: 0,
          overflow: "hidden",
        }}
      >
        <WDMain />
      </div>
    </div>
  );
};

export default App;
