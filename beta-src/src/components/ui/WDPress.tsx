import React, { FC, ReactNode, ReactElement, useRef, useEffect } from "react";
import { IconButton, TextField, Divider } from "@mui/material";
import { Email, Send } from "@mui/icons-material";
import useLocalStorageState from "use-local-storage-state";

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
  children: ReactNode;
  userCountry: CountryTableData | null;
  allCountries: CountryTableData[];
}

const WDPress: FC<WDPressProps> = function ({
  children,
  userCountry,
  allCountries,
}): ReactElement {
  const dispatch = useAppDispatch();

  const [messageStack, setMessageStack] = useLocalStorageState("messageStack", {
    defaultValue: {},
  });

  const messagesEndRef = useRef<HTMLDivElement>(null);
  const { user, gameID, pressType, phase } = useAppSelector(gameOverview);
  const messages = useAppSelector(({ game }) => game.messages.messages);
  const countryIDSelected = useAppSelector(
    ({ game }) => game.messages.countryIDSelected,
  );
  const newMessagesFrom = useAppSelector(
    ({ game }) => game.messages.newMessagesFrom,
  );

  useEffect(() => {
    // scroll to the bottom of the message list
    messagesEndRef.current?.scrollIntoView();
  }, [countryIDSelected]);
  useEffect(() => {
    const lastID = messages[messages.length - 1]?.fromCountryID;
    if (lastID === userCountry?.countryID || lastID === countryIDSelected) {
      messagesEndRef.current?.scrollIntoView();
    }
  }, [messages]);

  const clickSend = () => {
    if (!userCountry) {
      return;
    }
    if (messageStack[countryIDSelected]) {
      dispatch(
        sendMessage({
          gameID: String(gameID),
          countryID: String(userCountry.countryID),
          toCountryID: String(countryIDSelected),
          message: messageStack[countryIDSelected],
        }),
      );
    }
    const ms = { ...messageStack };
    ms[countryIDSelected] = "";
    setMessageStack(ms);
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

  const makeCountryButton = ({ country, countryID }) => {
    const color = countryID === 0 ? "black" : `${country.toLowerCase()}-main`;
    return (
      <button
        type="button"
        className={`rounded-full py-1 px-3 font-medium ${
          countryIDSelected === countryID
            ? `bg-${color} text-white`
            : `text-${color} bg-white`
        }`}
        key={countryID}
        color="primary"
        onClick={() => {
          dispatchMessagesSeen(countryID);
          dispatch(gameApiSliceActions.selectMessageCountryID(countryID));
        }}
      >
        {newMessagesFrom.includes(countryID) && <Email />}
        {country.slice(0, 3).toUpperCase()}
      </button>
    );
  };

  let countryButtons = allCountries
    .filter((country) => country.countryID !== userCountry?.countryID)
    .sort((a, b) => a.countryID - b.countryID)
    .map(makeCountryButton);
  const allButton = makeCountryButton({
    country: "ALL",
    countryID: 0,
  });
  countryButtons = userCountry ? [allButton, ...countryButtons] : [allButton];

  const canMsg =
    pressType === "Regular" ||
    (pressType === "PublicPressOnly" && countryIDSelected === 0) ||
    (pressType === "RulebookPress" &&
      ["Diplomacy", "Finished"].includes(phase));

  return (
    // eslint-disable-next-line jsx-a11y/click-events-have-key-events, jsx-a11y/no-static-element-interactions
    <div
      className="p-0"
      onClick={() => dispatchMessagesSeen(countryIDSelected)} // clicking anywhere in the window means you've seen it
    >
      <div className="ml-3 mt-3 p-0 items-center">
        <div
          className="dialogue-countries inline"
          style={{
            padding: "6px 0px",
            width: userCountry ? "auto" : "95%",
          }}
        >
          {countryButtons}
        </div>
      </div>
      <WDMessageList
        messages={messages}
        allCountries={allCountries}
        userCountry={userCountry}
        countryIDSelected={countryIDSelected}
        messagesEndRef={messagesEndRef}
      />
      {userCountry && (
        <div>
          <div className="flex-row items-center pr-3 pl-2">
            {/* <button
                href="#message-reload-button"
                onClick={dispatchFetchMessages}
                style={{
                  maxWidth: "12px",
                  minWidth: "12px",
                }}
              >
                <AutorenewIcon sx={{ fontSize: "medium" }} />
              </button> */}
            <TextField
              id="user-msg"
              label="Send Message"
              variant="outlined"
              value={messageStack[countryIDSelected] || ""}
              multiline
              maxRows={4}
              onChange={(text) => {
                const ms = { ...messageStack };
                ms[countryIDSelected] = text.target.value;
                setMessageStack(ms);
              }}
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
                      disabled={!messageStack[countryIDSelected] || !canMsg}
                    >
                      <Send
                        color={
                          messageStack[countryIDSelected] && canMsg
                            ? "primary"
                            : "disabled"
                        }
                      />
                    </IconButton>
                  </>
                ),
                style: {
                  padding: "4px 0 4px 8px", // needed to cancel out extra height induced by the button
                },
              }}
            />
          </div>
        </div>
      )}
    </div>
  );
};

export default WDPress;
