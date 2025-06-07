import { postGameApiRequest } from "../utils/api";
import ApiRoute from "../enums/ApiRoute";

/*
Pusher is a lot for what webDip needs; instant notification from the server to the client when an 
event is triggered; the game is processed, a vote was cast, or a message was sent.
This is a simple SSE-based implementation that uses Redis to receive events from the PHP server,
then a simple node.js server sends these events to clients via SSE.
*/
const { REACT_APP_SSE_HOST, REACT_APP_SSE_PORT } = process.env;

function sseDebugLog(msg: any) {
  console.log(`[SSE] ${msg}`);
}

sseDebugLog(
  `"REACT_APP_SSE_HOST, REACT_APP_SSE_PORT", ${REACT_APP_SSE_HOST}, ${REACT_APP_SSE_PORT}`,
);

// This function should be kept in sync with javascript/api.js, which does the same function for the legacy board

// The single event source shared for all event listeners:
let eventSource: EventSource;
let gameID = 0; // This will be set when the first game is subscribed to, so we can use it for the authorizer
let countryID = 0; // This will be set when the first game is subscribed to, so we can use it for the authorizer
// Set next reconnect time to now + 30 seconds:
let nextReconnectTime = new Date(); // In case of a connection issue this variable will trigger a reconnection
nextReconnectTime.setSeconds(nextReconnectTime.getSeconds() + 30);
let isEventSourceReconnecting = false; // Set when a new SSE connection has been established, so reconnections should halt
// This will hold all event callbacks, keyed by event name
type EventCallback = (...args: any[]) => void;
const eventCallbacks: { [key: string]: EventCallback[] } = {};

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
    const reconnect = () => {
      sseDebugLog(
        `Authorizing SSE connection for game ${gameID} with country ${countryID}`,
      );
      postGameApiRequest(ApiRoute.SSE_AUTHENTICATION, {
        channel_name: `private-game${gameID}${
          countryID > 0 ? `-country${countryID}` : ""
        }`,
        gameID: gameID.toString(),
      })
        .then((response) => {
          if (response.status !== 200) {
            throw new Error("Failed to authenticate SSE connection");
          }
          if (!response.data || !response.data.data.auth) {
            throw new Error("No authentication token received from SSE server");
          }
          eventSource = new EventSource(
            `http://${REACT_APP_SSE_HOST}:${REACT_APP_SSE_PORT}/events?channelList=private-game${gameID},private-game${gameID}-country${countryID}&auth=${response.data.data.auth}`,
          );
          eventSource.onopen = () => {
            sseDebugLog("Connected to SSE server");
            // Callback all connected subscribers:
            if (eventCallbacks.connected) {
              eventCallbacks.connected.forEach((callback) => callback());
            }
            if (eventCallbacks["pusher:subscription_succeeded"]) {
              eventCallbacks["pusher:subscription_succeeded"].forEach(
                (callback) => callback(),
              );
            }
          };
          eventSource.onerror = (e) => {
            sseDebugLog(
              "Connection error or closed. Will attempt reconnection in 5 seconds.",
            );
            eventSource.close();
            nextReconnectTime = new Date(); // Trigger a reconnection
            if (eventCallbacks["pusher:subscription_error"]) {
              eventCallbacks["pusher:subscription_error"].forEach((callback) =>
                callback(e),
              );
            }
          };
          // Every 5 seconds check if we need to reconnect:
          setInterval(() => {
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
              // Message = vote-sent|processed|message
              sseDebugLog(`Message received via SSE: ${e.data}`);
              // Update the next reconnect time to 30 seconds from now:
              const newReconnectTime = new Date();
              newReconnectTime.setSeconds(newReconnectTime.getSeconds() + 30);
              nextReconnectTime = newReconnectTime;

              if (data.message && data.message.includes("message")) {
                sseDebugLog(`New game message received`);
                if (eventCallbacks.message) {
                  eventCallbacks.message.forEach((callback) => callback());
                }
              } else if (data.message && data.message.includes("vote-sent")) {
                sseDebugLog(`New vote-sent message received`);
                if (eventCallbacks.overview) {
                  eventCallbacks.overview.forEach((callback) =>
                    callback("vote-sent"),
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
                if (eventCallbacks["pusher:pong]"]) {
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
