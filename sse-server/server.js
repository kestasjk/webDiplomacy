// Simple node.js server that will serve an SSE feed of channel event messages 
// to clients, published to the Redis server from PHP as games are processed, 
// votes cast, and messages sent.
// Pusher / Durable Objects / Workers / etc was inefficient and expensive for 
// a simple client notification requirement.

// sseSecret and ssePort must be in sync with the secret and port set in Config.php

const crypto = require('crypto');
const cors = require('cors');
require('dotenv').config(); // Load environment variables from .env file
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

app.get('/events', async (req, res) => {

  const auth = req.query.auth;
  if (!auth) {
    res.status(400).send('Missing auth parameter');
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
    res.status(400).send('Invalid auth token for this channel; may be expired.');
    return;
  }

  // Authenticated and ready

  // Set headers for SSE
  res.writeHead(200, {
    'Content-Type': 'text/event-stream',
    'Cache-Control': 'no-cache',
    'Connection': 'keep-alive',
    'Access-Control-Allow-Origin': '*',
  });

  // Send initial comment to keep connection alive in some browsers
  res.write(`: connected to channels: ${channels.join(',')}\n\n`);

  // Create a Redis subscriber client with redisHost and redisPort
  const subscriber = Redis.createClient(
    {
      socket: {
        host: redisHost,
        port: redisPort,
      },
    }
  );
  subscriber.on('error', (err) => {
    console.error('Redis error:', err);
  });
  await subscriber.connect();

  // Send keep-alive comment and ping every 13 seconds
  const keepAliveInterval = setInterval(() => {
    res.write(': keep-alive\n\n');
    // Also send a ping:
    res.write(`event: message\n`);
    const data = JSON.stringify({ channel: 'ping', message: 'ping' });
    res.write(`data: ${data}\n\n`);
  }, 13000);

  // Subscribe to requested channels
  await subscriber.subscribe(channels, (message, channel) => {
    const data = JSON.stringify({ channel, message });
    console.info(`Received message on channel ${channel}:`, data);
    res.write(`event: message\n`);
    res.write(`data: ${data}\n\n`);
  });

  // Cleanup on client disconnect
  req.on('close', async () => {
    clearInterval(keepAliveInterval);
    try {
      await subscriber.unsubscribe(channels);
      await subscriber.quit();
    } catch (e) {
      // ignore errors on cleanup
    }
    res.end();
  });
});

app.listen(ssePort, () => {
  console.log(`SSE server listening at http://localhost:${ssePort}/events`);
});
