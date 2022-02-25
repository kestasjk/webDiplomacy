import * as React from "react";
import "./assets/css/App.css";
import Box from "@mui/material/Box";
import WDMain from "./components/ui/WDMain";
import WDScrollButton from "./components/ui/WDScrollButton";
import { ScrollButtonState } from "./enums/UIState";

const App: React.FC = function (): React.ReactElement {
  return (
    <Box className="App">
      {/* <WDMain /> */}
      <WDScrollButton direction={ScrollButtonState.BACK} />
    </Box>
  );
};

export default App;
