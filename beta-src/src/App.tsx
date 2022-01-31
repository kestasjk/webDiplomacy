import * as React from "react";
import "./assets/css/App.css";
import Box from "@mui/material/Box";
import WDButton from "./wd-button/WDButton";
// import map from "./assets/svg/map.svg"; <img alt="Game Map" src={map} />

const App: React.FC = function (): React.ReactElement {
  const whatAmI = (e) => alert(e.target.innerText);
  return (
    <Box
      className="App"
      m={5}
      style={{
        display: "flex",
        justifyContent: "space-between",
      }}
    >
      <WDButton onClick={whatAmI}>primary</WDButton>
      <WDButton color="secondary" onClick={whatAmI}>
        secondary
      </WDButton>
      <WDButton disabled>primary disabled</WDButton>
      <WDButton color="secondary" disabled>
        secondary disabled
      </WDButton>
    </Box>
  );
};

export default App;
