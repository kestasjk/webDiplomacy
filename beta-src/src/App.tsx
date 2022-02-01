import * as React from "react";
import "./assets/css/App.css";
import Box from "@mui/material/Box";
import map from "./assets/svg/map.svg";
import SimpleDialogDemo from "./components/wd-modal/WDModal";

const App: React.FC = function (): React.ReactElement {
  return (
    <Box className="App">
      <SimpleDialogDemo modalParentId="exitGameModal" topValue="0" />
      {/* <SimpleDialogDemo modalParentId="gameMoveModal" topValue="200px" /> */}
      {/* <img alt="Game Map" src={map} /> */}
    </Box>
  );
};

export default App;
