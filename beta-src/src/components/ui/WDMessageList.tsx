import * as React from "react";
import { Box, Stack } from "@mui/material";
import WDMessage from "./WDMessage";
import { GameMessage } from "../../state/interfaces/GameMessages";
import { CountryTableData } from "../../interfaces/CountryTableData";
import WDVerticalScroll from "./WDVerticalScroll";
import { getPhaseSeasonYear } from "../../utils/state/getPhaseSeasonYear";

interface WDMessageListProps {
  messages: GameMessage[];
  userCountry: CountryTableData | null;
  allCountries: CountryTableData[];
  countryIDSelected: number;
  messagesEndRef: React.RefObject<HTMLDivElement>;
}

const WDMessageList: React.FC<WDMessageListProps> = function ({
  messages,
  userCountry,
  allCountries,
  countryIDSelected,
  messagesEndRef,
}): React.ReactElement {
  const filteredMessages = messages.filter(
    (message) =>
      (message.fromCountryID === countryIDSelected ||
        message.toCountryID === countryIDSelected) &&
      (countryIDSelected === 0 || message.toCountryID !== 0), // public messages in public chat
  );
  const messagesByTurn: { [key: number]: React.ReactElement[] } = {};
  filteredMessages.forEach((message: GameMessage) => {
    if (!(message.turn in messagesByTurn)) {
      messagesByTurn[message.turn] = [];
    }
    messagesByTurn[message.turn].push(
      <WDMessage
        key={`${message.timeSent}:${message.fromCountryID}:${message.toCountryID}:${message.message}`}
        message={message}
        userCountry={userCountry}
        allCountries={allCountries}
      />,
    );
  });
  const messageTurnComponents = Object.entries(messagesByTurn).map(
    ([turn, msgs]) => {
      const psy = getPhaseSeasonYear(Number(turn), "Diplomacy");
      return (
        <Box key={turn}>
          <Box
            sx={{
              textAlign: "center",
              color: "#666",
              fontWeight: 500,
              p: "6px",
            }}
          >
            {psy.season} {psy.year}
          </Box>
          {msgs}
        </Box>
      );
    },
  );

  return (
    <WDVerticalScroll>
      <Stack direction="column">{messageTurnComponents}</Stack>
      <Box ref={messagesEndRef} />
    </WDVerticalScroll>
  );
};

export default WDMessageList;
