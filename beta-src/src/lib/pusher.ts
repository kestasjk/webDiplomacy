import Pusher from "pusher-js";
import { postGameApiRequest } from "../utils/api";
import ApiRoute from "../enums/ApiRoute";

const { PUSHER_APP_KEY, PUSHER_HOST, PUSHER_PORT } = process.env;

// Pusher.logToConsole = true;
const client = new Pusher(PUSHER_APP_KEY || "app-key", {
  wsHost: PUSHER_HOST || "127.0.0.1",
  wsPort: Number(PUSHER_PORT) || 6001,
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
