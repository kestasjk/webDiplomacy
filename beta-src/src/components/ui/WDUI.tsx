import * as React from "react";
import { Box, IconButton, Link, useTheme, Badge } from "@mui/material";

import WDPositionContainer from "./WDPositionContainer";
import Position from "../../enums/Position";
import { useAppSelector, useAppDispatch } from "../../state/hooks";
import { gameOverview } from "../../state/game/game-api-slice";
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
  console.log("WDUI rerendered");

  const [showControlModal, setShowControlModal] = React.useState(false);
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
  const newMessagesFrom = useAppSelector(
    ({ game }) => game.messages.newMessagesFrom,
  );

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
  countries.sort((x, y) => x.countryID - y.countryID);

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

  const checkIfTriggerVisible = (i = 0) => {
    if (i > 10) {
      return;
    }
    if (popoverTrigger.current) {
      const rect = popoverTrigger.current.getBoundingClientRect();
      if (!rect.width || !rect.height) {
        setTimeout(() => {
          checkIfTriggerVisible(i + 1);
        }, 500);
      } else {
        toggleControlModal();
      }
    }
  };

  React.useEffect(() => {
    checkIfTriggerVisible();
  }, [popoverTrigger]);

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
          {newMessagesFrom.length ? (
            <Badge badgeContent={newMessagesFrom} color="secondary">
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
            bgcolor: "#fff",
            color: "grey.800",
            border: "1px solid",
            borderColor: "grey.300",
            borderRadius: 2,
            fontSize: "0.875rem",
            fontWeight: "700",
          }}
        >
          {abbrMap[user.member.country]}
        </Box>
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
