import * as React from "react";
import "./assets/css/App.css";
import Box from "@mui/material/Box";
import WDMain from "./components/ui/WDMain";
import { useAppDispatch } from "./state/hooks";
import { loadGame } from "./state/game/game-api-slice";

const App: React.FC = function (): React.ReactElement {
  const urlParams = new URLSearchParams(window.location.search);
  const currentGameID = urlParams.get("gameID");
  const dispatch = useAppDispatch();
  dispatch(loadGame(String(currentGameID)));
  return (
    <Box className="App">
      <WDMain />
    </Box>
  );
};

export default App;
