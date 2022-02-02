import * as React from "react";
import "./assets/css/App.css";
import Box from "@mui/material/Box";
import { SvgIcon } from "@mui/material";
import map from "./assets/svg/map.svg";
import WDModal from "./components/wd-modal/WDModal";

const App: React.FC = function (): React.ReactElement {
  return (
    <Box className="App">
      <WDModal
        ariaDescribedBy="test-modal"
        triggerIcon={
          <SvgIcon>
            <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
          </SvgIcon>
        }
      >
        Hello World
      </WDModal>
      <WDModal
        ariaDescribedBy="test-modal"
        triggerIcon={
          <SvgIcon>
            <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
          </SvgIcon>
        }
      >
        Hello World
      </WDModal>
      <img alt="Game Map" src={map} />
    </Box>
  );
};

export default App;
