import * as React from "react";
import { useState } from "react";
import { Box, Stack, Button } from "@mui/material";
import WDInfoDisplay from "./WDInfoDisplay";
import GameMode from "../../enums/GameMode";
import Season from "../../enums/Season";
import Ranking from "../../enums/Ranking";
import IntegerRange from "../../types/IntegerRange";
import GameType from "../../enums/GameType";
import { CountryTableData } from "../../interfaces";
import Device from "../../enums/Device";
import WDInfoPanel from "./WDInfoPanel";
import WDPress from "./WDPress";
import ModalViews from "../../enums/ModalViews";

interface WDFullModalProps {
  children: React.ReactNode;
  countries: CountryTableData[];
  device: Device;
  gameMode: GameMode;
  gameType: GameType;
  maxDelays: IntegerRange<0, 5>;
  potNumber: IntegerRange<35, 665>;
  rank: Ranking;
  season: Season;
  title: string;
  userCountry: CountryTableData;
  year: number;
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
  children,
  countries,
  device,
  gameMode,
  gameType,
  maxDelays,
  potNumber,
  rank,
  season,
  title,
  userCountry,
  year,
}): React.ReactElement {
  const [view, setView] = useState("info");
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
              device={device}
              gameMode={gameMode}
              gameType={gameType}
              potNumber={potNumber}
              rank={rank}
              season={season}
              title={title}
              year={year}
            />
          </Box>
          <WDInfoPanel
            countries={countries}
            device={device}
            maxDelays={maxDelays}
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
