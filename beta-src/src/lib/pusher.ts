import Pusher from "pusher-js";
import { postGameApiRequest } from "../utils/api";
import ApiRoute from "../enums/ApiRoute";

const {
  REACT_APP_PUSHER_APP_KEY,
  REACT_APP_PUSHER_HOST,
  REACT_APP_PUSHER_WS_PORT,
  REACT_APP_PUSHER_WSS_PORT,
} = process.env;

console.log(
  "PUSHER_APP_KEY, PUSHER_HOST, PUSHER_PORT",
  REACT_APP_PUSHER_APP_KEY,
  REACT_APP_PUSHER_HOST,
  REACT_APP_PUSHER_WS_PORT,
  REACT_APP_PUSHER_WSS_PORT,
);

// Pusher.logToConsole = true;
const client = new Pusher(REACT_APP_PUSHER_APP_KEY || "app-key", {
  wsHost: REACT_APP_PUSHER_HOST || "127.0.0.1",
  wsPort: Number(REACT_APP_PUSHER_WS_PORT) || 6001,
  wssPort: Number(REACT_APP_PUSHER_WSS_PORT) || 6002,
  forceTLS: false,
  // encrypted: true,
  disableStats: true,
  enabledTransports: ["ws", "wss"],
  authorizer: (channel, options) => {
    const gameID = channel.name.split("-")[1].replace("game", "");

    return {
      authorize: async (socketId, callback) => {
        const response = await postGameApiRequest(
          ApiRoute.WEBSOCKETS_AUTHENTICATION,
          { socket_id: socketId, channel_name: channel.name, gameID },
        );
        callback(null, { auth: response.data.data.auth });
      },
    };
  },
});

client.connection.bind("connected", () => {
  // eslint-disable-next-line no-console
  console.info("connected");
});

export default client;
