import * as React from "react";
import "./assets/css/App.css";
import Box from "@mui/material/Box";
import WDMain from "./components/ui/WDMain";

import WDPhaseUI from "./components/ui/WDPhaseUI";

const App: React.FC = function (): React.ReactElement {
  return (
    <Box className="App">
      {/* <WDMain /> */}
      <WDPhaseUI />
    </Box>
  );
};

export default App;
