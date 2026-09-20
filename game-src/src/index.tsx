import React from "react";
import ReactDOM from "react-dom";
import { CssBaseline, ThemeProvider } from "@mui/material";
import { Provider } from "react-redux";
import ReactGA from "react-ga4";
import webDiplomacyTheme from "./webDiplomacyTheme";
import "./assets/css/index.css";
import App from "./App";
import { store } from "./state/store";
import ErrorBoundary from "./components/miscellaneous/ErrorBoundary";
import { installClientErrorReporting } from "./utils/clientLog";

// Before anything else renders, so that an error while it does is reported
installClientErrorReporting();

ReactGA.initialize("G-MC45SZ2JEC"); // Replace with your Measurement ID
ReactGA.send("pageview"); // Optional: Send initial pageview

ReactDOM.render(
  <ErrorBoundary>
    <Provider store={store}>
      <ThemeProvider theme={webDiplomacyTheme}>
        <CssBaseline />
        <App />
      </ThemeProvider>
    </Provider>
  </ErrorBoundary>,
  document.getElementById("root"),
);
