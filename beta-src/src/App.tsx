import * as React from "react";
import "./assets/css/App.css";
import Box from "@mui/material/Box";
import WDMain from "./components/ui/WDMain";
import { useAppDispatch } from "./state/hooks";
import {
  fetchGameMessages,
  fetchGameOverview,
} from "./state/game/game-api-slice";

const App: React.FC = function (): React.ReactElement {
  const urlParams = new URLSearchParams(window.location.search);
  const currentGameID = urlParams.get("gameID");
  const dispatch = useAppDispatch();
  dispatch(fetchGameOverview({ gameID: currentGameID as string }));
  dispatch(
    fetchGameMessages({
      gameID: currentGameID as string,
      countryID: "0" as string,
    }),
  );
  return (
    <Box className="App">
      <WDMain />
    </Box>
  );
};

export default App;
