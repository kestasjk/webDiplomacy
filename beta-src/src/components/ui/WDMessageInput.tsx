import * as React from "react";
import { Box, Stack, TextField } from "@mui/material";
import Device from "../../enums/Device";
import useViewport from "../../hooks/useViewport";
import getDevice from "../../utils/getDevice";
import WDButton from "./WDButton";
import { CountryTableData } from "../../interfaces";

interface WDMessageInputProps {
  children: React.ReactNode;
  countries: CountryTableData[];
  userCountry: CountryTableData;
}

const WDMessageInput: React.FC<WDMessageInputProps> = function ({
  children,
  countries,
  userCountry,
}): React.ReactElement {
  const [viewport] = useViewport();
  const device = getDevice(viewport);
  const mobileLandscapeLayout =
    device === Device.MOBILE_LANDSCAPE ||
    device === Device.MOBILE_LG_LANDSCAPE ||
    device === Device.MOBILE;
  const padding = mobileLandscapeLayout ? "0 6px" : "0 16px";
  const width = mobileLandscapeLayout ? 272 : 358;
  const spacing = mobileLandscapeLayout ? 1 : 2;

  const [userMsg, setUserMsg] = React.useState("");
  const [chatHistory, setChatHistory] = React.useState("");
  const [countrySelected, setCountrySelected] = React.useState("AUSTRIA");

  const sendUserMsg = () => {
    setUserMsg("");
    setChatHistory((curHistory) => `${curHistory}\n${userMsg}`);
  };

  const otherCountries = countries.filter(
    (country) => country.power !== userCountry.power,
  );

  const countryButtons = Object.entries(otherCountries).map(
    ([country, countryData]) => {
      return (
        <WDButton
          key={countrySelected}
          sx={{ p: padding }}
          color={countrySelected === country ? "primary" : "secondary"}
        >
          {country}
        </WDButton>
      );
    },
  );

  return (
    <Box>
      <Stack direction="row" spacing={spacing} alignItems="center">
        {countryButtons}
      </Stack>
      <Box sx={{ p: padding }}>
        <TextField
          id="chat-history"
          multiline
          rows={8}
          defaultValue=""
          inputProps={{ readOnly: true }}
          value={chatHistory}
        />
      </Box>

      <Box
        sx={{
          m: "20px 0 10px 0",
          p: padding,
          width,
        }}
      >
        <TextField
          id="user-msg"
          label="Send Message"
          variant="outlined"
          value={userMsg}
          multiline
          maxRows={4}
          onChange={(text) => setUserMsg(text.target.value)}
        />
        <WDButton
          key={userMsg}
          sx={{ p: padding }}
          color="primary"
          disabled={!userMsg}
          onClick={sendUserMsg}
        >
          Send
        </WDButton>
      </Box>
    </Box>
  );
};

export default WDMessageInput;
