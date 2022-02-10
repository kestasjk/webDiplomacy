import * as React from "react";
import "./assets/css/App.css";
import Box from "@mui/material/Box";
import { ThemeProvider } from "@mui/material";
import webDiplomacyTheme from "./webDiplomacyTheme";
import map from "./assets/svg/map.svg";

const App: React.FC = function (): React.ReactElement {
  return (
    <ThemeProvider theme={webDiplomacyTheme}>
      <Box className="App">
        <img alt="Game Map" src={map} />
      </Box>
    </ThemeProvider>
  );
};

export default App;
