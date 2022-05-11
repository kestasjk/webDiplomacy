import * as React from "react";
import { Box, IconButton, Link, useTheme } from "@mui/material";

import WDPositionContainer from "./WDPositionContainer";
import Position from "../../enums/Position";
import { useAppSelector } from "../../state/hooks";
import { gameData, gameOverview } from "../../state/game/game-api-slice";
import { CountryTableData } from "../../interfaces";
import Country from "../../enums/Country";
import WDFullModal from "./WDFullModal";
import WDPopover from "./WDPopover";
import WDActionIcon from "./icons/WDActionIcon";
import WDPhaseUI from "./WDPhaseUI";
import UIState from "../../enums/UIState";
import capitalizeString from "../../utils/capitalizeString";
import Vote from "../../enums/Vote";
import Move from "../../enums/Move";
import WDMoveControls from "./WDMoveControls";
import MoveStatus from "../../types/MoveStatus";
import countryMap from "../../data/map/variants/classic/CountryMap";
import WDHomeIcon from "./icons/WDHomeIcon";
import getOrderStates from "../../utils/state/getOrderStates";

const abbrMap = {
  Russia: "RUS",
  Germany: "GER",
  Italy: "ITA",
  Austria: "AUS",
  England: "ENG",
  France: "FRA",
  Turkey: "TUR",
};
/**
 * the game status data created here is for displaying purpose
 * the real gamestatus data will be provided from Redux Store?
 */
const gameStatusData: MoveStatus = {
  save: false,
  ready: false,
};

const WDUI: React.FC = function (): React.ReactElement {
  const theme = useTheme();

  const [showControlModal, setShowControlModal] = React.useState(false);
  const [readyDisabled, setReadyDisabled] = React.useState(false);
  const popoverTrigger = React.useRef<HTMLElement>(null);

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

  const {
    data: { contextVars },
  } = useAppSelector(gameData);

  const constructTableData = (member) => {
    const memberCountry: Country = countryMap[member.country];
    return {
      ...member,
      abbr: abbrMap[member.country],
      color: theme.palette[memberCountry].main,
      power: memberCountry,
      votes: {
        cancel: member.votes.includes(capitalizeString(Vote[Vote.cancel])),
        draw: member.votes.includes(capitalizeString(Vote[Vote.draw])),
        pause: member.votes.includes(capitalizeString(Vote[Vote.pause])),
      },
    };
  };

  const countries: CountryTableData[] = [];

  members.forEach((member) => {
    if (member.userID !== user.member.userID) {
      countries.push(constructTableData(member));
    }
  });

  const userTableData = constructTableData(user.member);

  const closeControlModal = () => {
    setShowControlModal(false);
  };

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

  const [gameState, setGameState] = React.useState(gameStatusData);
  const toggleState = (move: Move) => {
    setGameState((preState) => ({
      ...preState,
      [move]: !gameState[move],
    }));
  };

  React.useEffect(() => {
    if (popoverTrigger.current) {
      setTimeout(() => {
        toggleControlModal();
      }, 1000);
    }
  }, [popoverTrigger]);

  if (contextVars?.context?.orderStatus) {
    const orderStates = getOrderStates(contextVars.context.orderStatus);
    if (orderStates.None) {
      setReadyDisabled(true);
    }
    if (!orderStates.Ready && gameState.ready) {
      setGameState((preState) => ({
        ...preState,
        [Move.READY]: false,
      }));
    }
    if ((orderStates.Ready || orderStates.None) && !gameState.ready) {
      setGameState((preState) => ({
        ...preState,
        [Move.READY]: true,
      }));
    }
  }

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
          {controlModalTrigger}
        </Box>
        {popover}
      </WDPositionContainer>
      <WDPositionContainer position={Position.TOP_LEFT}>
        <WDPhaseUI />
      </WDPositionContainer>
      <WDPositionContainer position={Position.BOTTOM_RIGHT}>
        <WDMoveControls
          readyDisabled={readyDisabled}
          gameState={gameState}
          toggleState={toggleState}
        />
      </WDPositionContainer>
    </>
  );
};

export default WDUI;
