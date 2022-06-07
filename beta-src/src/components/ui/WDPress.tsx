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
import useViewport from "../../hooks/useViewport";
import getDevice from "../../utils/getDevice";
import WDMessageList from "./WDMessageList";
import { CountryTableData } from "../../interfaces";
import { useAppDispatch, useAppSelector } from "../../state/hooks";
import {
  gameApiSliceActions,
  gameOverview,
  markMessagesSeen,
  sendMessage,
} from "../../state/game/game-api-slice";
import { store } from "../../state/store";

interface WDPressProps {
  children: React.ReactNode;
  userCountry: CountryTableData | null;
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

  const padding = 0;

  const [userMsg, setUserMsg] = React.useState("");

  const { user, gameID, pressType, phase } = useAppSelector(gameOverview);

  const messages = useAppSelector(({ game }) => game.messages.messages);
  const countryIDSelected = useAppSelector(
    ({ game }) => game.messages.countryIDSelected,
  );
  const newMessagesFrom = useAppSelector(
    ({ game }) => game.messages.newMessagesFrom,
  );

  const messagesEndRef = React.useRef<HTMLDivElement>(null);
  React.useEffect(() => {
    // scroll to the bottom of the message list
    // FIXME: should this happen if we get a message from a 3rd party?
    messagesEndRef.current?.scrollIntoView();
  }, [messages, countryIDSelected]);

  const clickSend = () => {
    if (!userCountry) {
      return;
    }
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

  const dispatchMessagesSeen = (countryID) => {
    // need to update locally and on the server
    // because we don't immediately re-fetch message data from the server
    dispatch(gameApiSliceActions.processMessagesSeen(countryID));
    if (userCountry) {
      dispatch(
        markMessagesSeen({
          countryID: String(userCountry.countryID),
          gameID: String(gameID),
          seenCountryID: String(countryID),
        }),
      );
    }
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

  const makeCountryButton = ({ country, countryID, color }) => {
    return (
      <Button
        key={countryID}
        sx={{
          p: 1,
          "&.MuiButton-text": { color },
        }}
        color="primary"
        onClick={() => {
          dispatchMessagesSeen(countryID);
          dispatch(gameApiSliceActions.selectMessageCountryID(countryID));
        }}
        size="small"
        variant={countryIDSelected === countryID ? "contained" : "text"}
        startIcon={newMessagesFrom.includes(countryID) ? <Email /> : ""}
      >
        {country.slice(0, 3).toUpperCase()}
      </Button>
    );
  };

  let countryButtons = countries
    .sort((a, b) => a.countryID - b.countryID)
    .map(makeCountryButton);
  const allButton = makeCountryButton({
    country: "ALL",
    countryID: 0,
    color: "primary",
  });
  countryButtons = userCountry ? [allButton, ...countryButtons] : [allButton];

  const countriesForMessageList = [...countries];
  if (userCountry) {
    countriesForMessageList.push(userCountry);
  }
  const canMsg =
    pressType === "Regular" ||
    (pressType === "PublicPressOnly" && countryIDSelected === 0) ||
    (pressType === "RulebookPress" &&
      ["Diplomacy", "Finished"].includes(phase));

  return (
    <Box
      sx={{ p: padding }}
      onClick={() => dispatchMessagesSeen(countryIDSelected)} // clicking anywhere in the window means you've seen it
    >
      <Stack alignItems="center" sx={{ p: padding }}>
        <ButtonGroup
          className="dialogue-countries"
          sx={{
            display: "inline",
            padding: "6px 0px",
            width: userCountry ? "auto" : "95%",
          }}
        >
          {countryButtons}
        </ButtonGroup>
      </Stack>
      <WDMessageList
        messages={messages}
        countries={countriesForMessageList}
        userCountry={userCountry}
        countryIDSelected={countryIDSelected}
        messagesEndRef={messagesEndRef}
      />
      {userCountry && (
        <Box>
          <Stack alignItems="center" direction="row">
            {/* <Button
            href="#message-reload-button"
            onClick={dispatchFetchMessages}
            style={{
              maxWidth: "12px",
              minWidth: "12px",
            }}
          >
            <AutorenewIcon sx={{ fontSize: "medium" }} />
          </Button> */}
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
              disabled={!canMsg}
              sx={{ m: "0 0 0 6px" }}
              InputProps={{
                endAdornment: (
                  <>
                    <Divider orientation="vertical" />
                    <IconButton
                      onClick={clickSend}
                      disabled={!userMsg || !canMsg}
                    >
                      <Send
                        color={userMsg && canMsg ? "primary" : "disabled"}
                      />
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
      )}
    </Box>
  );
};

export default WDPress;
