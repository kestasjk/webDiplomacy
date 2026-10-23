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
 * panel (wD.Chat) if the page has one, the canvas board (wD.CanvasBoard) if the user has chosen it, and otherwise
 * toasts telling the user when the game moves on under the map image and drop-down order form.
 *
 *   wD.Board.init({ gameID, sbToken, turn, phase, chat: {...} | null, canvas: {...} | null });
 */
var wD = window.wD || {};
window.wD = wD;

wD.Board = (function () {
	'use strict';

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
		var page = { source: source, live: live, chat: chat, toast: toast, canvas: null };
		wD.page = page;

		var loads = [];
		if (chat) {
			chat.start();
			loads.push(source.getContext());
		}

		if (canvasContainer && config.canvas) {
			loads.push(wD.CanvasBoard.create(canvasContainer, { source: source, live: live, toast: toast, config: config.canvas }).then(function (board) {
				page.canvas = board;
			}).catch(function () { /* shown in the board */ }));
		} else {
			// The map image and the drop-down form only change with a page load: say when one is needed
			var pageTurn = config.turn;
			var pagePhase = config.phase;
			var offerRefresh = function (text) {
				toast.show({
					text: text, key: 'processed', timeout: 0, kind: 'success',
					actions: [{ label: 'Refresh', onClick: function () { window.location.reload(); } }]
				});
			};
			var checkMoved = function () {
				source.invalidateContext();
				source.getContext().then(function (context) {
					if (pageTurn === undefined || (context.game.turn === pageTurn && context.game.phase === pagePhase)) return;
					// game.json has the turn as a date; the context only has the number
					source.getFile('game').then(function (game) {
						offerRefresh('The game has been processed: it is now ' + game.turnText + ', ' + game.phase + '. Refresh the board to see the new phase.');
					}, function () {
						offerRefresh('The game has been processed. Refresh the board to see the new phase.');
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
