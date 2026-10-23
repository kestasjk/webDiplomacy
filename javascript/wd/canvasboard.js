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
 * wD.CanvasBoard: the "Legacy point and click" board on board.php. It looks like the board with the map image did:
 * the small map at its own size with the same history icons beneath it, stepping through the game a turn at a time
 * as map.php drew it, the large map opening over the page, and the Save and Ready buttons under the icons; only the
 * drop-down order form is gone, orders being given by clicking units and territories on the map (wD.OrderEntry).
 * It reads the game through wD.GameSource and keeps up to date through wD.Live, as the /game/ board does.
 *
 *   wD.CanvasBoard.create(document.getElementById('wdCanvasBoard'), { source, live, toast, config });
 *
 * config: { memberID, countryID, isMember, siteRoot, turnText: function(turn) -> "Spring, 1901", analyticsEventName }
 *
 * What the latest map shows is what map.php showed: during Diplomacy the previous turn's moves, during Retreats and
 * Builds the moves of this turn so far. Once the player has entered an order the map shows their orders instead, as
 * map.php's Preview did; the Preview icon switches between the two.
 */
var wD = window.wD || {};
window.wD = wD;

wD.CanvasBoard = (function () {
	'use strict';

	var ORDER_PHASES = ['Diplomacy', 'Retreats', 'Builds'];
	var ICONS = 'images/historyicons/';

	function el(tag, className, text) {
		var node = document.createElement(tag);
		if (className) node.className = className;
		if (text !== undefined && text !== null) node.textContent = text;
		return node;
	}

	function CanvasBoard(container, options) {
		this.container = container;
		this.source = options.source;
		this.live = options.live || null;
		this.toast = options.toast || null;
		this.config = options.config || {};
		this.siteRoot = this.config.siteRoot || '';
		this.turnText = (typeof this.config.turnText === 'function') ? this.config.turnText
			: function (turn) { return turn === -1 ? 'Pre-game' : 'Turn #' + turn; };

		this.context = null;
		this.game = null;
		this.history = null;
		this.variant = null;
		this.maps = { small: null, large: null };
		this.activeMap = null;
		this.controls = [];
		this.overlay = null;
		this.flyout = null;

		this.viewTurn = null;
		this.previewFlip = false;

		this.entry = null;
		this.legal = null;
		this.orderContext = null;
		this.orderStatus = [];
		this.saving = null;
		this.reloading = null;
		this.serverNotice = '';

		this.build();
	}

	CanvasBoard.create = function (container, options) {
		var board = new CanvasBoard(container, options);
		return board.start().then(function () { return board; });
	};

	/* Page */

	/** The map's place on the page, laid out as panelGameBoard::mapHTML() laid the map image out */
	CanvasBoard.prototype.build = function () {
		var self = this;
		var root = this.container;
		root.innerHTML = '';
		root.classList.add('wdcb');

		var store = el('div');
		store.id = 'mapstore';
		root.appendChild(store);

		this.mapHolder = el('div', 'wdcb-map');
		store.appendChild(this.mapHolder);
		this.loadingEl = el('div', 'wdcb-loading', 'Loading the map...');
		this.mapHolder.appendChild(this.loadingEl);

		this.controls.push(this.buildControls(store, true));

		document.addEventListener('keydown', function (e) { self.onKey(e); });
		window.addEventListener('beforeunload', function (e) {
			if (self.entry && self.entry.hasUnsaved()) {
				var text = 'You seem to have unsubmitted orders.';
				e.returnValue = text;
				return text;
			}
			return undefined;
		});
	};

	/**
	 * The history icons, the turn being viewed, and the Save and Ready buttons: under the map on the page, and again
	 * in the bar at the foot of the large map. Only the page's own set carries the ids the old page used.
	 *
	 * @param {boolean} main Whether this is the page's set
	 */
	CanvasBoard.prototype.buildControls = function (parent, main) {
		var self = this;
		var set = { main: main, icons: {} };
		var memberID = this.config.memberID;

		var nav = el('p', 'lightgrey');
		nav.style.textAlign = 'center';
		parent.appendChild(nav);

		var icon = function (key, alt, title, onClick) {
			var link = el('a', 'mapnav');
			link.href = '#';
			var image = el('img');
			image.alt = alt;
			image.title = title;
			if (main && key !== 'LargeMap') image.id = key;
			link.appendChild(image);
			link.addEventListener('click', function (e) {
				e.preventDefault();
				onClick();
			});
			nav.appendChild(link);
			nav.appendChild(document.createTextNode(' '));
			set.icons[key] = { link: link, image: image };
			return link;
		};

		icon('Start', 'Start', 'View the map from the first turn', function () { self.navigate('first'); });
		icon('Backward', 'Backward', 'View the map from the previous turn', function () { self.navigate('back'); });
		var large = icon('LargeMap', 'Open large map', main ? 'Open the large map over the page. The large map shows all the moves, and is useful when the small map isn\'t clear enough.' : 'Close the large map',
			function () { if (main) self.openLargeMap(); else self.closeLargeMap(); });
		if (main) large.id = 'LargeMapLink';
		set.icons.LargeMap.image.src = this.siteRoot + ICONS + 'external.png';
		icon('Forward', 'Forward', 'View the map from the next turn', function () { self.navigate('forward'); });
		icon('End', 'End', 'View the map from the most recent turn', function () { self.navigate('last'); });
		icon('Preview', 'PreviewMoves', 'Switch the map between the last moves and the orders you are entering', function () { self.togglePreview(); });
		set.icons.Preview.image.src = this.siteRoot + ICONS + 'Preview.png';

		set.history = el('p', 'lightgrey');
		if (main) set.history.id = 'History';
		set.history.style.display = 'none';
		parent.appendChild(set.history);

		set.orders = el('div', 'wdcb-orders');
		set.orders.style.textAlign = 'center';
		parent.appendChild(set.orders);
		set.notice = el('span', 'wdcb-notice');
		if (main) set.notice.id = 'ordersNoticeArea' + memberID;
		set.orders.appendChild(set.notice);
		set.orders.appendChild(el('br'));
		set.save = el('input', 'form-submit spaced-button');
		set.save.type = 'button';
		set.save.value = 'Save';
		set.save.disabled = true;
		if (main) set.save.id = 'UpdateButton' + memberID;
		set.save.addEventListener('click', function () { self.save(false); });
		set.orders.appendChild(set.save);
		set.ready = el('input', 'form-submit spaced-button');
		set.ready.type = 'button';
		set.ready.value = 'Ready';
		set.ready.disabled = true;
		if (main) set.ready.id = 'FinalizeButton' + memberID;
		set.ready.addEventListener('click', function () { self.save(true); });
		set.orders.appendChild(set.ready);

		return set;
	};

	/* Loading */

	CanvasBoard.prototype.start = function () {
		var self = this;
		return this.source.getContext().then(function (context) {
			self.context = context;
			return self.source.getFiles(['variant', 'game', 'history']);
		}).then(function (files) {
			self.variant = new wD.VariantMap(files.variant);
			self.game = files.game;
			self.history = files.history;
			self.viewTurn = self.latestTurn();

			var map = new wD.MapCanvas(self.mapHolder, self.variant, { size: 'small', siteRoot: self.siteRoot });
			map.onClick = function (hit, e, from) { self.onMapClick(hit, from); };
			self.maps.small = map;
			self.activeMap = map;
			return map.load();
		}).then(function () {
			if (self.loadingEl.parentNode) self.loadingEl.parentNode.removeChild(self.loadingEl);
			self.setupOrderEntry();
			self.render();
			self.listen();
		}).catch(function (error) {
			self.loadingEl.textContent = 'The map could not be loaded: ' + (error && error.message ? error.message : error);
			if (window.console) console.error(error);
			throw error;
		});
	};

	/* Turns */

	/** The turn the latest map shows: as map.php, the previous turn while this one's moves are still being entered */
	CanvasBoard.prototype.latestTurn = function () {
		var phase = this.game.phase;
		return (phase === 'Diplomacy' || phase === 'Pre-game') ? this.game.turn - 1 : this.game.turn;
	};

	CanvasBoard.prototype.isLatestView = function () {
		return this.viewTurn >= this.latestTurn();
	};

	CanvasBoard.prototype.phasesOfTurn = function (turn) {
		var phases = (this.history && this.history.phases) ? this.history.phases : [];
		return phases.filter(function (phase) { return phase.turn === turn; });
	};

	/** Every archived order of a turn, from all its phases */
	CanvasBoard.prototype.movesOfTurn = function (turn) {
		var moves = [];
		this.phasesOfTurn(turn).forEach(function (phase) {
			(phase.orders || []).forEach(function (order) { moves.push(order); });
		});
		return moves;
	};

	CanvasBoard.prototype.ownCountryIDs = function () {
		if (!this.context || !this.context.member) return [];
		if (this.game && this.game.isSandbox) return this.game.members.map(function (m) { return m.countryID; });
		return [this.context.member.countryID];
	};

	CanvasBoard.prototype.canEnterOrders = function () {
		var ctx = this.context;
		return !!(ctx && ctx.member && ctx.member.countryID > 0 && ctx.member.status === 'Playing' && ctx.orders &&
			ORDER_PHASES.indexOf(this.game.phase) !== -1 && ctx.game.turn === this.game.turn && ctx.game.phase === this.game.phase);
	};

	/** The live board as a unit map and province statuses, from game.json */
	CanvasBoard.prototype.liveBoard = function () {
		var units = {};
		// A unit is only dislodged while the retreats are being entered; a territory can still name the unit which
		// retreated from it in the Builds phase
		var retreats = (this.game.phase === 'Retreats');
		(this.game.units || []).forEach(function (unit) {
			units[unit.id] = { id: unit.id, type: unit.type, countryID: unit.countryID, terrID: unit.terrID, retreating: retreats && !!unit.retreating };
		});
		var provinceStatus = {};
		var ownership = {};
		var standoffs = [];
		(this.game.territories || []).forEach(function (territory) {
			provinceStatus[territory.terrID] = territory;
			if (territory.ownerCountryID) ownership[territory.terrID] = territory.ownerCountryID;
			if (territory.standoff) standoffs.push(territory.terrID);
		});
		return { units: units, provinceStatus: provinceStatus, ownership: ownership, standoffs: standoffs };
	};

	CanvasBoard.prototype.setupOrderEntry = function () {
		this.entry = null;
		this.legal = null;
		this.orderContext = null;
		this.orderStatus = [];
		if (!this.canEnterOrders()) return;

		var board = this.liveBoard();
		var ownCountryIDs = this.ownCountryIDs();
		this.legal = wD.LegalOrders.compute({
			variant: this.variant,
			phase: this.game.phase,
			units: board.units,
			provinceStatus: board.provinceStatus,
			members: this.game.members,
			ownCountryIDs: ownCountryIDs,
			isSandbox: this.game.isSandbox
		});
		this.entry = new wD.OrderEntry({
			variant: this.variant,
			phase: this.game.phase,
			units: board.units,
			ownCountryIDs: ownCountryIDs,
			members: this.game.members,
			isSandbox: this.game.isSandbox,
			orders: this.context.orders.orders,
			legal: this.legal
		});
		this.orderContext = { context: this.context.orders.context, contextKey: this.context.orders.contextKey };
		this.orderStatus = this.context.member.orderStatus.slice();
	};

	/* Scenes */

	/**
	 * Whether the latest map shows the player's own orders rather than the last moves: once they have entered or
	 * saved any this phase (new orders come from the server as Holds, so what has been saved is the only sign)
	 */
	CanvasBoard.prototype.showingOrders = function () {
		if (!this.entry || !this.entry.orders.length) return false;
		var status = this.orderStatus || [];
		var touched = this.entry.hasUnsaved() || ['Saved', 'Completed', 'Ready'].some(function (s) { return status.indexOf(s) !== -1; });
		return touched !== this.previewFlip;
	};

	/** What a map is to draw for the turn being viewed, as map.php would draw that turn */
	CanvasBoard.prototype.sceneFor = function (turn, isLarge) {
		var self = this;
		var scene = { ownership: {}, standoffs: [], ops: [], highlights: {} };
		var phases = this.phasesOfTurn(turn);
		var allPhases = (this.history && this.history.phases) ? this.history.phases : [];

		if (turn >= this.latestTurn()) {
			var board = this.liveBoard();
			scene.ownership = board.ownership;
			if (!Object.keys(scene.ownership).length) scene.ownership = this.homeOwnership();
			// map.php draws standoffs during Retreats, and always on the large map
			if (this.game.phase === 'Retreats' || isLarge) scene.standoffs = board.standoffs;

			var units = [];
			var dislodged = [];
			Object.keys(board.units).forEach(function (id) {
				var unit = board.units[id];
				if (unit.retreating) dislodged.push({ op: 'dislodgedUnit', terrID: unit.terrID, type: unit.type, countryID: unit.countryID });
				else units.push({ op: 'unit', terrID: unit.terrID, type: unit.type, countryID: unit.countryID });
			});

			var ops;
			if (this.showingOrders()) {
				ops = wD.MapCanvas.previewOps(this.entry.pendingOrders());
			} else {
				ops = wD.MapCanvas.composeMoves(this.variant, this.movesOfTurn(turn), { units: false });
				// The dislodged units themselves are drawn, so their bare markers aren't needed
				if (dislodged.length) ops = ops.filter(function (op) { return op.op !== 'dislodged'; });
			}
			scene.ops = units.concat(ops, dislodged);
			if (this.entry && this.isLatestView()) scene.highlights = this.entry.highlights();

			var caption = this.gameOverCaption();
			if (caption) scene.ops.push({ op: 'caption', text: caption });
			return scene;
		}

		if (turn < 0 || !phases.length) {
			// Before the first move: the starting units and home centres
			var first = allPhases[0];
			if (first) {
				(first.centers || []).forEach(function (center) { if (center.countryID) scene.ownership[center.terrID] = center.countryID; });
				scene.ops = (first.units || []).map(function (unit) {
					return { op: 'unit', terrID: unit.terrID, type: unit.type, countryID: unit.countryID };
				});
			}
			if (!Object.keys(scene.ownership).length) scene.ownership = this.homeOwnership();
			return scene;
		}

		var last = phases[phases.length - 1];
		(last.centers || []).forEach(function (center) { if (center.countryID) scene.ownership[center.terrID] = center.countryID; });
		scene.ops = wD.MapCanvas.composeMoves(self.variant, self.movesOfTurn(turn), { units: true });
		return scene;
	};

	/** The variant's home supply centres, for a board which has no ownership yet */
	CanvasBoard.prototype.homeOwnership = function () {
		var self = this;
		var ownership = {};
		this.variant.territoryIDs().forEach(function (terrID) {
			var territory = self.variant.territory(terrID);
			if (territory.homeCountryID && territory.coast !== 'Child' && territory.type !== 'Sea') ownership[terrID] = territory.homeCountryID;
		});
		return ownership;
	};

	CanvasBoard.prototype.gameOverCaption = function () {
		if (!this.game || this.game.phase !== 'Finished') return null;
		var self = this;
		if (this.game.gameOver === 'Drawn') return 'Game drawn';
		var winner = (this.game.members || []).filter(function (m) { return m.status === 'Won'; })[0];
		if (winner) return 'Game won by ' + self.variant.countryName(winner.countryID);
		return 'Game over';
	};

	/* Rendering */

	CanvasBoard.prototype.render = function () {
		this.renderMap();
		this.renderBar();
	};

	CanvasBoard.prototype.renderMap = function () {
		var self = this;
		['small', 'large'].forEach(function (size) {
			var map = self.maps[size];
			if (map && map.loaded) map.setScene(self.sceneFor(self.viewTurn, size === 'large'));
		});
		this.renderFlyout();
	};

	CanvasBoard.prototype.renderBar = function () {
		var self = this;
		var latest = this.isLatestView();
		var canBack = this.viewTurn > -1;
		var canForward = !latest;
		var entry = this.entry;
		var ready = (this.orderStatus || []).indexOf('Ready') !== -1;
		var saving = !!this.saving;
		var hasOrders = !!(entry && entry.orders.length);

		this.controls.forEach(function (set) {
			var icons = set.icons;
			icons.Start.image.src = self.siteRoot + ICONS + 'Start' + (canBack ? '' : '_disabled') + '.png';
			icons.Backward.image.src = self.siteRoot + ICONS + 'Backward' + (canBack ? '' : '_disabled') + '.png';
			icons.Forward.image.src = self.siteRoot + ICONS + 'Forward' + (canForward ? '' : '_disabled') + '.png';
			icons.End.image.src = self.siteRoot + ICONS + 'End' + (canForward ? '' : '_disabled') + '.png';
			icons.Preview.link.style.display = hasOrders ? '' : 'none';
			icons.Preview.link.classList.toggle('wdcb-preview-on', hasOrders && self.showingOrders());

			if (latest) {
				set.history.style.display = 'none';
			} else {
				set.history.textContent = self.turnText(self.viewTurn);
				set.history.style.display = '';
			}

			if (!entry) {
				set.orders.style.display = 'none';
				return;
			}
			set.orders.style.display = '';
			if (!hasOrders) {
				set.notice.innerHTML = 'No orders to enter this phase';
				set.save.style.display = 'none';
				set.ready.style.display = 'none';
				return;
			}
			set.save.style.display = '';
			set.ready.style.display = '';
			set.notice.innerHTML = self.noticeHTML();
			set.save.disabled = saving || ready || !entry.hasUnsaved();
			set.ready.value = ready ? 'Not ready' : 'Ready';
			set.ready.disabled = saving || (!ready && !entry.allEntered());
		});
	};

	/** What the notice area under the buttons says: that a save is under way, or what the server said of the last */
	CanvasBoard.prototype.noticeHTML = function () {
		if (this.saving) return this.saving === 'ready' ? 'Readying...' : (this.saving === 'notready' ? 'Unreadying...' : 'Saving...');
		return this.serverNotice || '';
	};

	CanvasBoard.prototype.setServerNotice = function (html) {
		this.serverNotice = html || '';
	};

	/* Order entry */

	CanvasBoard.prototype.onMapClick = function (hit, map) {
		var self = this;
		if (map) this.activeMap = map;
		if (!hit) { this.hideFlyout(); return; }

		if (!this.entry) {
			if (this.context && this.context.member && this.isLatestView() && ORDER_PHASES.indexOf(this.game.phase) !== -1 && this.toast) {
				this.toast.show({ text: 'Orders can\'t be entered here: reload the board.', key: 'orders', timeout: 5000 });
			}
			return;
		}
		if (!this.isLatestView()) {
			if (this.toast) this.toast.show({
				text: 'You are viewing a past turn. Go to the current turn to enter orders.',
				key: 'orders', timeout: 6000,
				actions: [{ label: 'Current turn', onClick: function () { self.navigate('last'); } }]
			});
			return;
		}
		if ((this.orderStatus || []).indexOf('Ready') !== -1) {
			if (this.toast) this.toast.show({ text: 'Unready your orders to change them.', key: 'orders', timeout: 5000 });
			return;
		}
		if (this.saving) return;

		var result = this.entry.click(hit);
		this.afterEntry(result);
	};

	CanvasBoard.prototype.afterEntry = function (result) {
		if (result.result === 'invalid' && result.terrID) this.flashInvalid(result.terrID);
		if (result.result === 'changed') this.setServerNotice('');
		this.render();
	};

	CanvasBoard.prototype.flashInvalid = function (terrID) {
		var self = this;
		var highlights = this.entry ? this.entry.highlights() : {};
		highlights[terrID] = 'invalid';
		['small', 'large'].forEach(function (size) {
			if (self.maps[size] && self.maps[size].loaded) self.maps[size].setScene({ highlights: highlights });
		});
		setTimeout(function () { self.renderMap(); }, 350);
	};

	/** The Hold / Move / Support / Convoy buttons by the selected unit, or the Army / Fleet choice for a build */
	CanvasBoard.prototype.renderFlyout = function () {
		var self = this;
		this.hideFlyout();
		if (!this.entry || !this.entry.current || !this.isLatestView()) return;
		var map = this.activeMap || this.maps.small;
		if (!map || !map.loaded) return;
		var current = this.entry.current;
		var options;
		var at;

		if (current.type === 'Build') {
			options = current.options.map(function (option) {
				return { label: option.label, onClick: function () { self.afterEntry(self.entry.chooseBuild(option.type, option.terrID)); } };
			});
			at = map.position(current.provinceID);
		} else if (!current.type) {
			options = this.entry.flyoutOptions().map(function (option) {
				return { label: option.label, shortcut: option.shortcut, onClick: function () { self.afterEntry(self.entry.chooseType(option.key)); } };
			});
			var unit = this.entry.units[current.unitID];
			at = map.position(unit.terrID);
		} else {
			return;
		}

		var flyout = el('div', 'wdcb-flyout');
		options.forEach(function (option) {
			var button = el('button', 'wdcb-flyout-btn', option.label);
			button.type = 'button';
			if (option.shortcut) button.title = 'Shortcut: ' + option.shortcut;
			button.addEventListener('click', function (e) { e.stopPropagation(); option.onClick(); });
			flyout.appendChild(button);
		});
		var cancel = el('button', 'wdcb-flyout-btn wdcb-flyout-cancel', '×');
		cancel.type = 'button';
		cancel.title = 'Cancel (Esc)';
		cancel.addEventListener('click', function (e) { e.stopPropagation(); self.afterEntry(self.entry.cancel()); });
		flyout.appendChild(cancel);

		var local = map.mapToLocal(at.x, at.y);
		flyout.style.left = local.x + 'px';
		flyout.style.top = local.y + 'px';
		map.overlay.appendChild(flyout);
		this.flyout = flyout;
	};

	CanvasBoard.prototype.hideFlyout = function () {
		if (this.flyout && this.flyout.parentNode) this.flyout.parentNode.removeChild(this.flyout);
		this.flyout = null;
	};

	CanvasBoard.prototype.onKey = function (e) {
		if (e.target && ['INPUT', 'TEXTAREA', 'SELECT'].indexOf(e.target.tagName) !== -1) return;
		if (e.key === 'Escape') {
			if (this.entry && this.entry.current) {
				e.preventDefault();
				this.afterEntry(this.entry.cancel());
			} else if (this.overlay) {
				e.preventDefault();
				this.closeLargeMap();
			}
			return;
		}
		if (e.shiftKey && (e.key === 'ArrowLeft' || e.key === 'ArrowRight')) {
			e.preventDefault();
			this.navigate(e.key === 'ArrowLeft' ? 'back' : 'forward');
			return;
		}
		if (this.entry && this.entry.current && !this.entry.current.type) {
			var key = e.key.toLowerCase();
			var option = this.entry.flyoutOptions().filter(function (o) { return o.shortcut === key; })[0];
			if (option) {
				e.preventDefault();
				this.afterEntry(this.entry.chooseType(option.key));
			}
		}
	};

	/* Navigation */

	CanvasBoard.prototype.navigate = function (key) {
		var last = this.latestTurn();
		var turn = this.viewTurn;
		if (key === 'first') turn = -1;
		else if (key === 'back') turn -= 1;
		else if (key === 'forward') turn += 1;
		else if (key === 'last') turn = last;
		turn = Math.max(-1, Math.min(last, turn));
		if (turn === this.viewTurn) return;
		this.viewTurn = turn;
		if (this.entry && this.entry.current) this.entry.cancel();
		this.render();
	};

	CanvasBoard.prototype.togglePreview = function () {
		this.previewFlip = !this.previewFlip;
		if (!this.isLatestView()) this.viewTurn = this.latestTurn();
		this.render();
	};

	/* The large map */

	/** The large map at its own size, over the page, with the history icons and the buttons in a bar at the foot */
	CanvasBoard.prototype.openLargeMap = function () {
		var self = this;
		if (this.overlay) { this.closeLargeMap(); return; }

		var overlay = el('div', 'wdcb-overlay');
		overlay.addEventListener('click', function (e) {
			if (e.target === overlay) self.closeLargeMap();
		});
		var holder = el('div', 'wdcb-overlay-map');
		var loading = el('div', 'wdcb-loading', 'Loading the large map...');
		holder.appendChild(loading);
		overlay.appendChild(holder);

		var bar = el('div', 'wdcb-overlay-bar');
		var close = el('a', 'wdcb-overlay-close', '×');
		close.href = '#';
		close.title = 'Close the large map (Esc)';
		close.addEventListener('click', function (e) { e.preventDefault(); self.closeLargeMap(); });
		bar.appendChild(close);
		var set = this.buildControls(bar, false);
		this.controls.push(set);
		overlay.appendChild(bar);

		document.body.appendChild(overlay);
		this.overlay = overlay;
		this.bodyOverflow = document.body.style.overflow;
		document.body.style.overflow = 'hidden';

		var map = new wD.MapCanvas(holder, this.variant, { size: 'large', siteRoot: this.siteRoot });
		map.onClick = function (hit, e, from) { self.onMapClick(hit, from); };
		this.maps.large = map;
		this.renderBar();
		map.load().then(function () {
			if (loading.parentNode) loading.parentNode.removeChild(loading);
			self.activeMap = map;
			self.renderMap();
		}).catch(function (error) {
			loading.textContent = 'The large map could not be loaded: ' + (error && error.message ? error.message : error);
		});
	};

	CanvasBoard.prototype.closeLargeMap = function () {
		if (!this.overlay) return;
		this.hideFlyout();
		if (this.maps.large) this.maps.large.destroy();
		this.maps.large = null;
		this.activeMap = this.maps.small;
		this.controls = this.controls.filter(function (set) { return set.main; });
		if (this.overlay.parentNode) this.overlay.parentNode.removeChild(this.overlay);
		this.overlay = null;
		document.body.style.overflow = this.bodyOverflow || '';
		this.renderFlyout();
	};

	/* Saving */

	CanvasBoard.prototype.save = function (toggleReady) {
		var self = this;
		if (!this.entry || this.saving || !this.orderContext) return;
		var ready = (this.orderStatus || []).indexOf('Ready') !== -1;
		if (this.entry.current) this.entry.cancel();

		var submission = {
			orderUpdates: this.entry.orderUpdates(),
			context: this.orderContext.context,
			contextKey: this.orderContext.contextKey
		};
		if (toggleReady) {
			if (ready) submission.notready = true;
			else submission.ready = true;
		}
		this.saving = toggleReady ? (ready ? 'notready' : 'ready') : 'save';
		this.setServerNotice('');
		this.renderBar();

		this.source.saveOrders(submission).then(function (result) {
			self.saving = null;
			var outcome = self.entry.applySaveResult(result);

			if (result.newContext && result.newContextKey) {
				self.orderContext = { context: JSON.stringify(result.newContext), contextKey: result.newContextKey };
				self.orderStatus = String(result.newContext.orderStatus || '').split(',').filter(function (s) { return s; });
				self.updateMemberStatus(result);
			}

			var html = '';
			if (result.notice) html += result.notice;
			outcome.notices.forEach(function (n) {
				var order = self.entry.orders.filter(function (o) { return o.id === n.orderID; })[0];
				var territory = order && order.terrID ? self.variant.territory(order.terrID) : null;
				var where = territory ? territory.name : 'Order ' + n.orderID;
				html += (html ? '<br />' : '') + '<strong>' + where + ':</strong> ' + n.notice;
			});
			self.setServerNotice(html);

			if (outcome.invalid && self.toast) self.toast.show({ text: 'Some orders were not accepted; see the notice under the map.', kind: 'error', key: 'save' });
			else if (toggleReady && self.toast) {
				var nowReady = (self.orderStatus || []).indexOf('Ready') !== -1;
				self.toast.show({ text: nowReady ? 'Orders readied.' : 'Orders unreadied.', kind: 'success', key: 'save', timeout: 4000 });
				if (nowReady && window.gtag && self.config.analyticsEventName) {
					try { window.gtag('event', self.config.analyticsEventName); } catch (e) { /* ignore */ }
				}
			}
			self.render();
		}).catch(function (error) {
			self.saving = null;
			self.setServerNotice('Could not save: ' + (error && error.message ? error.message : 'network error'));
			self.render();
		});
	};

	/** The member bar at the top of the page shows the order status too */
	CanvasBoard.prototype.updateMemberStatus = function (result) {
		var memberID = this.context.member.memberID;
		if (result.statusIcon) {
			Array.prototype.forEach.call(document.querySelectorAll('.member' + memberID + 'StatusIcon'), function (node) { node.innerHTML = result.statusIcon; });
		}
		if (result.statusText) {
			Array.prototype.forEach.call(document.querySelectorAll('.member' + memberID + 'StatusText'), function (node) { node.innerHTML = result.statusText; });
		}
	};

	/* Keeping up to date */

	CanvasBoard.prototype.listen = function () {
		var self = this;
		if (!this.live) return;
		this.live.on('files', function (changed) {
			if (changed.some(function (file) { return file === 'game' || file === 'status' || file === 'history'; })) self.reload();
		});
		this.live.on('processed', function () { self.reload(); });
		this.live.on('gap', function () { self.reload(); });
	};

	/**
	 * Fetch what changed and redraw. If the game moved on the latest map now shows the new turn, as a refresh of the
	 * old page would have, and a toast says so; the orders being entered belonged to the old phase and are gone.
	 */
	CanvasBoard.prototype.reload = function () {
		var self = this;
		if (this.reloading) return this.reloading;
		var before = this.game.turn + '|' + this.game.phase;
		var wasLatest = this.isLatestView();
		var hadUnsaved = this.entry && this.entry.hasUnsaved();

		this.reloading = this.source.getContext().then(function (context) {
			self.context = context;
			return self.source.getFiles(['game', 'history']);
		}).then(function (files) {
			self.game = files.game;
			self.history = files.history;
			var after = self.game.turn + '|' + self.game.phase;

			if (after !== before) {
				if (wasLatest) self.viewTurn = self.latestTurn();
				self.viewTurn = Math.min(self.viewTurn, self.latestTurn());
				self.hideFlyout();
				self.previewFlip = false;
				self.setServerNotice('');
				self.setupOrderEntry();
				if (self.toast) {
					var text = 'The game has been processed: it is now ' + (self.game.turnText || '') + ', ' + self.game.phase + '.';
					if (self.game.phase === 'Finished') text = 'The game has finished.';
					self.toast.show({
						text: text, key: 'processed', timeout: 0, kind: 'success',
						actions: [{ label: 'Refresh page', onClick: function () { window.location.reload(); } }]
					});
				}
			} else if (!hadUnsaved) {
				// Something else changed (a vote, a status, orders saved from another tab): take the server's orders
				self.setupOrderEntry();
			} else if (self.entry && self.context.orders) {
				// Keep what is being entered, but the token may have been renewed
				self.orderContext = { context: self.context.orders.context, contextKey: self.context.orders.contextKey };
				self.orderStatus = self.context.member.orderStatus.slice();
			}
			self.render();
		}).catch(function (error) {
			if (window.console) console.error('Board reload failed:', error);
		}).finally(function () {
			self.reloading = null;
		});
		return this.reloading;
	};

	return CanvasBoard;
})();
