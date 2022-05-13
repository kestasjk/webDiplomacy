import * as React from "react";
import { Box, Stack, TextField, ButtonGroup, Divider } from "@mui/material";
import { Email, Send } from "@mui/icons-material";

import Button from "@mui/material/Button";
import Device from "../../enums/Device";
import useViewport from "../../hooks/useViewport";
import getDevice from "../../utils/getDevice";
import WDButton from "./WDButton";
import WDMessageList from "./WDMessageList";
import { CountryTableData } from "../../interfaces";
import { useAppDispatch, useAppSelector } from "../../state/hooks";
import GameMessages, { GameMessage } from "../../state/interfaces/GameMessages";
import {
  fetchGameMessages,
  gameMessages,
  gameOverview,
} from "../../state/game/game-api-slice";
import webDiplomacyTheme from "../../webDiplomacyTheme";

interface WDPressProps {
  children: React.ReactNode;
  userCountry: CountryTableData;
  countries: CountryTableData[];
}

const WDPress: React.FC<WDPressProps> = function ({
  children,
  userCountry,
  countries,
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
  const [countryIDSelected, setCountryIDSelected] = React.useState(0);

  const { user, gameID } = useAppSelector(gameOverview);

  const messages = useAppSelector(gameMessages);

  const sendUserMsg = () => {
    setUserMsg("");
    setChatHistory((curHistory) => `${curHistory}\n${userMsg}`);
    console.log("members");
    console.log(countries);
    console.log("messages");
    console.log(messages);
  };

  const updateCountryPane = (country: number) => {
    setCountryIDSelected(country);
    // const dispatch = useAppDispatch();
    // dispatch(
    //   fetchGameMessages({
    //     gameID: gameID as unknown as string,
    //     countryID: user.member.countryID as unknown as string,
    //     toCountryID: country.countryID as unknown as string,
    //     limit: "25",
    //   }),
    // );
  };

  const countryButtons = countries
    .sort((a, b) => a.countryID - b.countryID)
    .map((country) => {
      return (
        <Button
          key={country.countryID}
          sx={{
            p: 1,
            "&.MuiButton-text": { color: country.color },
          }}
          color="primary"
          onClick={() => updateCountryPane(country.countryID)}
          size="small"
          variant={
            countryIDSelected === country.countryID ? "contained" : "text"
          }
        >
          {country.country.slice(0, 3).toUpperCase()}
        </Button>
      );
    });

  return (
    <Box sx={{ p: padding }}>
      <Stack alignItems="center" sx={{ p: padding }}>
        <ButtonGroup className="dialogue-countries">
          {countryButtons}
        </ButtonGroup>
      </Stack>
      <WDMessageList
        messages={messages.messages}
        countries={countries}
        userCountry={userCountry}
        countryIDSelected={countryIDSelected}
      />
      <Divider />

      <Box
        sx={{
          m: "20px 0 10px 0",
          p: padding,
          width,
        }}
      >
        <Stack alignItems="center" direction="row">
          <TextField
            id="user-msg"
            label="Send Message"
            variant="outlined"
            value={userMsg}
            multiline
            maxRows={4}
            onChange={(text) => setUserMsg(text.target.value)}
          />
          <Button
            key={userMsg}
            sx={{ p: padding }}
            color="primary"
            disabled={!userMsg}
            onClick={sendUserMsg}
            endIcon={<Send />}
            size="large"
          >
            {}
          </Button>
        </Stack>
      </Box>
    </Box>
  );
};

export default WDPress;
