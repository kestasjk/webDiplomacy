import * as React from "react";
import { Box, makeStyles, Stack } from "@mui/material";
import Device from "../../enums/Device";
import useViewport from "../../hooks/useViewport";
import getDevice from "../../utils/getDevice";
import WDButton from "./WDButton";
import WDMessage from "./WDMessage";
import { GameMessage } from "../../state/interfaces/GameMessages";
import { CountryTableData } from "../../interfaces/CountryTableData";

interface WDMessageListProps {
  messages: GameMessage[];
  userCountry: CountryTableData;
  countries: CountryTableData[];
  countryIDSelected: number;
}

const WDMessageList: React.FC<WDMessageListProps> = function ({
  messages,
  userCountry,
  countries,
  countryIDSelected,
}): React.ReactElement {
  console.log(`messageList updated ${countryIDSelected}`);
  const [viewport] = useViewport();
  const device = getDevice(viewport);
  const mobileLandscapeLayout =
    device === Device.MOBILE_LANDSCAPE ||
    device === Device.MOBILE_LG_LANDSCAPE ||
    device === Device.MOBILE;
  const padding = mobileLandscapeLayout ? "0 6px" : "0 16px";
  const width = mobileLandscapeLayout ? 272 : 358;
  const spacing = mobileLandscapeLayout ? 1 : 2;

  const messageComponents = messages
    .filter(
      (message) =>
        message.fromCountryID === countryIDSelected ||
        message.toCountryID === countryIDSelected,
    )
    .map((message: GameMessage) => <WDMessage message={message} />);
  console.log(
    `messageList updated ${countryIDSelected} ${typeof countryIDSelected}`,
  );

  console.log(
    `messages ${messages.length} components ${messageComponents.length}`,
  );
  console.log(messages);

  return (
    <Box
      sx={{
        m: "20px 0 10px 0",
        p: padding,
        width,
        height: "100%",
      }}
    >
      {messageComponents}
    </Box>
  );
};

export default WDMessageList;
