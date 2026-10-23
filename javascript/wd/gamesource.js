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
 * wD.GameSource: where the classic board's JavaScript gets a game from, and how it writes to it. The same sources
 * the /game/ board uses (doc/gamedata/02-spec.md, game-src/src/utils/api/gameSource.ts):
 *
 * - the game's public JSON files (variant, game, status, history, messages), static files fetched by version
 * - game/playercontext for what depends on who is asking: the player's country, orders and order token, private
 *   messages, the SSE token
 * - the writes: ajax.php for orders, game/sendmessage and game/messagesseen for press
 *
 * Messages are kept in localStorage per game and country, so that opening a game only asks the server for the
 * messages sent since the last one it has (the playercontext messagesSince cursor), and the history filter
 * (messageTurns) limits how far back the first load goes.
 *
 *   var source = new wD.GameSource({ gameID: 123, sbToken: null });
 *   source.getContext().then(function (ctx) { ... });
 *   source.getFile('game').then(function (game) { ... });
 *
 * Everything returns a Promise. Nothing here touches the page.
 */
var wD = window.wD || {};
window.wD = wD;

wD.GameSource = (function () {
	'use strict';

	var FILES = ['game', 'status', 'history', 'messages'];
	var REQUEST_TIMEOUT = 60000;
	// How many messages the local cache keeps per game, newest first
	var MESSAGE_CACHE_MAX = 3000;
	var STORAGE_PREFIX = 'wD.msgs.';

	function fetchWithTimeout(url, options) {
		options = options || {};
		var controller = (typeof AbortController === 'function') ? new AbortController() : null;
		if (controller) options.signal = controller.signal;
		options.credentials = 'same-origin';
		var timer = controller ? setTimeout(function () { controller.abort(); }, REQUEST_TIMEOUT) : null;
		return fetch(url, options).then(function (response) {
			if (timer) clearTimeout(timer);
			return response;
		}, function (error) {
			if (timer) clearTimeout(timer);
			throw error;
		});
	}

	function metric(name, ms) {
		if (window.wdClientLog && typeof window.wdClientLog.metric === 'function') {
			try { window.wdClientLog.metric(name, ms); } catch (e) { /* metrics never break the page */ }
		}
	}

	function storageGet(key) {
		try {
			var raw = window.localStorage.getItem(key);
			return raw ? JSON.parse(raw) : null;
		} catch (e) {
			return null;
		}
	}

	function storageSet(key, value) {
		try {
			window.localStorage.setItem(key, JSON.stringify(value));
			return true;
		} catch (e) {
			// Full or disabled; the cache is only an optimisation
			return false;
		}
	}

	/**
	 * @param {Object} options { gameID, sbToken, siteRoot: path prefix the file URLs are relative to ('' from the
	 *   site root), countryID: the country to act as, in sandbox games where the user holds several }
	 */
	function GameSource(options) {
		this.gameID = parseInt(options.gameID, 10);
		this.sbToken = options.sbToken || null;
		this.siteRoot = options.siteRoot || '';
		this.countryID = options.countryID || null;

		// The newest versions heard of, from game/playercontext or a files event; a file is fetched again when its
		// version here differs from the version loaded
		this.versions = {};
		this.fileURLs = null;
		this.loadedFiles = {};

		this.context = null;
		this.contextStale = true;
		this.contextRequest = null;

		// What the first context request asks for in the way of messages (see initialMessageParams()), and the
		// private messages it brought, until loadMessages() has used them
		this.firstMessageParams = { messages: '1' };
		this.firstMessages = null;
	}

	GameSource.prototype.apiURL = function (route, params) {
		var query = new URLSearchParams(params || {});
		query.set('route', route);
		if (this.sbToken) query.set('sbToken', this.sbToken);
		return this.siteRoot + 'api.php?' + query.toString();
	};

	GameSource.prototype.getJSON = function (url) {
		var started = performance.now();
		return fetchWithTimeout(url).then(function (response) {
			if (!response.ok) {
				return response.text().then(function (text) {
					var error = new Error(text || ('HTTP ' + response.status));
					error.status = response.status;
					throw error;
				});
			}
			return response.json();
		}).then(function (data) {
			metric('BOARD_FETCH', performance.now() - started);
			return data;
		});
	};

	GameSource.prototype.postJSON = function (route, body) {
		return fetchWithTimeout(this.apiURL(route), {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify(body)
		}).then(function (response) {
			return response.text().then(function (text) {
				if (!response.ok) {
					var error = new Error(text || ('HTTP ' + response.status));
					error.status = response.status;
					throw error;
				}
				return text ? JSON.parse(text) : null;
			});
		});
	};

	/* Context */

	GameSource.prototype.requestContext = function (params) {
		var self = this;
		var query = { gameID: String(this.gameID) };
		if (this.countryID) query.countryID = String(this.countryID);
		Object.keys(params || {}).forEach(function (key) { query[key] = String(params[key]); });

		return this.getJSON(this.apiURL('game/playercontext', query)).then(function (context) {
			self.fileURLs = context.files;
			FILES.forEach(function (file) {
				if (context.files[file] && context.files[file].version) self.versions[file] = context.files[file].version;
			});
			return context;
		});
	};

	/**
	 * The player's context with their orders; fetched again only after invalidateContext() or a new game.json.
	 * Callers asking at once share one request. The first request also asks for the player's messages, as
	 * initialMessageParams() last set, so that loading a game is one request.
	 */
	GameSource.prototype.getContext = function () {
		var self = this;
		if (this.context && !this.contextStale) return Promise.resolve(this.context);
		if (this.contextRequest) return this.contextRequest;

		var isFirst = (this.context === null);
		var params = { orders: '1' };
		if (isFirst && this.firstMessageParams) {
			Object.keys(this.firstMessageParams).forEach(function (key) { params[key] = self.firstMessageParams[key]; });
		}
		var started = performance.now();
		this.contextRequest = this.requestContext(params).then(function (context) {
			if (isFirst) {
				metric('BOARD_CONTEXT', performance.now() - started);
				self.firstMessages = context.messages;
			}
			self.context = context;
			self.contextStale = false;
			return context;
		}).finally(function () {
			self.contextRequest = null;
		});
		return this.contextRequest;
	};

	/** Anything which changes the player's own orders, status or votes makes the held context out of date */
	GameSource.prototype.invalidateContext = function () {
		this.contextStale = true;
	};

	/* Files */

	/**
	 * Note the versions in a files event from the SSE server.
	 * @returns {string[]} The files whose version is news
	 */
	GameSource.prototype.noteFileVersions = function (changed) {
		var self = this;
		var news = [];
		FILES.forEach(function (file) {
			if (changed[file] && self.versions[file] !== changed[file]) {
				self.versions[file] = changed[file];
				news.push(file);
			}
		});
		// A new game.json means the game was processed or otherwise changed, and with it the player's orders and token
		if (news.indexOf('game') !== -1) this.contextStale = true;
		return news;
	};

	/** The versions loaded, as "game:v,status:v,…", for telling the SSE server what this page has */
	GameSource.prototype.loadedVersions = function () {
		var self = this;
		return FILES.filter(function (file) { return self.loadedFiles[file]; })
			.map(function (file) { return file + ':' + self.loadedFiles[file].version; })
			.join(',');
	};

	/**
	 * @param {string} file 'variant', 'game', 'status', 'history' or 'messages'
	 * @returns {Promise<Object>} The file's contents, from memory if the version loaded is current
	 */
	GameSource.prototype.getFile = function (file) {
		var self = this;
		return this.getContext().then(function () {
			if (!self.fileURLs) throw new Error('No game context loaded');
			var ref = self.fileURLs[file];
			if (!ref || !ref.url) throw new Error('The game has no ' + file + ' file');
			var version = (file === 'variant' ? ref.version : self.versions[file]) || '';

			var loaded = self.loadedFiles[file];
			if (loaded && loaded.version === version) return loaded.contents;

			return self.getJSON(self.siteRoot + ref.url + '?v=' + encodeURIComponent(version)).then(function (contents) {
				// The file says which version it is, which is newer than the one asked for if it was rewritten in between
				self.loadedFiles[file] = { version: contents.version || version, contents: contents };
				if (file !== 'variant' && contents.version) self.versions[file] = contents.version;
				return contents;
			});
		});
	};

	GameSource.prototype.getFiles = function (files) {
		var self = this;
		return Promise.all(files.map(function (file) { return self.getFile(file); })).then(function (results) {
			var byName = {};
			files.forEach(function (file, i) { byName[file] = results[i]; });
			return byName;
		});
	};

	/* Messages */

	GameSource.prototype.messageCacheKey = function (countryID) {
		return STORAGE_PREFIX + this.gameID + '.' + (countryID || 0);
	};

	/**
	 * The locally saved messages for this game and country: { cursor, fromTurn, private: [], publicVersion, public: [] }.
	 * cursor is the playercontext messagesSince to ask from next; fromTurn is the earliest turn the private messages
	 * are complete from (0 when every turn was loaded).
	 */
	GameSource.prototype.readMessageCache = function (countryID) {
		var cache = storageGet(this.messageCacheKey(countryID));
		if (!cache || cache.schema !== 1) return null;
		return cache;
	};

	GameSource.prototype.writeMessageCache = function (countryID, cache) {
		cache.schema = 1;
		cache.saved = Math.floor(Date.now() / 1000);
		// Keep the newest if there are too many to store
		['private', 'public'].forEach(function (list) {
			if (cache[list].length > MESSAGE_CACHE_MAX) cache[list] = cache[list].slice(cache[list].length - MESSAGE_CACHE_MAX);
		});
		storageSet(this.messageCacheKey(countryID), cache);
	};

	function mergeByID(existing, incoming) {
		var seen = {};
		var merged = [];
		existing.concat(incoming).forEach(function (message) {
			if (seen[message.id]) return;
			seen[message.id] = true;
			merged.push(message);
		});
		merged.sort(function (a, b) { return a.id - b.id; });
		return merged;
	}

	function emptyMessageCache() {
		return { cursor: null, fromTurn: null, private: [], publicVersion: null, public: [] };
	}

	/** The earliest turn a filter of so many turns back reaches, at the given turn; 0 when every turn is wanted */
	function fromTurnFor(turns, currentTurn) {
		return (turns === null) ? 0 : Math.max(0, currentTurn - turns);
	}

	/** Whether the cache holds the private messages from wantFromTurn on, so only newer ones need asking for */
	function cacheCovers(cache, wantFromTurn) {
		return cache.cursor !== null && cache.fromTurn !== null && cache.fromTurn <= wantFromTurn;
	}

	/**
	 * Decide what the first context request asks for in the way of the player's messages, before anything has
	 * asked for the context: only the ones since the local cache's cursor if the cache reaches back as far as the
	 * history filter wants, or else the last so many turns. Call before getContext().
	 *
	 * @param {number} countryID The viewer's country, 0 for none
	 * @param {number|null} turns How many turns back messages are wanted, null for all
	 * @param {number} currentTurn The game's turn, as the page was given it
	 */
	GameSource.prototype.initialMessageParams = function (countryID, turns, currentTurn) {
		var params = { messages: '1' };
		if (countryID > 0) {
			var cache = this.readMessageCache(countryID);
			if (cache && cacheCovers(cache, fromTurnFor(turns, currentTurn))) params.messagesSince = String(cache.cursor);
			else if (turns !== null) params.messageTurns = String(turns);
		}
		this.firstMessageParams = params;
		return params;
	};

	/**
	 * The messages the viewer may see: everyone's from messages.json and, for a member, their own from
	 * game/playercontext, loaded from the local cache first and then only the ones since the cache's cursor.
	 *
	 * @param {Object} opts { countryID: the viewer's country (0 for a spectator or moderator), turns: how many turns
	 *   back the private messages are wanted (null for all) }
	 * @returns {Promise<{messages: Object[], newMessagesFrom: number[], fromTurn: number}>} messages oldest first
	 */
	GameSource.prototype.loadMessages = function (opts) {
		var self = this;
		var countryID = opts.countryID || 0;
		var turns = (opts.turns === undefined || opts.turns === null) ? null : parseInt(opts.turns, 10);

		return this.getContext().then(function (context) {
			var cache = self.readMessageCache(countryID) || emptyMessageCache();
			var wantFromTurn = fromTurnFor(turns, context.game.turn);
			var covered = cacheCovers(cache, wantFromTurn);

			var publicPromise = Promise.resolve(cache.public);
			var messagesFile = context.files.messages;
			if (messagesFile && messagesFile.url) {
				var version = self.versions.messages || messagesFile.version;
				if (version && cache.publicVersion === version) {
					// The cached copy is the current file; nothing to fetch
					if (!self.loadedFiles.messages) self.loadedFiles.messages = { version: version, contents: { messages: cache.public } };
				} else {
					publicPromise = self.getFile('messages').then(function (file) {
						cache.publicVersion = file.version || version || null;
						cache.public = file.messages || [];
						return cache.public;
					});
				}
			}

			var privatePromise = Promise.resolve(null);
			if (countryID > 0 && context.member && context.member.countryID === countryID) {
				if (self.firstMessages !== null) {
					// Loading the page: the messages came with the first context, asked for as initialMessageParams() decided
					privatePromise = Promise.resolve(self.firstMessages);
					if (!covered) { cache.private = []; cache.fromTurn = wantFromTurn; }
				} else if (covered) {
					// Only what was sent since the cache's cursor
					privatePromise = self.requestContext({ messages: '1', messagesSince: String(cache.cursor) }).then(function (ctx) {
						return ctx.messages;
					});
				} else {
					// The filter reaches further back than the cache does: load the range again
					var params = { messages: '1' };
					if (turns !== null) params.messageTurns = String(turns);
					privatePromise = self.requestContext(params).then(function (ctx) {
						cache.private = [];
						cache.fromTurn = wantFromTurn;
						return ctx.messages;
					});
				}
				self.firstMessages = null;
			}

			return Promise.all([publicPromise, privatePromise]).then(function (results) {
				var own = results[1];
				if (own && own.messages) cache.private = mergeByID(cache.private, own.messages);
				if (own && own.cursor) cache.cursor = own.cursor;
				if (cache.fromTurn === null) cache.fromTurn = wantFromTurn;
				self.writeMessageCache(countryID, cache);

				return {
					messages: mergeByID(cache.public, cache.private),
					newMessagesFrom: context.member ? context.member.newMessagesFrom : [],
					fromTurn: cache.fromTurn
				};
			});
		});
	};

	/** Add messages this client just sent to the cache, so they aren't asked for again */
	GameSource.prototype.rememberMessages = function (countryID, messages) {
		var cache = this.readMessageCache(countryID);
		if (!cache || !messages || !messages.length) return;
		messages.forEach(function (message) {
			if (message.toCountryID === 0) cache.public = mergeByID(cache.public, [message]);
			else cache.private = mergeByID(cache.private, [message]);
		});
		this.writeMessageCache(countryID, cache);
	};

	/**
	 * @returns {Promise<Object[]>} The message as stored (escaped) by the server, with its id
	 */
	GameSource.prototype.sendMessage = function (fromCountryID, toCountryID, message) {
		var self = this;
		return this.postJSON('game/sendmessage', {
			gameID: String(this.gameID),
			countryID: String(fromCountryID),
			toCountryID: String(toCountryID),
			message: message
		}).then(function (data) {
			var messages = (data && data.messages) ? data.messages : [];
			self.rememberMessages(fromCountryID, messages);
			return messages;
		});
	};

	GameSource.prototype.markMessagesSeen = function (countryID, seenCountryID) {
		return this.postJSON('game/messagesseen', {
			gameID: String(this.gameID),
			countryID: String(countryID),
			seenCountryID: String(seenCountryID)
		});
	};

	/* Orders */

	/**
	 * Save orders through ajax.php, as the drop-down form and the /game/ board do.
	 * @param {Object} submission { orderUpdates: [], context: string|Object, contextKey, ready: bool, notready: bool }
	 * @returns {Promise<Object>} ajax.php's result: { orders: {id: {status, notice, changed}}, notice, statusIcon,
	 *   statusText, invalid, newContext, newContextKey }
	 */
	GameSource.prototype.saveOrders = function (submission) {
		this.invalidateContext();

		var body = new URLSearchParams();
		body.set('orderUpdates', JSON.stringify(submission.orderUpdates));
		body.set('context', typeof submission.context === 'string' ? submission.context : JSON.stringify(submission.context));
		body.set('contextKey', submission.contextKey);

		var url = this.siteRoot + 'ajax.php';
		if (submission.ready) url += '?ready=on';
		else if (submission.notready) url += '?notready=on';

		return fetchWithTimeout(url, {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: body.toString()
		}).then(function (response) {
			return response.text().then(function (text) {
				var result = null;
				try { result = JSON.parse(text); } catch (e) { result = null; }
				if (!result || typeof result !== 'object') {
					// An error page rather than a result: most often the game has moved on since the orders were loaded
					return { invalid: true, notice: 'The orders could not be saved; the game may have moved on to the next phase. Reload the board.', orders: {} };
				}
				return result;
			});
		});
	};

	GameSource.FILES = FILES;

	return GameSource;
})();
