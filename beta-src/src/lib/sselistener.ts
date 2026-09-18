import { postGameApiRequest } from "../utils/api";
import ApiRoute from "../enums/ApiRoute";
import { store } from "../state/store";

/*
Pusher is a lot for what webDip needs; instant notification from the server to the client when an 
event is triggered; the game is processed, a vote was cast, or a message was sent.
This is a simple SSE-based implementation that uses Redis to receive events from the PHP server,
then a simple node.js server sends these events to clients via SSE.
*/

function sseDebugLog(msg: any) {
  console.log(`[SSE] ${msg}`);
}

// This function should be kept in sync with javascript/api.js, which does the same function for the legacy board

// The single event source shared for all event listeners:
let eventSource: EventSource;
let gameID = 0; // This will be set when the first game is subscribed to, so we can use it for the authorizer
let countryID = 0; // This will be set when the first game is subscribed to, so we can use it for the authorizer
// Set next reconnect time to now + 30 seconds:
let nextReconnectTime = new Date(); // In case of a connection issue this variable will trigger a reconnection
nextReconnectTime.setSeconds(nextReconnectTime.getSeconds() + 30);
let isEventSourceReconnecting = false; // Set while a reconnection is in progress, so the watchdog doesn't stack reconnections
// The single reconnect watchdog timer; kept module-level so reconnects replace it rather than adding another
let reconnectWatchdogTimer: ReturnType<typeof setInterval> | undefined;
// This will hold all event callbacks, keyed by event name
type EventCallback = (...args: any[]) => void;
const eventCallbacks: { [key: string]: EventCallback[] } = {};

// Tokens for the SSE server are accepted for a day
const TOKEN_MAX_AGE_SECONDS = 23 * 60 * 60;

// The token is "hash_timestamp", the timestamp being the server's time when it was made
function tokenTime(auth: string): number {
  return parseInt(auth.split("_")[1], 10);
}

// The game overview gives members a token for the SSE server, so it only needs requesting from
// sse/authentication if that one has expired (a tab left open) or was refused
function getOverviewToken(): string | null {
  const auth = store.getState().game.overview.user?.sseAuth;
  if (!auth) return null;
  const age = Math.abs(Date.now() / 1000 - tokenTime(auth));
  return age < TOKEN_MAX_AGE_SECONDS ? auth : null;
}

// What this page has, which the SSE server compares against the game when the connection opens, sending
// the events this page would have received if it missed the game being processed or a message
function getCatchupParams(auth: string): string {
  const { overview, messages } = store.getState().game;
  // Until the messages have been fetched, any message since the overview was (when the token was made)
  const since = messages.time || tokenTime(auth);
  return `&turn=${overview.turn}&phase=${encodeURIComponent(
    overview.phase,
  )}&since=${since}`;
}

// Events published while the connection was down are lost (the server keeps no history). The SSE server
// sends the processed and message events which were missed when the connection opens, then a catchup
// event; this refetches everything an event would have, for when it can't tell what was missed (a resync
// request) or no catchup event came as it is an older SSE server.
function refetchAfterGap(reason: string) {
  sseDebugLog(`Refetching game state after ${reason}`);
  if (eventCallbacks.overview) {
    eventCallbacks.overview.forEach((callback) => callback(reason));
  }
  if (eventCallbacks.message) {
    eventCallbacks.message.forEach((callback) => callback());
  }
}

const client = {
  authorizer: (channel, options) => {
    const newGameID = parseInt(channel.split("-")[1].replace("game", ""), 10);
    const newCountryID = parseInt(
      channel.includes("country")
        ? channel.split("-").pop().replace("country", "")
        : "0",
      10,
    );
    // We will auth once with the country ID, as the game ID doesn't matter, and the client will
    // always subscribe to both messag and game updates, so might as well use a single connection
    // and auth token.
    if (newCountryID < 1) {
      // If we already have a connection for this game and country, just return the existing event source
      sseDebugLog(`Ignoring subscription to game without country ID`);
      return;
    }
    if (newGameID === gameID || newCountryID === countryID) {
      // If we already have a connection for this game and country, just return the existing event source
      sseDebugLog(
        `Reusing existing SSE connection for game ${gameID} with country ${countryID}`,
      );
      return;
    }
    gameID = newGameID;
    countryID = newCountryID;
    let hasConnectedBefore = false;
    // Set if a connection fails before opening, as the token may have been refused
    let overviewTokenRefused = false;
    let catchupTimer: ReturnType<typeof setTimeout> | undefined;
    const getToken = (): Promise<string> => {
      const overviewToken = overviewTokenRefused ? null : getOverviewToken();
      if (overviewToken) return Promise.resolve(overviewToken);
      sseDebugLog(
        `Authorizing SSE connection for game ${gameID} with country ${countryID}`,
      );
      return postGameApiRequest(ApiRoute.SSE_AUTHENTICATION, {
        channel_name: `private-game${gameID}${
          countryID > 0 ? `-country${countryID}` : ""
        }`,
        gameID: gameID.toString(),
      }).then((response) => {
        if (response.status !== 200) {
          throw new Error("Failed to authenticate SSE connection");
        }
        if (!response.data || !response.data.data.auth) {
          throw new Error("No authentication token received from SSE server");
        }
        return response.data.data.auth;
      });
    };
    const reconnect = () => {
      getToken()
        .then((auth) => {
          const isReconnection = hasConnectedBefore;
          let hasOpened = false;
          eventSource = new EventSource(
            `/events?channelList=private-game${gameID},private-game${gameID}-country${countryID}&auth=${auth}${getCatchupParams(
              auth,
            )}`,
          );
          eventSource.onopen = () => {
            sseDebugLog("Connected to SSE server");
            hasOpened = true;
            overviewTokenRefused = false;
            // The connection is up again, so let the watchdog fire for the next timeout,
            // and give the server the full timeout period before that can happen:
            isEventSourceReconnecting = false;
            const newReconnectTime = new Date();
            newReconnectTime.setSeconds(newReconnectTime.getSeconds() + 30);
            nextReconnectTime = newReconnectTime;
            // Callback all connected subscribers:
            if (eventCallbacks.connected) {
              eventCallbacks.connected.forEach((callback) => callback());
            }
            if (eventCallbacks["pusher:subscription_succeeded"]) {
              eventCallbacks["pusher:subscription_succeeded"].forEach(
                (callback) => callback(),
              );
            }
            // The SSE server should now send the events this page missed, then a catchup event. The
            // first connection comes just after the page loaded the game, so without one only
            // reconnections refetch.
            if (catchupTimer) clearTimeout(catchupTimer);
            catchupTimer = setTimeout(() => {
              if (isReconnection) refetchAfterGap("reconnecting");
            }, 5000);
            hasConnectedBefore = true;
          };
          eventSource.onerror = (e) => {
            sseDebugLog(
              "Connection error or closed. Will attempt reconnection in 5 seconds.",
            );
            eventSource.close();
            if (!hasOpened) overviewTokenRefused = true;
            nextReconnectTime = new Date(); // Trigger a reconnection
            if (eventCallbacks["pusher:subscription_error"]) {
              eventCallbacks["pusher:subscription_error"].forEach((callback) =>
                callback(e),
              );
            }
          };
          // Periodically check if we need to reconnect. Each reconnection replaces the
          // previous watchdog so long-lived tabs don't accumulate timers:
          if (reconnectWatchdogTimer) clearInterval(reconnectWatchdogTimer);
          reconnectWatchdogTimer = setInterval(() => {
            const now = new Date();
            if (!isEventSourceReconnecting && now >= nextReconnectTime) {
              sseDebugLog(
                "Nothing received from server in reconnect timeout period. Reconnecting",
              );
              eventSource.close();
              isEventSourceReconnecting = true; // Ensure this timer won't keep reconnecting

              reconnect(); // Restart SSE connection
              if (eventCallbacks["pusher:subscription_error"]) {
                eventCallbacks["pusher:subscription_error"].forEach(
                  (callback) => callback("SSE timeout, reconnecting"),
                );
              }
            }
          }, 17000);
          eventSource.onmessage = (e) => {
            try {
              const data = JSON.parse(e.data);
              // If message starts with "overview", it's an overview message:
              // Message = set-vote|processed|message
              sseDebugLog(`Message received via SSE: ${e.data}`);
              // Update the next reconnect time to 30 seconds from now:
              const newReconnectTime = new Date();
              newReconnectTime.setSeconds(newReconnectTime.getSeconds() + 30);
              nextReconnectTime = newReconnectTime;

              if (data.channel === "resync") {
                // The SSE server can't tell what was missed: a key it checks wasn't set, or it lost
                // its Redis connection
                refetchAfterGap("a resync request");
              } else if (data.channel === "catchup") {
                // Missed processed and message events have been sent. Votes aren't covered, so
                // refetch the overview if any could have been missed.
                if (catchupTimer) clearTimeout(catchupTimer);
                if (isReconnection && eventCallbacks.overview) {
                  eventCallbacks.overview.forEach((callback) =>
                    callback("reconnecting"),
                  );
                }
              } else if (data.message && data.message.includes("message")) {
                sseDebugLog(`New game message received`);
                if (eventCallbacks.message) {
                  eventCallbacks.message.forEach((callback) => callback());
                }
              } else if (data.message && data.message.includes("set-vote")) {
                sseDebugLog(`New set-vote message received`);
                if (eventCallbacks.overview) {
                  eventCallbacks.overview.forEach((callback) =>
                    callback("set-vote"),
                  );
                }
              } else if (data.message && data.message.includes("processed")) {
                sseDebugLog(`New processed message received`);
                if (eventCallbacks.overview) {
                  eventCallbacks.overview.forEach((callback) =>
                    callback("processed"),
                  );
                }
              } else if (data.message && data.message.includes("ping")) {
                if (eventCallbacks["pusher:pong"]) {
                  eventCallbacks["pusher:pong"].forEach((callback) =>
                    callback(),
                  );
                }
              }
            } catch {
              sseDebugLog(`Raw message: ${e.data}`);
            }
          };
        })
        .catch((error) => {
          console.error("Failed to authenticate SSE connection:", error);
          // Allow the watchdog to attempt another reconnection:
          isEventSourceReconnecting = false;
          if (eventCallbacks["pusher:subscription_error"]) {
            eventCallbacks["pusher:subscription_error"].forEach((callback) =>
              callback(error),
            );
          }
        });
    };

    // Start the connection to the SSE server
    reconnect();
  },
  // For connection-wide events, like connected
  connection: {
    bind: (event, callback) => {
      // Event = connected, pusher:subscription_succeeded, pusher:subscription_error
      sseDebugLog(`Binding to connection event: ${event}`);
      eventCallbacks[event] = eventCallbacks[event] || [];
      eventCallbacks[event].push(callback);
    },
  },
  // For subscribing to game-specific channels
  subscribe: (channelName) => {
    // When the subscribe request comes we know the game and country to subscribe to, so now we can start connecting
    // This will run asynchronously, so we can return a channel object immediately
    sseDebugLog(`Subscribing to channel: ${channelName}`);
    client.authorizer(channelName, {});
    // overview:data=message|set-vote, pusher:pong, pusher:subscription_succeeded, pusher:subscription_error
    return {
      bind: (event, callback) => {
        // Event = processed   pusher:subscription_succeeded pusher:subscription_error
        eventCallbacks[event] = eventCallbacks[event] || [];
        eventCallbacks[event].push(callback);
      },
    };
  },
};

export default client;
