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
import UIState from "../../enums/UIState";

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

  const constructTableData = (member) => {
    const memberCountry: Country = countryMap[member.country];
    return {
      ...member,
      abbr: abbrMap[member.country],
      color: theme.palette[memberCountry].main,
      power: memberCountry,
      votes: {
        cancel: member.votes.includes("Cancel"),
        draw: member.votes.includes("Draw"),
        pause: member.votes.includes("Pause"),
      },
    };
  };

  const countries: CountryTableData[] = members
    .map((member) => {
      const isUser = member.userID === user.member.userID;
      const memberData = isUser ? null : constructTableData(member);
      return memberData;
    })
    .filter((data) => !!data);

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
  );
};

export default WDUI;
