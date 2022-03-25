import * as React from "react";
import { Box, useTheme } from "@mui/material";

import WDPositionContainer from "./WDPositionContainer";
import Position from "../../enums/Position";
import WDInfoDisplay from "./WDInfoDisplay";
import { useAppSelector } from "../../state/hooks";
import { gameOverview } from "../../state/game/game-api-slice";
import WDInfoPanel from "./WDInfoPanel";
import Device from "../../enums/Device";
import { CountryTableData } from "../../interfaces";
import Country from "../../enums/Country";
import WDPhaseUI from "./WDPhaseUI";

interface WDUIProps {
  device: Device;
}

const WDUI: React.FC<WDUIProps> = function ({ device }): React.ReactElement {
  const theme = useTheme();
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
  console.log({
    alternatives,
    name,
    pot,
    season,
    year,
  });

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
  console.log({
    userTableData,
    countries,
  });

  return (
    <>
      <WDPositionContainer position={Position.TOP_RIGHT}>
        <Box sx={{ background: "white" }}>
          <Box sx={{ padding: 2 }}>
            <WDInfoDisplay
              potNumber={pot}
              alternatives={alternatives}
              season={season}
              title={name}
              year={year}
            />
          </Box>
          <Box sx={{ padding: 2 }}>
            <WDInfoPanel
              countries={countries}
              device={device}
              userCountry={userTableData}
              maxDelays={excusedMissedTurns}
            />
          </Box>
        </Box>
      </WDPositionContainer>
      <WDPositionContainer position={Position.TOP_LEFT}>
        <WDPhaseUI />
      </WDPositionContainer>
    </>
  );
};

export default WDUI;
