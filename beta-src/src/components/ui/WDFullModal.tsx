import * as React from "react";
import { useState } from "react";
import WDInfoDisplay from "./WDInfoDisplay";
import { CountryTableData } from "../../interfaces";
import WDInfoPanel from "./WDInfoPanel";
import WDPress from "./WDPress";
import ModalViews from "../../enums/ModalViews";
import GameOverviewResponse from "../../state/interfaces/GameOverviewResponse";
import WDViewsContainer from "./WDViewsContainer";
import WDTabPanel from "./WDTabPanel";
import WDOrdersPanel from "./WDOrdersPanel";
import { IOrderDataHistorical } from "../../models/Interfaces";
import GameStateMaps from "../../state/interfaces/GameStateMaps";
import { Unit } from "../../utils/map/getUnits";
import WDGamesList from "./WDGamesList";
import WDHelp from "./WDHelp";
import WDSettings from "./WDSettings";

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
  units: Unit[];
  userCountry: CountryTableData | null;
  year: GameOverviewResponse["year"];
  modalRef?: React.RefObject<HTMLDivElement>;
  gameIsPaused: boolean;
  defaultView: ModalViews;
  onChangeView: (view: ModalViews) => void;
}

const tabGroup: ModalViews[] = [
  ModalViews.PRESS,
  ModalViews.INFO,
  ModalViews.SETTINGS,
  ModalViews.ORDERS,
  ModalViews.GAMES,
  ModalViews.HELP,
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
  units,
  userCountry,
  year,
  modalRef,
  gameIsPaused,
  defaultView,
  onChangeView,
}): React.ReactElement {
  const [view, setView] = useState(defaultView);
  const handleOnChangeView = (tab: ModalViews) => {
    setView(tab);
    onChangeView(tab);
  };
  const gameIsFinished = phase === "Finished";

  return (
    <div ref={modalRef}>
      <WDViewsContainer
        tabGroup={tabGroup}
        currentView={view}
        onChange={handleOnChangeView}
      >
        <WDTabPanel currentTab={ModalViews.INFO} currentView={view}>
          <div>
            <div className="mt-3 mb-2 px-3 sm:px-4">
              <WDInfoDisplay
                alternatives={alternatives}
                phase={phase}
                potNumber={potNumber}
                season={season}
                title={title}
                year={year}
              />
            </div>

            <WDInfoPanel
              allCountries={allCountries}
              maxDelays={excusedMissedTurns}
              userCountry={userCountry}
              gameID={gameID}
              gameIsFinished={gameIsFinished}
              gameIsPaused={gameIsPaused}
            />
          </div>
        </WDTabPanel>
        <WDTabPanel currentTab={ModalViews.PRESS} currentView={view}>
          <WDPress userCountry={userCountry} allCountries={allCountries}>
            {children}
          </WDPress>
        </WDTabPanel>
        <WDTabPanel currentTab={ModalViews.SETTINGS} currentView={view}>
          <WDSettings
            allCountries={allCountries}
            maxDelays={excusedMissedTurns}
            userCountry={userCountry}
            gameID={gameID}
            gameIsFinished={gameIsFinished}
            gameIsPaused={gameIsPaused}
          />
        </WDTabPanel>
        <WDTabPanel currentTab={ModalViews.ORDERS} currentView={view}>
          <WDOrdersPanel
            orders={orders}
            units={units}
            allCountries={allCountries}
            maps={maps}
          />
        </WDTabPanel>
        <WDTabPanel currentTab={ModalViews.GAMES} currentView={view}>
          <WDGamesList />
        </WDTabPanel>
        <WDTabPanel currentTab={ModalViews.HELP} currentView={view}>
          <WDHelp />
        </WDTabPanel>
      </WDViewsContainer>
    </div>
  );
};

WDFullModal.defaultProps = { modalRef: undefined };

export default WDFullModal;
