import * as React from "react";
import { useTheme } from "@mui/material";

import WDPositionContainer from "./WDPositionContainer";
import Position from "../../enums/Position";
import { useAppSelector } from "../../state/hooks";
import { gameOverview } from "../../state/game/game-api-slice";
import { CountryTableData } from "../../interfaces";
import Country from "../../enums/Country";
import WDFullModal from "./WDFullModal";
import WDPopover from "./WDPopover";
import WDActionIcon from "../svgr-components/WDActionIcon";
import WDPhaseUI from "./WDPhaseUI";
import UIState from "../../enums/UIState";
import capitalizeString from "../../utils/capitalizeString";
import Vote from "../../enums/Vote";

const countryMap = {
  Russia: Country.RUSSIA,
  Germany: Country.GERMANY,
  Italy: Country.ITALY,
  Austria: Country.AUSTRIA,
  England: Country.ENGLAND,
  France: Country.FRANCE,
  Turkey: Country.TURKEY,
};

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

  const {
    alternatives,
    excusedMissedTurns,
    members,
    name,
    pot,
    season,
    user,
    year,
  } = useAppSelector(gameOverview);

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

  const openControlModal = () => {
    setShowControlModal(true);
  };

  const closeControlModal = () => {
    setShowControlModal(false);
  };

  const controlModalTrigger = (
    <WDActionIcon
      iconState={showControlModal ? UIState.ACTIVE : UIState.INACTIVE}
    />
  );

  return (
    <>
      <WDPositionContainer position={Position.TOP_RIGHT}>
        <WDPopover
          isOpen={showControlModal}
          open={openControlModal}
          onClose={closeControlModal}
          popoverTrigger={controlModalTrigger}
        >
          <WDFullModal
            alternatives={alternatives}
            countries={countries}
            excusedMissedTurns={excusedMissedTurns}
            potNumber={pot}
            season={season}
            title={name}
            userCountry={userTableData}
            year={year}
          >
            {null}
          </WDFullModal>
        </WDPopover>
      </WDPositionContainer>
      <WDPositionContainer position={Position.TOP_LEFT}>
        <WDPhaseUI />
      </WDPositionContainer>
    </>
  );
};

export default WDUI;
