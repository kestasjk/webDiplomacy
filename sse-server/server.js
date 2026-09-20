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
// board that is a game/playercontext request, which sets both keys again. A catchup event always ends the check,
// which tells the client that this server does it; a client which doesn't get one checks for itself.
// The events are in the format PHP publishes them in (RedisInterface::trigger).
//
// A client of the game's public JSON files (lib/gamefiles.php) instead says which version of each file it
// has: have=game:<version>,status:<version>,... That is compared with
// - gamefiles_{gameID}: {"<file>":{"v":"<version>",..},..}, set whenever a file is rewritten (libGameFiles::refresh)
// and the client is sent the files event it would have received, on the game's files channel
// (private-game{gameID}-files), for the files whose versions differ.
async function sendMissedEvents(write, overviewChannel, countryChannel, query) {
  const hasTurnPhase = query.turn !== undefined && query.phase !== undefined;
  const since = parseInt(query.since, 10);
  const have = parseHave(query.have);
  let resync = false;

  try {
    const match = countryChannel.match(/^private-game(\d+)-country(\d+)$/);
    if (!match) throw new Error(`Unexpected country channel ${countryChannel}`);
    const [, gameID, countryID] = match;

    const [turnPhase, lastMessageTime, gameFiles] = await Promise.all([
      hasTurnPhase ? redisClient.get(`gameTurnPhase_${gameID}`) : null,
      !isNaN(since) ? redisClient.get(`lastmsgtime_${gameID}_${countryID}`) : null,
      have ? redisClient.get(`gamefiles_${gameID}`) : null,
    ]);

    if (have) {
      if (gameFiles === null) resync = true;
      else {
        const changed = {};
        for (const [file, state] of Object.entries(JSON.parse(gameFiles))) {
          if (state && state.v && have[file] !== state.v) changed[file] = state.v;
        }
        if (Object.keys(changed).length > 0) {
          write(`${overviewChannel}-files`, JSON.stringify({ event: 'files', data: changed }));
        }
      }
    }

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

// The have parameter, "game:6aadf087d779bc,status:..", as { game: '6aadf087d779bc', .. }, or null if there isn't one
function parseHave(have) {
  if (typeof have !== 'string') return null;
  const versions = {};
  for (const part of have.split(',')) {
    const [file, version] = part.split(':');
    if (file && version) versions[file.trim()] = version.trim();
  }
  return versions;
}

// The current time as PHP's time() gives it, for the keys status.php reads
function unixTime() {
  return Math.floor(Date.now() / 1000).toString();
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

  // The token is for one country of one game (country 0 for someone who is only watching the game), and is good
  // for that country's channel and the game's own channels (overview and files) and nothing else; without this
  // check a valid token for one game could be used to listen to any other game's events.
  const tokenChannel = countryChannel[0].match(/^(private-game\d+)-country\d+$/);
  if( !tokenChannel || channels.some(c => c !== countryChannel[0] && c !== tokenChannel[1] && c !== `${tokenChannel[1]}-files`) )
  {
    res.status(403).send('The auth token does not cover all of the channels asked for.');
    return;
  }

  // Authenticated and ready

  // Record client connection timestamp (not awaited; it is only for status.php and shouldn't delay the client)
  console.log(`Client connected (${openClients.size + 1} open). Setting SSE_LASTCLIENTCONNECT in Redis`);
  // In Unix seconds: status.php compares these against PHP's time(), as it does its own REDIS_HEALTHCHECK
  redisClient.set('SSE_LASTCLIENTCONNECT', unixTime()).catch((err) => {
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
  if (req.query.turn !== undefined || req.query.since !== undefined || req.query.have !== undefined) {
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
    await redisClient.set('SSE_HEALTHCHECK', unixTime());
  } catch (err) {
    console.error('Health check write failed:', err);
  }
}, 10000);

// ---------------------------------------------------------------------------
// The gamemaster driver
//
// Nothing on the site happens on its own: gamemaster.php has to be called in a loop for games to
// process, votes to apply, and for gamemaster/backgroundTasks.php to run everything that used to be
// on cron (game backups, group and user connection updates, reliability ratings, NMR warnings).
//
// That loop used to be a shell script - `while true; do wget ...; sleep 1; done` - running
// unsupervised on another machine. It is here instead because this process is already looked after:
// gitpull.php starts it, restarts it when sse-server/ changes and keeps its pid and log, so one
// running SSE server now means the whole site is running.
//
// It stays an HTTP request to the site's public URL rather than becoming code in this file or a
// call to localhost. The comment at the top of gamemaster.php explains why: if the site stops being
// reachable while the server itself is still up, game processing must stop with it, rather than
// turns passing while nobody can play. GAMEMASTER_URL should be the URL a player's browser uses.
//
// Runs are strictly sequential - the next is scheduled once the last finishes, never on a timer -
// because gamemaster.php takes a MySQL lock, get_lock('gamemaster',1), that an overlapping run
// would wait one second for and then give up on with an error page. A run can take 30 seconds or
// more, so a timer would pile them up on a busy site.
const gamemasterUrl = process.env.GAMEMASTER_URL || '';
const gamemasterSecret = process.env.GAMEMASTER_SECRET || '';
const gamemasterIntervalMs = parseInt(process.env.GAMEMASTER_INTERVAL_MS || '1000', 10);
// gamemaster.php sets its own max_execution_time to 300 seconds, so nothing useful is waiting for
// after that; this only stops one stuck request from stopping the loop forever.
const gamemasterTimeoutMs = parseInt(process.env.GAMEMASTER_TIMEOUT_MS || '300000', 10);

// gamemaster.php prints this before it looks for anything to do. A response without it was turned
// away at the top of the script (processing disabled, the downtime trigger, a failed permission
// check) or died on the way, and either way nothing was processed.
const GAMEMASTER_MARKER = 'applying votes';

let gamemasterRuns = 0; // Successful runs since the last stats line
let gamemasterFailures = 0; // Failed runs since the last stats line
let gamemasterConsecutiveFailures = 0; // Failures in a row, which is what decides whether to log
let gamemasterFailureLogged = 0; // When this run of failures was last written to the log

// What matters in a failed run is the text of the page - the PHP error, or the notice saying why it
// wouldn't run - and the rest is the site's menu and footer. This does what the old
// runGamemaster_errorLogFilter.sh did to the .html files the shell loop saved, so that the reason
// lands in this server's log instead of in a file per failure: keep what lies between the header's
// noscript block and the footer, then drop the tags. A page that died before printing either of
// those has neither, and is kept whole.
function gamemasterPageText(html) {
  let content = String(html);

  const bodyStart = content.indexOf('</noscript>');
  if (bodyStart !== -1) content = content.slice(bodyStart + '</noscript>'.length);
  const bodyEnd = content.indexOf('<div id="footer">');
  if (bodyEnd !== -1) content = content.slice(0, bodyEnd);

  const text = content
    .replace(/<script\b[^>]*>[\s\S]*?<\/script>/gi, ' ')
    .replace(/<style\b[^>]*>[\s\S]*?<\/style>/gi, ' ')
    .replace(/<[^>]*>/g, ' ')
    .replace(/&nbsp;/g, ' ')
    .replace(/\s+/g, ' ')
    .trim();

  return text.length > 1500 ? text.slice(0, 1500) + ' [...]' : text;
}

// A site that is down would otherwise put a line a second in the log: report the first failure, then
// at most one a minute for as long as it carries on, and one line when it starts working again.
function gamemasterFailed(reason, body) {
  gamemasterFailures++;
  gamemasterConsecutiveFailures++;

  const now = Date.now();
  if (gamemasterConsecutiveFailures > 1 && (now - gamemasterFailureLogged) < 60000) return;
  gamemasterFailureLogged = now;

  const text = body ? gamemasterPageText(body) : '';
  console.error(`Gamemaster call failed (${gamemasterConsecutiveFailures} in a row): ${reason}`
    + (text ? `: ${text}` : ''));
}

async function runGamemaster() {
  const url = gamemasterUrl + (gamemasterUrl.includes('?') ? '&' : '?')
    + 'gameMasterSecret=' + encodeURIComponent(gamemasterSecret);

  const controller = new AbortController();
  const timeout = setTimeout(() => controller.abort(), gamemasterTimeoutMs);
  try {
    const response = await fetch(url, { signal: controller.signal });
    const body = await response.text();

    // Any answer at all means this loop is running and reaching the site, which is what status.php
    // reads this for: wD_Misc.LastProcessTime stops moving both when nothing is calling the
    // gamemaster and when the gamemaster is refusing the calls, and this tells those two apart.
    redisClient.set('GAMEMASTER_LASTRUN', unixTime()).catch((err) => {
      console.error('GAMEMASTER_LASTRUN write failed:', err);
    });

    if (!response.ok) {
      gamemasterFailed(`HTTP ${response.status}`, body);
      return;
    }
    if (!body.includes(GAMEMASTER_MARKER)) {
      gamemasterFailed('the run did not get as far as processing games', body);
      return;
    }

    if (gamemasterConsecutiveFailures > 0) {
      console.log(`Gamemaster call succeeded again, after ${gamemasterConsecutiveFailures} failures`);
      gamemasterConsecutiveFailures = 0;
    }
    gamemasterRuns++;
  } catch (err) {
    gamemasterFailed(err && err.name === 'AbortError'
      ? `no response within ${gamemasterTimeoutMs}ms`
      : String(err && err.message ? err.message : err), null);
  } finally {
    clearTimeout(timeout);
  }
}

// Nothing here may throw into the rest of this process: the point of moving the loop in was that one
// running server means everything is running, which a gamemaster failure taking SSE down would undo.
async function gamemasterLoop() {
  for (;;) {
    try {
      await runGamemaster();
    } catch (err) {
      console.error('Gamemaster loop error:', err);
    }
    await new Promise((resolve) => setTimeout(resolve, gamemasterIntervalMs));
  }
}

if (!gamemasterUrl) {
  // Every site drives its own gamemaster at its own URL, so this is only for an install that should
  // not be processing games at all
  console.log('No GAMEMASTER_URL is set, so the gamemaster is not run from here');
} else if (typeof fetch !== 'function') {
  console.error('This node has no global fetch (node 18 or newer is needed), so the gamemaster cannot be run from here');
} else {
  console.log(`Running the gamemaster at ${gamemasterUrl} every ${gamemasterIntervalMs}ms`);
  gamemasterLoop();
}

// Once a minute log how many clients are connected and how many messages were forwarded to them, as
// individual messages aren't logged
setInterval(() => {
  const channels = new Set();
  for (const clientChannels of openClients.values()) {
    for (const channel of clientChannels) channels.add(channel);
  }
  console.log(`SSE stats: ${openClients.size} clients open on ${channels.size} channels, `
    + `${messagesForwarded} messages forwarded to clients in the last minute`
    + (gamemasterUrl ? `; ${gamemasterRuns} gamemaster runs, ${gamemasterFailures} failed` : ''));
  messagesForwarded = 0;
  gamemasterRuns = 0;
  gamemasterFailures = 0;
}, 60000);
