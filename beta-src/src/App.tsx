import * as React from "react";
import "./assets/css/App.css";
import { Box, Typography } from "@mui/material";
// import map from "./assets/svg/map.svg"; <img alt="Game Map" src={map} />

const App: React.FC = function (): React.ReactElement {
  return (
    <Box className="App">
      <Typography gutterBottom variant="h1">
        variant h1, figma h1
      </Typography>
      <Typography gutterBottom variant="h2">
        variant h2, figma h2
      </Typography>
      <Typography gutterBottom variant="h3">
        variant h3, figma h3
      </Typography>
      <Typography gutterBottom variant="body1">
        variant body1 (default), figma p
      </Typography>
      <Typography gutterBottom variant="smallLabel" component="label">
        variant smallLabel, figma small label
      </Typography>
    </Box>
  );
};

export default App;
