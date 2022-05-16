import * as React from "react";
import {
  Box,
  Stack,
  IconButton,
  TextField,
  ButtonGroup,
  Divider,
} from "@mui/material";
import { Email, Send } from "@mui/icons-material";

import Button from "@mui/material/Button";
import Device from "../../enums/Device";
import useViewport from "../../hooks/useViewport";
import getDevice from "../../utils/getDevice";
import WDMessageList from "./WDMessageList";
import { CountryTableData } from "../../interfaces";
import { useAppDispatch, useAppSelector } from "../../state/hooks";
import {
  fetchGameMessages,
  gameMessages,
  gameOverview,
  sendMessage,
} from "../../state/game/game-api-slice";

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
  const dispatch = useAppDispatch();
  const mobileLandscapeLayout =
    device === Device.MOBILE_LANDSCAPE ||
    device === Device.MOBILE_LG_LANDSCAPE ||
    device === Device.MOBILE;
  const padding = mobileLandscapeLayout ? "0 6px" : "0 16px";
  const width = mobileLandscapeLayout ? 272 : 358;
  const spacing = mobileLandscapeLayout ? 1 : 2;

  const [userMsg, setUserMsg] = React.useState("");
  const [countryIDSelected, setCountryIDSelected] = React.useState(
    // start with the first country
    Math.min(...countries.map((country) => country.countryID)),
  );

  const { user, gameID } = useAppSelector(gameOverview);
  const messages = useAppSelector(gameMessages);

  // -------- message polling loop ----------
  const [messagePollCounter, setMessagePollCounter] = React.useState(0);

  // 1. on construction, set an interval that increments messagePollCounter
  React.useEffect(() => {
    setInterval(() => {
      setMessagePollCounter((c) => c + 1);
    }, 1000);
  }, []);

  // 2. each time mesagePollCounter is incremented, we get the state
  // from the store and dispatch a message fetch.
  //
  // FIXME: for now, crazily fetch all messages every 1sec
  React.useEffect(() => {
    if (user && gameID) {
      // console.log(`Dispatch messages. time= ${messages.time}`);
      dispatch(
        fetchGameMessages({
          gameID: gameID as unknown as string,
          countryID: user.member.countryID as unknown as string,
          allMessages: "true",
          sinceTime: messages.time as unknown as string,
        }),
      );
    }
  }, [messagePollCounter]);

  // ----------------------------------------

  const messagesEndRef = React.useRef<HTMLDivElement>(null);
  React.useEffect(() => {
    // scroll to the bottom of the message list
    messagesEndRef.current?.scrollIntoView();
  }, [messages, countryIDSelected]);

  const clickSend = () => {
    dispatch(
      sendMessage({
        gameID: String(gameID),
        countryID: String(userCountry.countryID),
        toCountryID: String(countryIDSelected),
        message: userMsg,
      }),
    );
    setUserMsg("");
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
          onClick={() => setCountryIDSelected(country.countryID)}
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
        countries={[...countries, userCountry]} // sorry, its just silly to exclude userCountry from this table
        userCountry={userCountry}
        countryIDSelected={countryIDSelected}
        messagesEndRef={messagesEndRef}
      />
      <Box>
        <Stack alignItems="center" direction="row">
          <TextField
            id="user-msg"
            label="Send Message"
            variant="outlined"
            value={userMsg}
            multiline
            maxRows={4}
            onChange={(text) => setUserMsg(text.target.value)}
            fullWidth
            InputProps={{
              endAdornment: (
                <>
                  <Divider orientation="vertical" />
                  <IconButton onClick={clickSend} disabled={!userMsg}>
                    <Send color="primary" />
                  </IconButton>
                </>
              ),
              style: {
                padding: "4px 0 4px 8px", // needed to cancel out extra height induced by the button
              },
            }}
          />
        </Stack>
      </Box>
    </Box>
  );
};

export default WDPress;
