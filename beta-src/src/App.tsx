import * as React from "react";
import "./assets/css/App.css";
import Box from "@mui/material/Box";
import WDMain from "./components/ui/WDMain";
import WDCountdownPill from "./components/ui/WDCountdownPill";

const App: React.FC = function (): React.ReactElement {
  return (
    <Box className="App">
      <WDCountdownPill
        remainingTime={{
          seconds: 40,
          minutes: 0,
          hours: 0,
          days: 0,
        }}
      />
      <WDMain />
    </Box>
  );
};

export default App;
