import * as React from "react";
import "./assets/css/App.css";
import Box from "@mui/material/Box";
import WDMain from "./components/ui/WDMain";
import { useAppDispatch, useAppSelector } from "./state/hooks";
import {
  fetchGameMessages,
  fetchGameOverview,
} from "./state/game/game-api-slice";

const App: React.FC = function (): React.ReactElement {
  const urlParams = new URLSearchParams(window.location.search);
  const currentGameID = urlParams.get("gameID");
  const dispatch = useAppDispatch();
  const countryID = useAppSelector((state) =>
    String(state.game.overview.user.member.countryID),
  );
  dispatch(fetchGameOverview({ gameID: currentGameID as string }));
  dispatch(
    fetchGameMessages({
      gameID: currentGameID as string,
      countryID,
      allMessages: "true",
    }),
  );
  return (
    <Box className="App">
      <WDMain />
    </Box>
  );
};

export default App;
