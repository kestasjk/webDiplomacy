// Simple node.js server that will serve an SSE feed of channel event messages 
// to clients, published to the Redis server from PHP as games are processed, 
// votes cast, and messages sent.

// Pusher / Durable Objects / Workers / etc was inefficient and expensive for 
// a simple client notification requirement.
// Web-sockets allows clients to talk back to the server, and allows for other
// use cases, however for basic server->client notifications it is overly complex
// and the solutions are expensive and complex. SSE is simply an HTTP connection
// where the client opens a "page" and the server then waits until messages arrive.

// PHP can't handle this as it would block, using an FPM worker for each connection,
// whereas node.js works asynchronously so can handle many connections without using
// much resources.

// sseSecret and ssePort must be in sync with the secret and port set in Config.php

// This server will serve from /events , and apache/nginx need to be configured to proxy requests to this server.

const crypto = require('crypto');
const cors = require('cors');
require('dotenv').config(); // Load environment variables from .env file
// The .env file should be protected by the web server or file permissions, however 
// getting access to the secret only allows clients to see when a user in a game has 
// received a message from another unknown user in the game, so the potential for 
// abuse is low.
// Load environment variables:
const redisHost = process.env.REDIS_HOST || 'localhost';
const redisPort = process.env.REDIS_PORT || 6379;
const ssePort = process.env.SSE_PORT || 3000;
const sseSecret = process.env.SSE_SECRET;


// Use the secret to validate an auth token we have been passed
function validateAuth(auth, channelName)
{
	// Code validates token generated here:
	// api.php - SSEAuthentication 
	// $timestamp = time();
	// $token = md5($channelName.Config::$sseSecret.$timestamp.'generateToken').'_'.$timestamp;

	// Split auth into md5 hash and timestamp:

	const parts = auth.split('_');
	if (parts.length !== 2) return false;

	const [receivedHash, timestampStr] = parts;
	const timestamp = parseInt(timestampStr, 10);
	if (isNaN(timestamp)) return false;

	// Check if timestamp is under 1 day old:
	const now = Math.floor(Date.now() / 1000);
	if (Math.abs(now - timestamp) > 86400) return false;

	// Generate valid md5 hash:
	const secret = sseSecret; // Ensure Config is in scope
	const input = channelName + secret + timestamp + 'generateToken';
	const expectedHash = crypto.createHash('md5').update(input).digest('hex');

	// Check if hash matches:
	if( expectedHash != receivedHash )
	{
		return false;
	}

	// This is a valid token, we can subscribe to this channel
	return true;
}

const express = require('express');
const Redis = require('redis');

const app = express();
app.use(cors()); // allow all origins by default

// Redis client for writing health check timestamps (needed as subscriber can't write):
const redisClient = Redis.createClient({
  socket: { host: redisHost, port: redisPort }
});
redisClient.on('error', (err) => {
  console.error('Redis client error:', err);
});
redisClient.connect();

// A single Redis subscriber shared by every SSE client, rather than one Redis connection per client.
// node-redis keeps a set of listeners per channel on this one connection: it only sends SUBSCRIBE when
// a channel gets its first listener and UNSUBSCRIBE when its last listener is removed, and after a
// reconnect it resubscribes every channel before emitting 'ready'.
const subscriber = Redis.createClient({
  socket: { host: redisHost, port: redisPort }
});
subscriber.on('error', (err) => {
  console.error('Redis subscriber error:', err);
});

// A board's page may be out of date by the time it has subscribed: anything published between the page
// being generated and the subscription starting is lost, as is anything published while a client was
// reconnecting. So a client can say what it has when it connects (turn, phase and since, the time of the
// newest message it could have), and this checks that against what PHP keeps in Redis and sends the
// client the events it would have received:
// - gameTurnPhase_{gameID}: "turn|phase", set whenever a game's turn or phase changes (Game::cacheTurnPhase)
// - lastmsgtime_{gameID}_{countryID}: when the country's newest message was sent (libGameMessage::send)
// If either is missing it can't tell, so it sends a resync and the client checks for itself; for the old
// board that is a game/pulse request, which sets both keys again. A catchup event always ends the check,
// which tells the client that this server does it; a client which doesn't get one checks for itself.
// The events are in the format PHP publishes them in (RedisInterface::trigger).
async function sendMissedEvents(write, overviewChannel, countryChannel, query) {
  const hasTurnPhase = query.turn !== undefined && query.phase !== undefined;
  const since = parseInt(query.since, 10);
  let resync = false;

  try {
    const match = countryChannel.match(/^private-game(\d+)-country(\d+)$/);
    if (!match) throw new Error(`Unexpected country channel ${countryChannel}`);
    const [, gameID, countryID] = match;

    const [turnPhase, lastMessageTime] = await Promise.all([
      hasTurnPhase ? redisClient.get(`gameTurnPhase_${gameID}`) : null,
      !isNaN(since) ? redisClient.get(`lastmsgtime_${gameID}_${countryID}`) : null,
    ]);

    if (hasTurnPhase) {
      if (turnPhase === null) resync = true;
      else if (turnPhase !== `${parseInt(query.turn, 10)}|${query.phase}`) {
        write(overviewChannel, JSON.stringify({ event: 'overview', data: 'processed' }));
      }
    }
    if (!isNaN(since)) {
      if (lastMessageTime === null) resync = true;
      else if (parseInt(lastMessageTime, 10) > since) {
        write(countryChannel, JSON.stringify({ event: 'message', data: 'messageSent' }));
      }
    }
  } catch (err) {
    console.error('Checking for missed events failed:', err);
    resync = true;
  }

  if (resync) write('resync', 'resync');
  write('catchup', 'catchup');
}

// Open SSE responses and the channels each is subscribed to, so they can all be told to resync after a
// Redis reconnect, and for the stats logged every minute
const openClients = new Map();

// Messages written to clients since the last stats line
let messagesForwarded = 0;

// Redis pub/sub keeps no history, so anything published while the subscriber was disconnected is lost.
// By the time 'ready' fires after a reconnect every channel is subscribed again, so tell the open
// clients to refetch their state; nothing published from this point on will be missed.
let subscriberConnectedBefore = false;
subscriber.on('ready', () => {
  if (subscriberConnectedBefore) {
    console.log(`Redis subscriber reconnected; sending resync to ${openClients.size} clients`);
    const data = JSON.stringify({ channel: 'resync', message: 'resync' });
    for (const client of openClients.keys()) {
      try {
        client.write(`event: message\ndata: ${data}\n\n`);
      } catch (e) {
        console.error('SSE resync write failed:', e);
      }
    }
  }
  subscriberConnectedBefore = true;
});
subscriber.connect();

app.get('/events', async (req, res) => {

  const auth = req.query.auth;
  if (!auth) {
    res.status(403).send('Missing auth parameter');
    return;
  }

  const channelsParam = req.query.channelList;
  if (!channelsParam) {
    res.status(400).send('Missing channelList parameter');
    return;
  }

  const channels = channelsParam.split(',').map(c => c.trim()).filter(c => c.length > 0);

  if (channels.length === 0) {
    res.status(400).send('No valid channels specified');
    return;
  }

  // Authenticate the country channel, as this authenticates the country and game:
  const countryChannel = channels.filter(c=>c.includes('country'));
  if( countryChannel.length !== 1 ) {
    res.status(400).send('One country channel needs to be specified');
    return;
  }

  if( !validateAuth(auth, countryChannel[0]) )
  {
    res.status(403).send('Invalid auth token for this channel; may be expired.');
    return;
  }

  // Authenticated and ready

  // Record client connection timestamp (not awaited; it is only for status.php and shouldn't delay the client)
  console.log(`Client connected (${openClients.size + 1} open). Setting SSE_LASTCLIENTCONNECT in Redis`);
  redisClient.set('SSE_LASTCLIENTCONNECT', Date.now().toString()).catch((err) => {
    console.error('SSE_LASTCLIENTCONNECT write failed:', err);
  });

  // Set headers for SSE
  res.writeHead(200, {
    'Content-Type': 'text/event-stream',
    'Cache-Control': 'no-cache',
    'Connection': 'keep-alive',
    'Access-Control-Allow-Origin': '*',
  });

  // Send initial comment to keep connection alive in some browsers
  res.write(`: connected to channels: ${channels.join(',')}\n\n`);

  // Forwards messages on this client's channels. This runs inside the shared subscriber's reply parser,
  // so it must never throw, or the parser would be reset for every client.
  const listener = (message, channel) => {
    try {
      const data = JSON.stringify({ channel, message });
      res.write(`event: message\n`);
      res.write(`data: ${data}\n\n`);
      messagesForwarded++;
    } catch (e) {
      console.error('SSE write failed:', e);
    }
  };

  // Send keep-alive comment and ping every 13 seconds
  const keepAliveInterval = setInterval(() => {
    res.write(': keep-alive\n\n');
    // Also send a ping:
    res.write(`event: message\n`);
    const data = JSON.stringify({ channel: 'ping', message: 'ping' });
    res.write(`data: ${data}\n\n`);
  }, 13000);

  // Cleanup on client disconnect. This is attached before anything is awaited, so a client that
  // disconnects while subscribing can't be missed. The listener is only removed here once subscribing
  // has finished; if it is still in progress the code after the await below removes it instead.
  // (Unsubscribing a listener that isn't registered yet could unsubscribe the channel in Redis while
  // another client's subscribe to it is still in flight.)
  let closed = false;
  let subscribed = false;
  openClients.set(res, channels);
  res.on('close', () => {
    closed = true;
    openClients.delete(res);
    clearInterval(keepAliveInterval);
    if (subscribed) {
      subscriber.unsubscribe(channels, listener).catch(() => {}); // ignore errors on cleanup
    }
  });

  // Subscribe to requested channels
  try {
    await subscriber.subscribe(channels, listener);
  } catch (err) {
    // End the response so the client reconnects and tries again
    console.error('Redis subscribe failed:', err);
    res.end();
    return;
  }
  subscribed = true;
  if (closed) {
    // The client disconnected while we were subscribing
    subscriber.unsubscribe(channels, listener).catch(() => {});
    return;
  }

  // Now that nothing new can be missed, send what was missed before this point. Clients from before this
  // was added don't say what they have, and check for themselves.
  if (req.query.turn !== undefined || req.query.since !== undefined) {
    const overviewChannel = channels.find(c => !c.includes('country')) || countryChannel[0].replace(/-country\d+$/, '');
    await sendMissedEvents((channel, message) => {
      if (!closed) listener(message, channel);
    }, overviewChannel, countryChannel[0], req.query);
  }
});

app.listen(ssePort, (err) => {
  // Express 5 passes a failed listen (e.g. the port still held by an old instance) to this callback rather
  // than throwing. Exit instead of carrying on, as otherwise this process would serve nothing while still
  // writing SSE_HEALTHCHECK, so status.php would report it as healthy.
  if (err) {
    console.error(`SSE server could not listen on port ${ssePort}:`, err);
    process.exit(1);
  }
  console.log(`SSE server listening at http://localhost:${ssePort}/events`);
});

// Health check: write timestamp to Redis every 10 seconds, so that status.php can see the server is alive
setInterval(async () => {
  try {
    console.log(`Setting SSE_HEALTHCHECK in Redis`);
    await redisClient.set('SSE_HEALTHCHECK', Date.now().toString());
  } catch (err) {
    console.error('Health check write failed:', err);
  }
}, 10000);

// Once a minute log how many clients are connected and how many messages were forwarded to them, as
// individual messages aren't logged
setInterval(() => {
  const channels = new Set();
  for (const clientChannels of openClients.values()) {
    for (const channel of clientChannels) channels.add(channel);
  }
  console.log(`SSE stats: ${openClients.size} clients open on ${channels.size} channels, `
    + `${messagesForwarded} messages forwarded to clients in the last minute`);
  messagesForwarded = 0;
}, 60000);
