import React, {
  ReactElement,
  FunctionComponent,
  useEffect,
  useState,
} from "react";
import { IconButton, useTheme, Badge } from "@mui/material";

import { useAppSelector, useAppDispatch } from "../../../../state/hooks";
import WDPopover from "../../WDPopover";
import useOutsideAlerter from "../../../../hooks/useOutsideAlerter";
import useViewport from "../../../../hooks/useViewport";
import WDActionIcon from "../../icons/WDActionIcon";
import UIState from "../../../../enums/UIState";
import WDFullModal from "../../WDFullModal";
import WDBuildCounts from "../../WDBuildCounts";
import ModalViews from "../../../../enums/ModalViews";
import { IOrderDataHistorical } from "../../../../models/Interfaces";
import { Unit } from "../../../../utils/map/getUnits";
import { CountryTableData } from "../../../../interfaces";
import {
  gameOverview,
  fetchGameMessages,
  gameMaps,
} from "../../../../state/game/game-api-slice";
import useInterval from "../../../../hooks/useInterval";
import { store } from "../../../../state/store";
import { MessageStatus } from "../../../../state/interfaces/GameMessages";
import RightButton from "./RightButton";
import { abbrMap } from "../../../../enums/Country";
import useComponentVisible from "../../../../hooks/useComponentVisible";

interface BottomRightProps {
  orders: IOrderDataHistorical[];
  units: Unit[];
  allCountries: CountryTableData[];
  userTableData: any;
}

const TopRight: FunctionComponent<BottomRightProps> = function ({
  orders,
  units,
  allCountries,
  userTableData,
}: BottomRightProps): ReactElement {
  const theme = useTheme();
  const popoverTrigger = React.useRef<HTMLDivElement>(null);
  const {
    ref: modalRef,
    isComponentVisible,
    setIsComponentVisible,
  } = useComponentVisible(true);
  const [viewport] = useViewport();

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

  // FIXME: for now, crazily fetch all messages every 2sec
  useInterval(dispatchFetchMessages, 2000);

  const closeControlModal = () => {
    setIsComponentVisible(false);
  };

  const toggleControlModal = () => {
    setIsComponentVisible(!isComponentVisible);
  };

  useEffect(() => {
    setIsComponentVisible(true);
  }, []);

  const controlModalTrigger = (
    <RightButton
      image="action"
      text={abbrMap[user?.member.country || ""]}
      onClick={toggleControlModal}
      className="mb-6"
    />
  );
  // TODO: where to show this?:
  // iconState={showControlModal ? UIState.ACTIVE : UIState.INACTIVE}

  useOutsideAlerter([modalRef, popoverTrigger, viewport], () => {
    // if viewport is too small to do chat and map at same time,
    // then close the modal on outside click.
    if (viewport.width <= theme.breakpoints.values.mobileLandscape) {
      closeControlModal();
    }
  });

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
    <WDPopover
      isOpen={isComponentVisible}
      onClose={closeControlModal}
      anchorEl={popoverTrigger.current}
    >
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
        modalRef={modalRef}
        gameIsPaused={processStatus === "Paused"}
        defaultView={
          pressType === "NoPress" ? ModalViews.INFO : ModalViews.PRESS
        }
      >
        {null}
      </WDFullModal>
    </WDPopover>
  ) : null;

  if (phase === "Error" || phase === "Pre-game") return <div />;

  return (
    <>
      <div className="pt-3 pointer-events-auto" ref={popoverTrigger}>
        {numUnread + numUnknown ? (
          <Badge badgeContent={numUnknown ? " " : numUnread} color="error">
            {controlModalTrigger}
          </Badge>
        ) : (
          controlModalTrigger
        )}
      </div>
      <WDBuildCounts />
      {popover}
    </>
  );
};

export default TopRight;
