import * as React from "react";
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
import capitalizeString from "../../utils/capitalizeString";
import Vote from "../../enums/Vote";
import WDMoveControls from "./WDMoveControls";
import countryMap from "../../data/map/variants/classic/CountryMap";
import WDHomeIcon from "./icons/WDHomeIcon";
import WDBuildCounts from "./WDBuildCounts";
import {
  gameOverview,
  fetchGameMessages,
  gameApiSliceActions,
} from "../../state/game/game-api-slice";
import useInterval from "../../hooks/useInterval";
import useOutsideAlerter from "../../hooks/useOutsideAlerter";
import useViewport from "../../hooks/useViewport";
import { store } from "../../state/store";
import { MessageStatus } from "../../state/interfaces/GameMessages";

const abbrMap = {
  Russia: "RUS",
  Germany: "GER",
  Italy: "ITA",
  Austria: "AUS",
  England: "ENG",
  France: "FRA",
  Turkey: "TUR",
};

const WDUI: React.FC = function (): React.ReactElement {
  const theme = useTheme();

  const [showControlModal, setShowControlModal] = React.useState(false);
  const popoverTrigger = React.useRef<HTMLElement>(null);
  const modalRef = React.useRef<HTMLElement>(null);

  const {
    alternatives,
    excusedMissedTurns,
    gameID,
    members,
    name,
    phase,
    pot,
    season,
    user,
    year,
  } = useAppSelector(gameOverview);

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

  const countries: CountryTableData[] = [];

  members.forEach((member) => {
    if (member.userID !== user.member.userID) {
      countries.push(constructTableData(member));
    }
  });
  countries.sort((x, y) => x.countryID - y.countryID);

  const userTableData = constructTableData(user.member);

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
    if (!outstandingMessageRequests) {
      dispatch(
        fetchGameMessages({
          gameID: String(gameID),
          countryID: String(userTableData.countryID),
          allMessages: "true",
          sinceTime: String(game.messages.time),
        }),
      );
    }
  };

  // FIXME: for now, crazily fetch all messages every 2sec
  useInterval(dispatchFetchMessages, 2000);

  const popover = popoverTrigger.current ? (
    <WDPopover
      isOpen={showControlModal}
      onClose={closeControlModal}
      anchorEl={popoverTrigger.current}
    >
      <WDFullModal
        alternatives={alternatives}
        countries={countries}
        excusedMissedTurns={excusedMissedTurns}
        gameID={gameID}
        phase={phase}
        potNumber={pot}
        season={season}
        title={name}
        userCountry={userTableData}
        year={year}
        modalRef={modalRef}
      >
        {null}
      </WDFullModal>
    </WDPopover>
  ) : null;

  return (
    <>
      <WDPositionContainer position={Position.TOP_RIGHT}>
        <Link href="/">
          <WDHomeIcon />
        </Link>

        <Box
          sx={{
            pt: "15px",
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
        <WDBuildCounts />
        {popover}
      </WDPositionContainer>
      <WDPositionContainer position={Position.TOP_LEFT}>
        <WDPhaseUI />
      </WDPositionContainer>
      <WDPositionContainer position={Position.BOTTOM_RIGHT}>
        <WDMoveControls />
      </WDPositionContainer>
    </>
  );
};

export default WDUI;
