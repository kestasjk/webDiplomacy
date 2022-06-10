import * as React from "react";
import { Box, Stack } from "@mui/material";
import Device from "../../enums/Device";
import useViewport from "../../hooks/useViewport";
import getDevice from "../../utils/getDevice";
import WDButton from "./WDButton";
import WDMessage from "./WDMessage";
import { GameMessage } from "../../state/interfaces/GameMessages";
import { CountryTableData } from "../../interfaces/CountryTableData";
import WDVerticalScroll from "./WDVerticalScroll";

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
  const [viewport] = useViewport();
  const device = getDevice(viewport);
  const height = "350px";
  const filteredMessages = messages.filter(
    (message) =>
      (message.fromCountryID === countryIDSelected ||
        message.toCountryID === countryIDSelected) &&
      (countryIDSelected === 0 || message.toCountryID !== 0), // public messages in public chat
  );
  const messageComponents = filteredMessages.map((message: GameMessage) => (
    <WDMessage
      key={`${message.timeSent}:${message.fromCountryID}:${message.toCountryID}:${message.message}`}
      message={message}
      userCountry={userCountry}
      allCountries={allCountries}
    />
  ));

  return (
    <WDVerticalScroll>
      <Stack direction="column">{messageComponents}</Stack>
      <Box ref={messagesEndRef} />
    </WDVerticalScroll>
  );
};

export default WDMessageList;
