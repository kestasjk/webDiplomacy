import * as React from "react";
import "./assets/css/App.css";
import Box from "@mui/material/Box";
import WDMain from "./components/ui/WDMain";
import WDArrowController from "./components/svgr-components/WDArrowController";

const App: React.FC = function (): React.ReactElement {
  return (
    <Box className="App">
      {/* <WDMain /> */}
      <WDArrowController actionType="holdSupport" />
    </Box>
  );
};

export default App;
