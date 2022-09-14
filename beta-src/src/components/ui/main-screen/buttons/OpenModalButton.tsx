import React, {
  ReactElement,
  FunctionComponent,
  useEffect,
  useState,
} from "react";
import { useKeyPressEvent, useWindowSize } from "react-use";
import { Badge } from "@mui/material";

import { useAppSelector, useAppDispatch } from "../../../../state/hooks";
import WDPopover from "../../WDPopover";
import WDFullModal from "../../WDFullModal";
import ModalViews from "../../../../enums/ModalViews";
import { IOrderDataHistorical } from "../../../../models/Interfaces";
import { Unit } from "../../../../utils/map/getUnits";
import { CountryTableData } from "../../../../interfaces";
import {
  gameOverview,
  fetchGameMessages,
  gameMaps,
} from "../../../../state/game/game-api-slice";
import { store } from "../../../../state/store";
import { MessageStatus } from "../../../../state/interfaces/GameMessages";
import RightButton from "./RightButton";
import { abbrMap } from "../../../../enums/Country";
import useComponentVisible from "../../../../hooks/useComponentVisible";
import client from "../../../../lib/pusher";

interface BottomRightProps {
  orders: IOrderDataHistorical[];
  units: Unit[];
  allCountries: CountryTableData[];
  userTableData: any;
}

const OpenModalButton: FunctionComponent<BottomRightProps> = function ({
  orders,
  units,
  allCountries,
  userTableData,
}: BottomRightProps): ReactElement {
  const { width } = useWindowSize();
  const popoverTrigger = React.useRef<HTMLDivElement>(null);
  const [currentTab, setCurrentTab] = useState(ModalViews.PRESS);
  const {
    ref: modalRef,
    isComponentVisible,
    setIsComponentVisible,
  } = useComponentVisible(false, width < 1000);

  const {
    alternatives,
    anon,
    excusedMissedTurns,
    gameID,
    name,
    phase,
    pot,
    pressType,
    season,
    user,
    year,
    processStatus,
  } = useAppSelector(gameOverview);

  const maps = useAppSelector(gameMaps);

  const dispatch = useAppDispatch();
  const dispatchFetchMessages = () => {
    const { game } = store.getState();
    const { outstandingMessageRequests } = game;
    if (!outstandingMessageRequests && phase !== "Pre-game") {
      dispatch(
        fetchGameMessages({
          gameID: String(gameID),
          countryID: user ? String(user.member.countryID) : undefined,
          sinceTime: String(game.messages.time),
        }),
      );
    }
  };

  const countryID = user?.member.countryID;
  useEffect(() => {
    if (gameID === 0) return;
    dispatchFetchMessages();
    console.log("creating press socket");
    const channel = client.subscribe(
      `private-game${gameID}-country${user?.member.countryID}`,
    );

    channel.bind("message", (message) => {
      // it would be ideal to push the message in the state manager but
      // I couldn't find an elegant way to do it with redux-toolkit
      // come back to this later
      dispatchFetchMessages();
    });

    channel.bind("pusher:subscription_succeeded", () => {
      // eslint-disable-next-line no-console
      console.info("messages subscription succeeded");
    });

    channel.bind("pusher:subscription_error", (data) => {
      // eslint-disable-next-line no-console
      console.error("messages subscription error", data);
    });
  }, [gameID, countryID]);

  const toggleControlModal = () => {
    setIsComponentVisible(!isComponentVisible);
  };
  const inputMessage = document.getElementById("user-msg");
  useKeyPressEvent("p", () => {
    if (inputMessage !== document.activeElement) {
      toggleControlModal();
    }
  });

  const controlModalTrigger = (
    <RightButton
      image="action"
      text={abbrMap[user?.member.country || ""]}
      onClick={toggleControlModal}
    />
  );
  // TODO: where to show this?:
  // iconState={showControlModal ? UIState.ACTIVE : UIState.INACTIVE}

  const messages = useAppSelector(({ game }) => game.messages.messages);
  const numUnread = messages.reduce(
    (acc, m) => acc + Number(m.status === MessageStatus.UNREAD),
    0,
  );
  const numUnknown = messages.reduce(
    (acc, m) => acc + Number(m.status === MessageStatus.UNKNOWN),
    0,
  );

  let moreAlternatives = alternatives;
  if (anon === "Yes") {
    moreAlternatives += ", Anonymous";
  }
  switch (pressType) {
    case "Regular":
      moreAlternatives += ", Regular Press";
      break;
    case "PublicPressOnly":
      moreAlternatives += ", Public Press Only";
      break;
    case "NoPress":
      moreAlternatives += ", Gunboat (no press)";
      break;
    case "RulebookPress":
      moreAlternatives += ", Rulebook Press";
      break;
    default:
      break;
  }

  const popover = popoverTrigger.current ? (
    <WDPopover isOpen={isComponentVisible}>
      <WDFullModal
        alternatives={moreAlternatives}
        allCountries={allCountries}
        excusedMissedTurns={excusedMissedTurns}
        gameID={gameID}
        maps={maps}
        orders={orders}
        phase={phase}
        potNumber={pot}
        season={season}
        title={name}
        units={units}
        userCountry={userTableData}
        year={year}
        gameIsPaused={processStatus === "Paused"}
        defaultView={
          pressType === "NoPress" ? ModalViews.INFO : ModalViews.PRESS
        }
        onChangeView={setCurrentTab}
      >
        {null}
      </WDFullModal>
    </WDPopover>
  ) : null;

  if (phase === "Error" || phase === "Pre-game") return <div />;

  return (
    <div ref={modalRef}>
      <div
        className="pointer-events-auto h-[66px] has-tooltip"
        ref={popoverTrigger}
        title="Toggle open with 'P'"
      >
        {numUnread + numUnknown ? (
          <Badge badgeContent={numUnknown ? " " : numUnread} color="error">
            {controlModalTrigger}
          </Badge>
        ) : (
          controlModalTrigger
        )}
      </div>
      {popover}
    </div>
  );
};

export default OpenModalButton;
