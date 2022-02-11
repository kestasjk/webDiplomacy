import React from "react";
import ReactDOM from "react-dom";
import { CssBaseline, ThemeProvider } from "@mui/material";
import webDiplomacyTheme from "./webDiplomacyTheme";
import "./assets/css/index.css";
import App from "./App";

ReactDOM.render(
  <React.StrictMode>
    <ThemeProvider theme={webDiplomacyTheme}>
      <CssBaseline />
      <App />
    </ThemeProvider>
  </React.StrictMode>,
  document.getElementById("root"),
);
