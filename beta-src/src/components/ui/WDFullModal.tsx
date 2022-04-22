import * as React from "react";
import { useState } from "react";
import { Box } from "@mui/material";
import WDInfoDisplay from "./WDInfoDisplay";
import { CountryTableData } from "../../interfaces";
import Device from "../../enums/Device";
import WDInfoPanel from "./WDInfoPanel";
import WDPress from "./WDPress";
import ModalViews from "../../enums/ModalViews";
import GameOverviewResponse from "../../state/interfaces/GameOverviewResponse";
import useViewport from "../../hooks/useViewport";
import getDevice from "../../utils/getDevice";
import WDViewsContainer from "./WDViewsContainer";
import WDTabPanel from "./WDTabPanel";

interface WDFullModalProps {
  alternatives: GameOverviewResponse["alternatives"];
  children: React.ReactNode;
  countries: CountryTableData[];
  excusedMissedTurns: GameOverviewResponse["excusedMissedTurns"];
  phase: GameOverviewResponse["phase"];
  potNumber: GameOverviewResponse["pot"];
  season: GameOverviewResponse["season"];
  title: GameOverviewResponse["name"];
  userCountry: CountryTableData;
  year: GameOverviewResponse["year"];
}

const tabGroup: ModalViews[] = [ModalViews.PRESS, ModalViews.INFO];

const WDFullModal: React.FC<WDFullModalProps> = function ({
  alternatives,
  children,
  countries,
  excusedMissedTurns,
  phase,
  potNumber,
  season,
  title,
  userCountry,
  year,
}): React.ReactElement {
  const [view, setView] = useState(ModalViews.PRESS);
  const onChangeView = (tab: ModalViews) => setView(tab);
  const [viewport] = useViewport();
  const device = getDevice(viewport);

  const mobileLandscapeLayout =
    device === Device.MOBILE_LANDSCAPE ||
    device === Device.MOBILE_LG_LANDSCAPE ||
    device === Device.MOBILE;

  const padding = mobileLandscapeLayout ? "0 6px" : "0 16px";

  return (
    <WDViewsContainer
      tabGroup={tabGroup}
      currentView={view}
      onChange={onChangeView}
      padding={padding}
    >
      <WDTabPanel currentTab={ModalViews.INFO} currentView={view}>
        <Box>
          <Box
            sx={{
              m: "20px 0 10px 0",
              p: padding,
            }}
          >
            <WDInfoDisplay
              alternatives={alternatives}
              phase={phase}
              potNumber={potNumber}
              season={season}
              title={title}
              year={year}
            />
          </Box>
          <WDInfoPanel
            countries={countries}
            maxDelays={excusedMissedTurns}
            userCountry={userCountry}
          />
        </Box>
      </WDTabPanel>
      <WDTabPanel currentTab={ModalViews.PRESS} currentView={view}>
        <WDPress>{children}</WDPress>
      </WDTabPanel>
    </WDViewsContainer>
  );
};

export default WDFullModal;
