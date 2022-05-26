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
import useInterval from "../../utils/useInterval";
import WDMessageList from "./WDMessageList";
import { CountryTableData } from "../../interfaces";
import { useAppDispatch, useAppSelector } from "../../state/hooks";
import {
  fetchGameMessages,
  gameApiSliceActions,
  gameOverview,
  markMessagesSeen,
  sendMessage,
} from "../../state/game/game-api-slice";
import { store } from "../../state/store";

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

  const [userMsg, setUserMsg] = React.useState("");
  const [countryIDSelected, setCountryIDSelected] = React.useState(
    // start with the first country
    Math.min(...countries.map((country) => country.countryID)),
  );

  const { user, gameID } = useAppSelector(gameOverview);
  const messages = useAppSelector(({ game }) => game.messages.messages);
  const newMessagesFrom = useAppSelector(
   ({ game }) => game.messages.newMessagesFrom,
  );

  // FIXME: for now, crazily fetch all messages every 1sec
  useInterval(() => {
    if (user && gameID) {
      const { game } = store.getState();
      const { outstandingMessageRequests } = game;
      if (outstandingMessageRequests === 0) {
        console.log("Dispatching");
        dispatch(gameApiSliceActions.updateOutstandingMessageRequests(1));
        dispatch(
          fetchGameMessages({
            gameID: gameID as unknown as string,
            countryID: user.member.countryID as unknown as string,
            allMessages: "true",
            sinceTime: game.messages.time as unknown as string,
          }),
        );
      }
    }
  }, 1000);

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

  // capture enter for end, shift-enter for newline
  const keydownHandler = (e) => {
    const keyCode = e.which || e.keyCode;
    const ENTER = 13;
    if (keyCode === ENTER && !e.shiftKey) {
      e.preventDefault();
      clickSend();
    }
  };

  if (newMessagesFrom.includes(countryIDSelected)) {
    // need to update locally and on the server
    // because we don't immediately re-fetch message data from the server
    dispatch(gameApiSliceActions.processMessagesSeen(countryIDSelected));
    dispatch(
      markMessagesSeen({
        countryID: String(userCountry.countryID),
        gameID: String(gameID),
        seenCountryID: String(countryIDSelected),
      }),
    );
  }

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
          startIcon={
            newMessagesFrom.includes(country.countryID) ? <Email /> : ""
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
        messages={messages}
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
            onKeyDown={keydownHandler}
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
