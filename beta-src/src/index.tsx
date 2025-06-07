import React from "react";
import ReactDOM from "react-dom";
import { CssBaseline, ThemeProvider } from "@mui/material";
import { Provider } from "react-redux";
import ReactGA from "react-ga4";
import webDiplomacyTheme from "./webDiplomacyTheme";
import "./assets/css/index.css";
import App from "./App";
import { store } from "./state/store";

ReactGA.initialize("G-MC45SZ2JEC"); // Replace with your Measurement ID
ReactGA.send("pageview"); // Optional: Send initial pageview

ReactDOM.render(
  <Provider store={store}>
    <ThemeProvider theme={webDiplomacyTheme}>
      <CssBaseline />
      <App />
    </ThemeProvider>
  </Provider>,
  document.getElementById("root"),
);
