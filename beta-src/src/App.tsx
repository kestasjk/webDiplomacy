import * as React from "react";
import "./assets/css/App.css";
import Box from "@mui/material/Box";
import { useAppDispatch, useAppSelector } from "./state/hooks";
import {
  fetchGameStatus,
  gameApiStatus,
  gameStatus,
} from "./state/game/game-api-slice";

const App: React.FC = function (): React.ReactElement {
  const dispatch = useAppDispatch();
  const status = useAppSelector(gameStatus);
  const apiStatus = useAppSelector(gameApiStatus);

  React.useEffect(() => {
    if (apiStatus === "idle") {
      dispatch(fetchGameStatus({ countryID: "0", gameID: "2" }));
    }
  }, [apiStatus, dispatch]);

  return (
    <Box className="App">
      <div>{JSON.stringify(status)}</div>
    </Box>
  );
};

export default App;
