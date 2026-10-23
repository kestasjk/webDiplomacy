/*
    Copyright (C) 2026 Kestas J. Kuliukas

	This file is part of webDiplomacy.

    webDiplomacy is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    webDiplomacy is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with webDiplomacy.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * wD.Live: server-sent events for one game, as the /game/ board listens for them (game-src/src/lib/sselistener.ts).
 * PHP publishes to Redis and sse-server/server.js forwards to the browser. Events emitted here:
 *
 *   files     (changedFiles: string[])  the game's public files which were rewritten, with their versions noted in
 *                                        the GameSource; 'game' among them means the game changed or was processed
 *   message   ()                         a message for this player's country arrived
 *   processed ()                         the older "processed" event, from a server not publishing files events
 *   vote      ()                         another player voted (older "set-vote" event)
 *   gap       (reason)                   events may have been missed; everything should be checked again
 *   connected ()
 *
 *   var live = new wD.Live(source); live.on('files', fn); live.start();
 *
 * The token comes from the player's context (sseAuth), which also gives a spectator a token for the game's public
 * channel. When connecting, the page tells the server what it has (the versions of the files it loaded, and the
 * time it has messages up to), and the server sends what was missed and then a catchup event.
 */
var wD = window.wD || {};
window.wD = wD;

wD.Live = (function () {
	'use strict';

	var TOKEN_MAX_AGE_SECONDS = 23 * 60 * 60; // Tokens are accepted for a day
	var RECONNECT_AFTER_SILENCE_MS = 30000; // The server pings every 13 seconds
	var WATCHDOG_INTERVAL_MS = 5000;
	var CATCHUP_WAIT_MS = 5000;

	function log(message) {
		if (window.console) console.log('[SSE] ' + message);
	}

	// The token is "hash_timestamp", the timestamp being the server's time when it was made
	function tokenTime(auth) {
		return parseInt(String(auth).split('_')[1], 10) || 0;
	}

	/**
	 * @param {wD.GameSource} source
	 * @param {Object} [options] { messagesSince: function returning the unix time this page has messages up to }
	 */
	function Live(source, options) {
		this.source = source;
		this.options = options || {};
		this.callbacks = {};
		this.eventSource = null;
		this.started = false;
		this.hasConnectedBefore = false;
		this.tokenRefused = false;
		this.reconnecting = false;
		this.nextReconnectTime = 0;
		this.watchdog = null;
		this.catchupTimer = null;
	}

	Live.prototype.on = function (event, callback) {
		(this.callbacks[event] = this.callbacks[event] || []).push(callback);
		return this;
	};

	Live.prototype.emit = function (event) {
		var args = Array.prototype.slice.call(arguments, 1);
		(this.callbacks[event] || []).forEach(function (callback) {
			try { callback.apply(null, args); } catch (e) { if (window.console) console.error(e); }
		});
	};

	Live.prototype.start = function () {
		if (this.started) return;
		this.started = true;
		this.connect();
	};

	Live.prototype.stop = function () {
		this.started = false;
		if (this.watchdog) clearInterval(this.watchdog);
		this.watchdog = null;
		if (this.eventSource) this.eventSource.close();
		this.eventSource = null;
	};

	Live.prototype.touch = function () {
		this.nextReconnectTime = Date.now() + RECONNECT_AFTER_SILENCE_MS;
	};

	/** The token and channels to connect with, from the player's context; fetched again if the token is old or was refused */
	Live.prototype.getConnection = function () {
		var self = this;
		return this.source.getContext().then(function (context) {
			var tooOld = context.sseAuth && Math.abs(Date.now() / 1000 - tokenTime(context.sseAuth)) > TOKEN_MAX_AGE_SECONDS;
			if (context.sseAuth && (tooOld || self.tokenRefused)) {
				self.source.invalidateContext();
				return self.source.getContext();
			}
			return context;
		}).then(function (context) {
			if (!context.sseAuth) throw new Error('This server has no SSE server');
			return {
				auth: context.sseAuth,
				countryID: context.member ? context.member.countryID : 0,
				gameID: context.game.realGameID
			};
		});
	};

	Live.prototype.connect = function () {
		var self = this;
		this.reconnecting = true;

		this.getConnection().then(function (connection) {
			var isReconnection = self.hasConnectedBefore;
			var hasOpened = false;
			var gameID = connection.gameID;
			var channels = 'private-game' + gameID + ',private-game' + gameID + '-files,private-game' + gameID + '-country' + connection.countryID;
			var since = (self.options.messagesSince && self.options.messagesSince()) || tokenTime(connection.auth);

			var url = '/events?channelList=' + encodeURIComponent(channels) +
				'&auth=' + encodeURIComponent(connection.auth) +
				'&have=' + encodeURIComponent(self.source.loadedVersions()) +
				'&since=' + encodeURIComponent(since);

			if (self.eventSource) self.eventSource.close();
			var eventSource = new EventSource(url);
			self.eventSource = eventSource;

			eventSource.onopen = function () {
				log('Connected');
				hasOpened = true;
				self.tokenRefused = false;
				self.reconnecting = false;
				self.touch();
				self.emit('connected');
				if (window.wdClientLog) try { window.wdClientLog.metric(isReconnection ? 'SSE_RECONNECT' : 'SSE_CONNECT'); } catch (e) { /* ignore */ }
				// The server now sends what this page missed, then a catchup event. Without one, after a reconnection,
				// the page can't tell what it missed and checks everything.
				if (self.catchupTimer) clearTimeout(self.catchupTimer);
				self.catchupTimer = setTimeout(function () {
					if (isReconnection) self.emit('gap', 'no catchup event after reconnecting');
				}, CATCHUP_WAIT_MS);
				self.hasConnectedBefore = true;
			};

			eventSource.onerror = function () {
				log('Connection error or closed; will reconnect');
				eventSource.close();
				if (!hasOpened) self.tokenRefused = true; // The token may have been refused
				self.reconnecting = false;
				self.nextReconnectTime = Date.now(); // Have the watchdog reconnect
			};

			eventSource.onmessage = function (e) {
				self.touch();
				var data;
				try { data = JSON.parse(e.data); } catch (err) { log('Raw message: ' + e.data); return; }

				if (data.channel === 'resync') {
					// The server can't tell what this page missed: a key it checks wasn't set, or it lost Redis
					if (window.wdClientLog) try { window.wdClientLog.metric('SSE_RESYNC'); } catch (err) { /* ignore */ }
					self.emit('gap', 'a resync request');
					return;
				}
				if (data.channel === 'catchup') {
					if (self.catchupTimer) clearTimeout(self.catchupTimer);
					return;
				}
				if (data.channel === 'ping') return;

				var event;
				try { event = JSON.parse(data.message); } catch (err) { event = null; }
				if (!event) {
					// The older boards' events are plain strings
					if (typeof data.message === 'string' && data.message.indexOf('message') !== -1) self.emit('message');
					return;
				}

				if (event.event === 'files') {
					var changed = self.source.noteFileVersions(event.data || {});
					log('Files changed: ' + (changed.join(', ') || 'none new'));
					if (changed.length) self.emit('files', changed);
				} else if (event.event === 'message') {
					self.emit('message');
				} else if (event.event === 'overview') {
					// Published by every version of the server; a server running older code publishes no files event
					if (event.data === 'processed') {
						self.source.invalidateContext();
						self.emit('processed');
					} else if (event.data === 'set-vote') {
						self.emit('vote');
					}
				}
			};

			if (self.watchdog) clearInterval(self.watchdog);
			self.touch();
			self.watchdog = setInterval(function () {
				if (!self.started) return;
				if (!self.reconnecting && Date.now() >= self.nextReconnectTime) {
					log('Nothing heard from the server in time; reconnecting');
					if (self.eventSource) self.eventSource.close();
					self.connect();
				}
			}, WATCHDOG_INTERVAL_MS);
		}).catch(function (error) {
			if (window.console) console.error('Failed to start the SSE connection:', error);
			self.reconnecting = false;
			// Try again later; getConnection() may have failed on a network error
			self.nextReconnectTime = Date.now() + RECONNECT_AFTER_SILENCE_MS;
			if (!self.watchdog) {
				self.watchdog = setInterval(function () {
					if (self.started && !self.reconnecting && Date.now() >= self.nextReconnectTime) self.connect();
				}, WATCHDOG_INTERVAL_MS);
			}
		});
	};

	return Live;
})();
