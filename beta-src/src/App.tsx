import * as React from "react";
import { useState } from "react";
import "./assets/css/App.css";
import { Box, Button } from "@mui/material";
import map from "./assets/svg/map.svg";
import WDPopover from "./components/WDPopover";

const App: React.FC = function (): React.ReactElement {
  const [isOpen, setIsOpen] = useState(false);

  const openPopper = () => {
    setIsOpen(true);
  };

  const closePopper = () => {
    setIsOpen(false);
  };
  return (
    <Box className="App">
      <WDPopover
        onClose={closePopper}
        isOpen={isOpen}
        popoverTrigger={<Button onClick={openPopper}>Trigger</Button>}
      >
        Hello World
      </WDPopover>
      <WDPopover
        onClose={closePopper}
        isOpen={isOpen}
        popoverTrigger={<Button onClick={openPopper}>Trigger</Button>}
      >
        Hello World
      </WDPopover>
      <img alt="Game Map" src={map} />
    </Box>
  );
};

export default App;
