import React from "react";
import ReactDOM from "react-dom";
import { CssBaseline, ThemeProvider } from "@mui/material";
import { Provider } from "react-redux";
import ReactGA from "react-ga4";
import webDiplomacyTheme from "./webDiplomacyTheme";
import "./assets/css/index.css";
import App from "./App";
import { store } from "./state/store";
import syncPushSubscription from "./utils/pushSync";

ReactGA.initialize("G-MC45SZ2JEC"); // Replace with your Measurement ID
ReactGA.send("pageview"); // Optional: Send initial pageview

// Fire-and-forget; no-op unless push is enabled for this user
syncPushSubscription().catch(() => undefined);

ReactDOM.render(
  <Provider store={store}>
    <ThemeProvider theme={webDiplomacyTheme}>
      <CssBaseline />
      <App />
    </ThemeProvider>
  </Provider>,
  document.getElementById("root"),
);
