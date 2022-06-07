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
import WDOrdersPanel from "./WDOrdersPanel";
import { IOrderDataHistorical } from "../../models/Interfaces";
import GameStateMaps from "../../state/interfaces/GameStateMaps";

interface WDFullModalProps {
  alternatives: GameOverviewResponse["alternatives"];
  children: React.ReactNode;
  allCountries: CountryTableData[];
  excusedMissedTurns: GameOverviewResponse["excusedMissedTurns"];
  maps: GameStateMaps;
  orders: IOrderDataHistorical[];
  phase: GameOverviewResponse["phase"];
  potNumber: GameOverviewResponse["pot"];
  gameID: GameOverviewResponse["gameID"];
  season: GameOverviewResponse["season"];
  title: GameOverviewResponse["name"];
  userCountry: CountryTableData;
  year: GameOverviewResponse["year"];
  modalRef: React.RefObject<HTMLElement>;
}

const tabGroup: ModalViews[] = [
  ModalViews.PRESS,
  ModalViews.INFO,
  ModalViews.ORDERS,
];

const WDFullModal: React.FC<WDFullModalProps> = function ({
  alternatives,
  children,
  allCountries,
  excusedMissedTurns,
  gameID,
  maps,
  orders,
  phase,
  potNumber,
  season,
  title,
  userCountry,
  year,
  modalRef,
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
    <Box ref={modalRef}>
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
              allCountries={allCountries}
              maxDelays={excusedMissedTurns}
              userCountry={userCountry}
              gameID={gameID}
            />
          </Box>
        </WDTabPanel>
        <WDTabPanel currentTab={ModalViews.PRESS} currentView={view}>
          <WDPress userCountry={userCountry} allCountries={allCountries}>
            {children}
          </WDPress>
        </WDTabPanel>
        <WDTabPanel currentTab={ModalViews.ORDERS} currentView={view}>
          <WDOrdersPanel
            orders={orders}
            allCountries={allCountries}
            maps={maps}
          />
        </WDTabPanel>
      </WDViewsContainer>
    </Box>
  );
};

export default WDFullModal;
