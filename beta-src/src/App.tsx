import * as React from "react";
import "./assets/css/App.css";
import Box from "@mui/material/Box";
import map from "./assets/svg/map.svg";
import {
  ActionIconSelected,
  HomeIconSelected,
} from "./components/svgr-components";

const App: React.FC = function (): React.ReactElement {
  return (
    <Box className="App">
      {/* <img alt="Game Map" src={map} /> */}
      <ActionIconSelected iconState="inactive" />
      <HomeIconSelected iconState="inactive" />
    </Box>
  );
};

export default App;
