import * as React from "react";
import "./assets/css/App.css";
import { Box, Button } from "@mui/material";
import { useState } from "react";
// import map from "./assets/svg/map.svg"; <img alt="Game Map" src={map} />

const App: React.FC = function (): React.ReactElement {
  const [isDisabled, setIsDisabled] = useState(false);
  return (
    <Box className="App">
      <Button
        disabled={isDisabled}
        onClick={() => setIsDisabled(!isDisabled)}
        variant="contained"
      >
        Save
      </Button>
      <Button
        color="secondary"
        onClick={() => setIsDisabled(!isDisabled)}
        variant="contained"
      >
        Cancel
      </Button>
    </Box>
  );
};

export default App;
