import React, { useEffect } from "react";
import { Box, IconButton, Link, useTheme, Badge } from "@mui/material";

import WDPositionContainer from "./WDPositionContainer";
import Position from "../../enums/Position";
import { useAppSelector, useAppDispatch } from "../../state/hooks";
import { CountryTableData } from "../../interfaces";
import Country from "../../enums/Country";
import WDFullModal from "./WDFullModal";
import WDPopover from "./WDPopover";
import WDActionIcon from "./icons/WDActionIcon";
import WDPhaseUI from "./WDPhaseUI";
import UIState from "../../enums/UIState";
import Vote from "../../enums/Vote";
import WDOrderStatusControls from "./WDOrderStatusControls";
import countryMap from "../../data/map/variants/classic/CountryMap";
import WDHomeIcon from "./icons/WDHomeIcon";
import WDBuildCounts from "./WDBuildCounts";
import ModalViews from "../../enums/ModalViews";
import {
  gameOverview,
  fetchGameMessages,
  gameApiSliceActions,
  gameMaps,
  gameViewedPhase,
} from "../../state/game/game-api-slice";
import useInterval from "../../hooks/useInterval";
import useOutsideAlerter from "../../hooks/useOutsideAlerter";
import useViewport from "../../hooks/useViewport";
import { store } from "../../state/store";
import { MessageStatus } from "../../state/interfaces/GameMessages";
import { IOrderDataHistorical } from "../../models/Interfaces";
import WDGameFinishedOverlay from "./WDGameFinishedOverlay";
import { Unit } from "../../utils/map/getUnits";
import WDLoading from "../miscellaneous/Loading";

const abbrMap = {
  Russia: "RUS",
  Germany: "GER",
  Italy: "ITA",
  Austria: "AUS",
  England: "ENG",
  France: "FRA",
  Turkey: "TUR",
};

interface WDUIProps {
  orders: IOrderDataHistorical[];
  units: Unit[];
  viewingGameFinishedPhase: boolean;
}

const WDUI: React.FC<WDUIProps> = function ({
  orders,
  units,
  viewingGameFinishedPhase,
}): React.ReactElement {
  const theme = useTheme();

  const [showControlModal, setShowControlModal] = React.useState(false);
  const popoverTrigger = React.useRef<HTMLElement>(null);
  const modalRef = React.useRef<HTMLElement>(null);
  const {
    alternatives,
    anon,
    drawType,
    excusedMissedTurns,
    gameID,
    members,
    name,
    phase,
    pot,
    pressType,
    season,
    user,
    year,
    processStatus,
  } = useAppSelector(gameOverview);
  if (phase === "Error" || phase === "Pre-game") return <div />;

  const maps = useAppSelector(gameMaps);

  // console.log("WDUI RENDERED");

  const messages = useAppSelector(({ game }) => game.messages.messages);
  const numUnread = messages.reduce(
    (acc, m) => acc + Number(m.status === MessageStatus.UNREAD),
    0,
  );
  const numUnknown = messages.reduce(
    (acc, m) => acc + Number(m.status === MessageStatus.UNKNOWN),
    0,
  );

  const constructTableData = (member) => {
    const memberCountry: Country = countryMap[member.country];
    return {
      ...member,
      abbr: abbrMap[member.country],
      color: theme.palette[memberCountry].main,
      power: memberCountry,
      votes: member.votes,
    };
  };

  const allCountries: CountryTableData[] = [];
  const getCountrySortIdx = function (countryID: number) {
    // Sort user country to the front
    if (countryID === user?.member.countryID) return -1;
    return countryID;
  };

  members.forEach((member) => {
    allCountries.push(constructTableData(member));
  });
  allCountries.sort(
    (x, y) => getCountrySortIdx(x.countryID) - getCountrySortIdx(y.countryID),
  );

  const userTableData = user ? constructTableData(user.member) : null;

  const closeControlModal = () => {
    setShowControlModal(false);
  };

  const [viewport] = useViewport();
  useOutsideAlerter([modalRef, popoverTrigger, viewport], () => {
    // if viewport is too small to do chat and map at same time,
    // then close the modal on outside click.
    if (viewport.width <= theme.breakpoints.values.mobileLandscape) {
      closeControlModal();
    }
  });

  const toggleControlModal = () => {
    setShowControlModal(!showControlModal);
  };

  const controlModalTrigger = (
    <IconButton
      sx={{ padding: 0, pointerEvents: "all" }}
      onClick={toggleControlModal}
    >
      <WDActionIcon
        iconState={showControlModal ? UIState.ACTIVE : UIState.INACTIVE}
      />
    </IconButton>
  );

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

  const gameIsFinished = phase === "Finished";

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

  useEffect(() => {
    setShowControlModal(true);
  }, []);

  const popover = popoverTrigger.current ? (
    <WDPopover
      isOpen={showControlModal}
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

  return (
    <>
      <WDLoading percentage={80} />
      <WDPositionContainer position={Position.TOP_RIGHT}>
        <Link href="/">
          <WDHomeIcon />
        </Link>

        <Box
          sx={{
            pt: "15px",
            pointerEvents: "auto",
          }}
          ref={popoverTrigger}
        >
          {numUnread + numUnknown ? (
            <Badge badgeContent={numUnknown ? " " : numUnread} color="error">
              {controlModalTrigger}
            </Badge>
          ) : (
            controlModalTrigger
          )}
        </Box>
        {user && (
          <Box
            component="div"
            sx={{
              display: "block",
              p: 1,
              mt: 2,
              bgcolor: theme.palette[user.member.country]?.light,
              color: "black",
              border: "1px solid",
              borderColor: "grey.300",
              borderRadius: 2,
              fontSize: "0.875rem",
              fontWeight: "700",
              userSelect: "none",
            }}
            title={`Currently playing as ${user.member.country}`}
          >
            {abbrMap[user.member.country]}
          </Box>
        )}
        <WDBuildCounts />
        {popover}
      </WDPositionContainer>
      <WDPositionContainer position={Position.TOP_LEFT}>
        <WDPhaseUI />
      </WDPositionContainer>
      {user && !gameIsFinished && (
        <WDPositionContainer position={Position.BOTTOM_RIGHT}>
          <WDOrderStatusControls orderStatus={user.member.orderStatus} />
        </WDPositionContainer>
      )}
      {gameIsFinished && viewingGameFinishedPhase && (
        <WDGameFinishedOverlay allCountries={allCountries} />
      )}
    </>
  );
};

export default WDUI;
