import * as React from "react";
import "./assets/css/App.css";
import { Box, Button } from "@mui/material";
// import map from "./assets/svg/map.svg"; <img alt="Game Map" src={map} />

const App: React.FC = function (): React.ReactElement {
  return (
    <Box className="App">
      <Button variant="contained">Save</Button>
      <Button color="secondary" variant="contained">
        Cancel
      </Button>
      <Button disabled variant="contained">
        Save
      </Button>
    </Box>
  );
};

export default App;
