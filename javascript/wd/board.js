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
 * wD.Board: wires up board.php's JavaScript: one wD.GameSource and one wD.Live connection for the page, the press
 * panel (wD.Chat) if the page has one, and the canvas board (wD.CanvasBoard) if the user has chosen it.
 *
 * When the game moves on to a new phase the page refreshes itself a few seconds later, as most of it (the order form
 * or the canvas board's buttons, the members' statuses, the phase and deadline) is drawn by board.php. It waits while
 * the user is writing something (a message, a note, any text field), or has entered orders for the new phase on the
 * canvas board (which updates in place) and not saved them; a toast says so and lets them refresh by hand or stay.
 *
 *   wD.Board.init({ gameID, sbToken, turn, phase, reloadURL, chat: {...} | null, canvas: {...} | null });
 *
 * reloadURL is the page to load for a refresh when it was drawn for a form's POST (reloading that would post the
 * form again); otherwise the page is reloaded as it is.
 */
var wD = window.wD || {};
window.wD = wD;

wD.Board = (function () {
	'use strict';

	var REFRESH_DELAY_MS = 4000; // Time to read the toast, or to say stay
	var REFRESH_JITTER_MS = 1500; // So that everyone watching a game doesn't load it in the same instant
	var WRITING_CHECK_MS = 2000;
	var TYPING_PAUSE_MS = 8000; // Typing this recently counts as writing, even in an empty box
	var REFRESH_GUARD_MS = 60000; // A refresh to the same phase this recently isn't repeated: something is amiss
	var GUARD_KEY = 'wdBoardAutoRefresh';

	var lastTyped = 0;

	function isTextField(node) {
		if (!node || !node.tagName) return false;
		if (node.isContentEditable) return true;
		var tag = node.tagName.toLowerCase();
		if (tag === 'textarea') return true;
		if (tag !== 'input') return false;
		return ['text', 'search', 'email', 'url', 'tel', 'number', 'password'].indexOf((node.type || 'text').toLowerCase()) !== -1;
	}

	/** Whether the user is writing: typing just now, or a text field holds something they changed and haven't sent */
	function isWriting() {
		if (Date.now() - lastTyped < TYPING_PAUSE_MS) return true;
		var fields = document.querySelectorAll('textarea, input');
		for (var i = 0; i < fields.length; i++) {
			var field = fields[i];
			if (!isTextField(field)) continue;
			if (field.value && field.value.trim() !== '' && field.value !== field.defaultValue) return true;
		}
		return false;
	}

	function readGuard() {
		try { return JSON.parse(window.sessionStorage.getItem(GUARD_KEY) || 'null'); } catch (e) { return null; }
	}

	function writeGuard(value) {
		try { window.sessionStorage.setItem(GUARD_KEY, JSON.stringify(value)); } catch (e) { /* no guard then */ }
	}

	/**
	 * Refreshing the page when the game moves on.
	 * @returns {function(string, string)} Call with the toast's text ("The game has been processed: ...") and the new
	 *   phase as "turn|phase"
	 */
	function makeRefresher(config, toast, page) {
		var pending = null; // { text, phaseKey, cancelled, timer, shown }

		/** What the refresh has to wait for: 'writing', 'orders' (entered on the canvas and not saved), or null */
		function busy() {
			if (isWriting()) return 'writing';
			var entry = page.canvas && page.canvas.entry;
			if (entry && (entry.hasUnsaved() || entry.current || page.canvas.saving)) return 'orders';
			return null;
		}

		document.addEventListener('input', function (e) { if (isTextField(e.target)) lastTyped = Date.now(); }, true);

		function refreshNow() {
			writeGuard({ gameID: config.gameID, phaseKey: pending ? pending.phaseKey : null, time: Date.now() });
			// The orders on the page were for the phase which has gone, so leaving them isn't worth a warning
			window.ordersChanged = false;
			window.leavepagedanger = false;
			if (config.reloadURL) window.location.replace(config.reloadURL);
			else window.location.reload();
		}

		function show(state) {
			if (!pending || pending.shown === state) return;
			pending.shown = state;
			var stay = { label: 'Stay on this page', onClick: function () { cancel(); } };
			var now = { label: 'Refresh now', onClick: function () { refreshNow(); } };
			if (state === 'writing' || state === 'orders') {
				toast.show({
					text: pending.text + (state === 'writing' ? ' The page will refresh once you have finished writing.'
						: ' The page will refresh once your orders are saved.'),
					key: 'processed', timeout: 0, kind: 'success', actions: [now, stay]
				});
			} else if (state === 'refreshing') {
				toast.show({ text: pending.text + ' Refreshing the page...', key: 'processed', timeout: 0, kind: 'success', actions: [stay] });
			} else {
				toast.show({ text: pending.text + ' Refresh the page to see the new phase.', key: 'processed', timeout: 0, kind: 'success', actions: [now] });
			}
		}

		function cancel() {
			if (!pending) return;
			if (pending.timer) clearTimeout(pending.timer);
			pending.cancelled = true;
			show('manual');
		}

		function tick() {
			if (!pending || pending.cancelled) return;
			pending.timer = null;
			var reason = busy();
			if (reason) {
				show(reason);
				pending.timer = setTimeout(tick, WRITING_CHECK_MS);
				return;
			}
			if (pending.shown !== 'refreshing') {
				show('refreshing');
				pending.timer = setTimeout(tick, REFRESH_DELAY_MS + Math.floor(Math.random() * REFRESH_JITTER_MS));
				return;
			}
			refreshNow();
		}

		return function (text, phaseKey) {
			if (pending && !pending.cancelled) {
				// Moved on again while waiting: say so
				var state = pending.shown;
				pending.text = text;
				pending.phaseKey = phaseKey;
				pending.shown = null;
				if (state) show(state);
				return;
			}
			pending = { text: text, phaseKey: phaseKey, cancelled: false, timer: null, shown: null };
			// Refreshed for this phase a moment ago, and the page still had the old one: don't go round again
			var guard = readGuard();
			if (guard && guard.gameID === config.gameID && guard.phaseKey === phaseKey && Date.now() - guard.time < REFRESH_GUARD_MS) {
				pending.cancelled = true;
				show('manual');
				return;
			}
			tick();
		};
	}

	function init(config) {
		var source = new wD.GameSource({ gameID: config.gameID, sbToken: config.sbToken || null, siteRoot: '' });
		var toast = wD.Toast;
		var chatContainer = document.getElementById('wdChat');
		var canvasContainer = document.getElementById('wdCanvasBoard');
		var chat = null;
		var countryID = config.chat ? parseInt(config.chat.countryID, 10) || 0 : 0;

		var live = new wD.Live(source, {
			// The time this page has messages up to, for the SSE server to tell what it missed on connecting
			messagesSince: function () {
				var cache = source.readMessageCache(countryID);
				return cache && cache.cursor ? cache.cursor : null;
			}
		});

		if (chatContainer && config.chat) {
			chat = new wD.Chat(chatContainer, { source: source, live: live, toast: toast, config: config.chat });
			chat.prepare(); // Decides what the first context request asks for, so must come before anything loads
		}

		// What the page is running, for the browser console (wD.page) and for tests
		var page = { source: source, live: live, chat: chat, toast: toast, canvas: null, isWriting: isWriting };
		var refreshForNewPhase = makeRefresher(config, toast, page);
		page.refreshForNewPhase = refreshForNewPhase;
		wD.page = page;

		var loads = [];
		if (chat) {
			chat.start();
			loads.push(source.getContext());
		}

		if (canvasContainer && config.canvas) {
			loads.push(wD.CanvasBoard.create(canvasContainer, {
				source: source, live: live, toast: toast, config: config.canvas, onNewPhase: refreshForNewPhase
			}).then(function (board) {
				page.canvas = board;
			}).catch(function () { /* shown in the board */ }));
		} else {
			// The map image and the drop-down form only change with a page load
			var pageTurn = config.turn;
			var pagePhase = config.phase;
			var checkMoved = function () {
				source.invalidateContext();
				source.getContext().then(function (context) {
					if (pageTurn === undefined || (context.game.turn === pageTurn && context.game.phase === pagePhase)) return;
					var phaseKey = context.game.turn + '|' + context.game.phase;
					// game.json has the turn as a date; the context only has the number
					source.getFile('game').then(function (game) {
						refreshForNewPhase(game.phase === 'Finished' ? 'The game has finished.'
							: 'The game has been processed: it is now ' + game.turnText + ', ' + game.phase + '.', phaseKey);
					}, function () {
						refreshForNewPhase('The game has been processed.', phaseKey);
					});
				}).catch(function () { /* nothing to show */ });
			};
			live.on('files', function (changed) {
				if (changed.indexOf('game') !== -1) checkMoved();
			});
			live.on('processed', function () { checkMoved(); });
			live.on('gap', function () { checkMoved(); });
			loads.push(source.getContext());
		}

		// Connect once the page has what it is going to show, so the server can be told what that is
		Promise.all(loads).then(function () { live.start(); }, function () { live.start(); });

		return page;
	}

	return { init: init };
})();
