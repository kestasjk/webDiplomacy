import * as React from "react";
import { useState } from "react";
import { Box, Stack, Button } from "@mui/material";
import WDInfoDisplay from "./WDInfoDisplay";
import { CountryTableData } from "../../interfaces";
import Device from "../../enums/Device";
import WDInfoPanel from "./WDInfoPanel";
import WDPress from "./WDPress";
import ModalViews from "../../enums/ModalViews";
import GameOverviewResponse from "../../state/interfaces/GameOverviewResponse";
import useDevice from "../../hooks/useDevice";

interface WDFullModalProps {
  alternatives: GameOverviewResponse["alternatives"];
  children: React.ReactNode;
  countries: CountryTableData[];
  excusedMissedTurns: GameOverviewResponse["excusedMissedTurns"];
  potNumber: GameOverviewResponse["pot"];
  season: GameOverviewResponse["season"];
  title: GameOverviewResponse["name"];
  userCountry: CountryTableData;
  year: GameOverviewResponse["year"];
}

const textButtonStyle = {
  borderRadius: 0,
  fontWeight: 400,
  minWidth: 0,
  p: "0 0 5px 0",
  "&:hover": {
    background: "transparent",
  },
};

const textButtonSelected = {
  borderRadius: 0,
  borderBottom: "solid black 2px",
  fontWeight: 600,
  minWidth: 0,
  p: "0 0 5px 0",
  "&:hover": {
    background: "transparent",
  },
};

const WDFullModal: React.FC<WDFullModalProps> = function ({
  alternatives,
  children,
  countries,
  excusedMissedTurns,
  potNumber,
  season,
  title,
  userCountry,
  year,
}): React.ReactElement {
  const [view, setView] = useState("info");
  const [device] = useDevice();
  const mobileLandscapeLayout =
    device === Device.MOBILE_LANDSCAPE || device === Device.MOBILE_LG_LANDSCAPE;

  const padding = mobileLandscapeLayout ? "0 6px" : "0 16px";

  const renderView = () => {
    if (view === "info") {
      return (
        <Box>
          <Box
            sx={{
              m: "20px 0 10px 0",
              p: padding,
            }}
          >
            <WDInfoDisplay
              alternatives={alternatives}
              device={device}
              potNumber={potNumber}
              season={season}
              title={title}
              year={year}
            />
          </Box>
          <WDInfoPanel
            countries={countries}
            device={device}
            maxDelays={excusedMissedTurns}
            userCountry={userCountry}
          />
        </Box>
      );
    }
    if (view === "press") {
      return <WDPress device={device}>{children}</WDPress>;
    }
    return null;
  };

  return (
    <Box>
      <Stack
        alignItems="center"
        direction="row"
        spacing={2}
        sx={{ p: padding }}
      >
        <Button
          onClick={() => setView("press")}
          sx={view === "press" ? textButtonSelected : textButtonStyle}
        >
          {ModalViews.PRESS}
        </Button>
        <Button
          onClick={() => setView("info")}
          sx={view === "info" ? textButtonSelected : textButtonStyle}
        >
          {ModalViews.INFO}
        </Button>
      </Stack>
      {renderView()}
    </Box>
  );
};

export default WDFullModal;
