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
 * wD.CanvasBoard: the "Legacy point and click" board on board.php. A wD.MapCanvas showing the game, the phases of
 * its history to step through, point-and-click order entry (wD.OrderEntry) and a bar beneath the map with the
 * phase, the history arrows, the map size and the Save / Ready buttons. It reads the game through wD.GameSource
 * and keeps up to date through wD.Live, exactly as the /game/ board does.
 *
 *   wD.CanvasBoard.create(document.getElementById('wdCanvasBoard'), { source, live, toast, config });
 *
 * config: { memberID, countryID, isMember, siteRoot }
 */
var wD = window.wD || {};
window.wD = wD;

wD.CanvasBoard = (function () {
	'use strict';

	var MAP_SIZE_KEY = 'wD.map.size';
	var ORDER_PHASES = ['Diplomacy', 'Retreats', 'Builds'];

	function el(tag, className, text) {
		var node = document.createElement(tag);
		if (className) node.className = className;
		if (text !== undefined && text !== null) node.textContent = text;
		return node;
	}

	function storage(key, value) {
		try {
			if (value === undefined) return window.localStorage.getItem(key);
			window.localStorage.setItem(key, value);
		} catch (e) { /* unavailable */ }
		return null;
	}

	function phaseKey(turn, phase) {
		return turn + '|' + phase;
	}

	function CanvasBoard(container, options) {
		this.container = container;
		this.source = options.source;
		this.live = options.live || null;
		this.toast = options.toast || null;
		this.config = options.config || {};
		this.siteRoot = this.config.siteRoot || '';

		this.mapSize = storage(MAP_SIZE_KEY) === 'large' ? 'large' : 'small';
		this.context = null;
		this.game = null;
		this.history = null;
		this.variant = null;
		this.map = null;
		this.entry = null;
		this.legal = null;
		this.phases = [];
		this.viewIndex = 0;
		this.orderContext = null;
		this.saving = null;
		this.reloading = null;

		this.build();
	}

	CanvasBoard.create = function (container, options) {
		var board = new CanvasBoard(container, options);
		return board.start().then(function () { return board; });
	};

	/* Page */

	CanvasBoard.prototype.build = function () {
		var self = this;
		var root = this.container;
		root.innerHTML = '';
		root.classList.add('wdcb');

		this.mapHolder = el('div', 'wdcb-map');
		root.appendChild(this.mapHolder);

		this.loadingEl = el('div', 'wdcb-loading', 'Loading the board…');
		this.mapHolder.appendChild(this.loadingEl);

		var bar = el('div', 'wdcb-bar');
		root.appendChild(bar);

		var nav = el('div', 'wdcb-nav');
		bar.appendChild(nav);
		this.navButtons = {};
		[['first', 'Start', 'View the map from the first turn'], ['back', 'Backward', 'View the previous phase']].forEach(function (spec) {
			nav.appendChild(self.navButton(spec[0], spec[1], spec[2]));
		});
		this.phaseEl = el('div', 'wdcb-phase');
		this.phaseDateEl = el('div', 'wdcb-phase-date');
		this.phaseNameEl = el('div', 'wdcb-phase-name');
		this.phaseEl.appendChild(this.phaseDateEl);
		this.phaseEl.appendChild(this.phaseNameEl);
		nav.appendChild(this.phaseEl);
		[['forward', 'Forward', 'View the next phase'], ['last', 'End', 'View the current phase']].forEach(function (spec) {
			nav.appendChild(self.navButton(spec[0], spec[1], spec[2]));
		});

		var tools = el('div', 'wdcb-tools');
		bar.appendChild(tools);
		var sizeLabel = el('label', 'wdcb-size-label', 'Map: ');
		this.sizeSelect = el('select', 'wdcb-size');
		[['small', 'Small'], ['large', 'Large']].forEach(function (spec) {
			var option = el('option', null, spec[1]);
			option.value = spec[0];
			if (spec[0] === self.mapSize) option.selected = true;
			self.sizeSelect.appendChild(option);
		});
		this.sizeSelect.addEventListener('change', function () { self.setMapSize(self.sizeSelect.value); });
		sizeLabel.appendChild(this.sizeSelect);
		tools.appendChild(sizeLabel);

		this.largeMapLink = el('a', 'wdcb-largemap light', 'Open large map');
		this.largeMapLink.target = '_blank';
		this.largeMapLink.rel = 'noopener';
		tools.appendChild(this.largeMapLink);

		this.ordersEl = el('div', 'wdcb-orders');
		bar.appendChild(this.ordersEl);
		this.statusEl = el('span', 'wdcb-status');
		this.ordersEl.appendChild(this.statusEl);
		this.saveButton = el('button', 'form-submit wdcb-save', 'Save');
		this.saveButton.type = 'button';
		this.saveButton.addEventListener('click', function () { self.save(false); });
		this.ordersEl.appendChild(this.saveButton);
		this.readyButton = el('button', 'form-submit wdcb-ready', 'Ready');
		this.readyButton.type = 'button';
		this.readyButton.addEventListener('click', function () { self.save(true); });
		this.ordersEl.appendChild(this.readyButton);

		this.noticeEl = el('div', 'wdcb-notice');
		root.appendChild(this.noticeEl);

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

	CanvasBoard.prototype.navButton = function (key, icon, title) {
		var self = this;
		var link = el('a', 'wdcb-navbtn mapnav');
		link.href = '#';
		link.title = title;
		var image = el('img');
		image.alt = icon;
		link.appendChild(image);
		link.addEventListener('click', function (e) {
			e.preventDefault();
			self.navigate(key);
		});
		this.navButtons[key] = { link: link, image: image, icon: icon };
		return link;
	};

	/* Loading */

	CanvasBoard.prototype.start = function () {
		var self = this;
		return this.source.getContext().then(function (context) {
			self.context = context;
			return self.source.getFiles(['variant', 'game', 'status', 'history']);
		}).then(function (files) {
			self.variant = new wD.VariantMap(files.variant);
			self.game = files.game;
			self.status = files.status;
			self.history = files.history;
			self.phases = self.buildPhases();
			self.viewIndex = self.phases.length - 1;

			self.map = new wD.MapCanvas(self.mapHolder, self.variant, { size: self.mapSize, siteRoot: self.siteRoot });
			self.map.onClick = function (hit, e) { self.onMapClick(hit, e); };
			return self.map.load();
		}).then(function () {
			if (self.loadingEl.parentNode) self.loadingEl.parentNode.removeChild(self.loadingEl);
			self.setupOrderEntry();
			self.render();
			self.listen();
		}).catch(function (error) {
			self.loadingEl.textContent = 'The board could not be loaded: ' + (error && error.message ? error.message : error);
			if (window.console) console.error(error);
			throw error;
		});
	};

	/** The phases to step through: history.json's, the last being the phase being played */
	CanvasBoard.prototype.buildPhases = function () {
		var phases = (this.history && this.history.phases) ? this.history.phases.slice() : [];
		if (!phases.length && this.game) {
			phases.push({ turn: this.game.turn, phase: this.game.phase, turnText: this.game.turnText, units: [], centers: [], orders: [] });
		}
		return phases;
	};

	CanvasBoard.prototype.isLatestView = function () {
		return this.viewIndex >= this.phases.length - 1;
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
		(this.game.units || []).forEach(function (unit) {
			units[unit.id] = { id: unit.id, type: unit.type, countryID: unit.countryID, terrID: unit.terrID, retreating: !!unit.retreating };
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

	/* Rendering */

	CanvasBoard.prototype.render = function () {
		this.renderMap();
		this.renderBar();
	};

	CanvasBoard.prototype.renderMap = function () {
		var self = this;
		var phase = this.phases[this.viewIndex];
		if (!phase || !this.map) return;
		var state;

		if (this.isLatestView() && this.game.phase !== 'Finished') {
			var board = this.liveBoard();
			var units = Object.keys(board.units).map(function (id) { return board.units[id]; });
			state = {
				ownership: board.ownership,
				units: units,
				standoffs: this.game.phase === 'Retreats' ? board.standoffs : [],
				orders: this.entry ? this.entry.pendingOrders() : [],
				highlights: this.entry ? this.entry.highlights() : {}
			};
		} else {
			var ownership = {};
			(phase.centers || []).forEach(function (center) { ownership[center.terrID] = center.countryID; });
			var historyUnits = (phase.units || []).map(function (unit, i) {
				return { id: 'h' + i, type: unit.type, countryID: unit.countryID, terrID: unit.terrID, retreating: !!unit.retreating };
			});
			var orders = (phase.orders || []).map(function (order) {
				return {
					type: order.type, countryID: order.countryID, unitType: order.unitType, terrID: order.terrID,
					toTerrID: order.toTerrID, fromTerrID: order.fromTerrID, viaConvoy: order.viaConvoy,
					success: order.success, dislodged: order.dislodged, pending: false
				};
			});
			state = { ownership: ownership, units: historyUnits, standoffs: [], orders: orders, highlights: {} };
		}
		this.map.setState(state);
		this.renderFlyout();
		this.largeMapLink.href = this.siteRoot + 'map.php?gameID=' + this.source.gameID + '&turn=' + self.mapTurnFor(phase) + '&mapType=large';
	};

	/** The turn map.php names a phase's map by: the turn whose results it shows */
	CanvasBoard.prototype.mapTurnFor = function (phase) {
		if (this.isLatestView()) return (this.game.phase === 'Diplomacy' || this.game.phase === 'Pre-game') ? this.game.turn - 1 : this.game.turn;
		return phase.turn;
	};

	CanvasBoard.prototype.renderBar = function () {
		var self = this;
		var phase = this.phases[this.viewIndex] || {};
		var latest = this.isLatestView();
		var canBack = this.viewIndex > 0;
		var canForward = this.viewIndex < this.phases.length - 1;

		Object.keys(this.navButtons).forEach(function (key) {
			var button = self.navButtons[key];
			var enabled = (key === 'first' || key === 'back') ? canBack : canForward;
			button.image.src = self.siteRoot + 'images/historyicons/' + button.icon + (enabled ? '' : '_disabled') + '.png';
			button.link.classList.toggle('wdcb-navbtn-disabled', !enabled);
		});

		this.phaseDateEl.textContent = phase.turnText || this.game.turnText || '';
		var name = phase.phase || this.game.phase || '';
		if (latest && this.game.phase === 'Finished') name = 'Finished';
		this.phaseNameEl.textContent = name + (latest ? '' : ' (history)');
		this.phaseEl.classList.toggle('wdcb-phase-history', !latest);

		if (!this.entry) {
			this.ordersEl.style.display = 'none';
			return;
		}
		this.ordersEl.style.display = '';

		var status = this.orderStatus || [];
		var ready = status.indexOf('Ready') !== -1;
		var saving = !!this.saving;
		var unsaved = this.entry.hasUnsaved();

		if (!this.entry.orders.length) {
			this.statusEl.textContent = 'No orders to enter this phase';
			this.saveButton.style.display = 'none';
			this.readyButton.style.display = 'none';
			return;
		}
		this.saveButton.style.display = '';
		this.readyButton.style.display = '';

		var statusText;
		if (saving) statusText = this.saving === 'ready' ? 'Readying…' : (this.saving === 'notready' ? 'Unreadying…' : 'Saving…');
		else if (ready) statusText = 'Ready';
		else if (unsaved) statusText = 'Unsaved changes';
		else if (status.indexOf('Completed') !== -1) statusText = 'Saved and complete';
		else if (status.indexOf('Saved') !== -1) statusText = 'Saved';
		else statusText = 'No orders saved yet';
		this.statusEl.textContent = statusText;
		this.statusEl.classList.toggle('wdcb-status-unsaved', unsaved && !saving);

		this.saveButton.disabled = saving || ready || !unsaved;
		this.saveButton.classList.toggle('wdcb-glow', !saving && !ready && unsaved);
		this.readyButton.textContent = ready ? 'Unready' : 'Ready';
		this.readyButton.disabled = saving || (!ready && !this.entry.allEntered());
	};

	CanvasBoard.prototype.notice = function (html, kind) {
		this.noticeEl.innerHTML = html || '';
		this.noticeEl.className = 'wdcb-notice' + (kind ? ' wdcb-notice-' + kind : '');
	};

	/* Order entry */

	CanvasBoard.prototype.onMapClick = function (hit) {
		var self = this;
		if (!hit) { this.hideFlyout(); return; }

		if (!this.entry) {
			if (this.context && this.context.member && this.isLatestView() && ORDER_PHASES.indexOf(this.game.phase) !== -1 && this.toast) {
				this.toast.show({ text: 'Orders can\'t be entered here: reload the board.', key: 'orders', timeout: 5000 });
			}
			return;
		}
		if (!this.isLatestView()) {
			if (this.toast) this.toast.show({
				text: 'You are viewing a past phase. Switch to the current phase to enter orders.',
				key: 'orders', timeout: 6000,
				actions: [{ label: 'Current phase', onClick: function () { self.navigate('last'); } }]
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
		this.renderMap();
		this.renderBar();
		if (result.result === 'changed' || result.result === 'cancelled') this.notice('');
	};

	CanvasBoard.prototype.flashInvalid = function (terrID) {
		var self = this;
		var highlights = this.entry ? this.entry.highlights() : {};
		highlights[terrID] = 'invalid';
		this.map.setState({ highlights: highlights });
		setTimeout(function () { self.renderMap(); }, 350);
	};

	/** The Hold / Move / Support / Convoy buttons by the selected unit, or the Army / Fleet choice for a build */
	CanvasBoard.prototype.renderFlyout = function () {
		var self = this;
		this.hideFlyout();
		if (!this.entry || !this.entry.current) return;
		var current = this.entry.current;
		var options;
		var at;

		if (current.type === 'Build') {
			options = current.options.map(function (option) {
				return { label: option.label, onClick: function () { self.afterEntry(self.entry.chooseBuild(option.type, option.terrID)); } };
			});
			at = this.map.position(current.provinceID);
		} else if (!current.type) {
			options = this.entry.flyoutOptions().map(function (option) {
				return { label: option.label, shortcut: option.shortcut, onClick: function () { self.afterEntry(self.entry.chooseType(option.key)); } };
			});
			var unit = this.entry.units[current.unitID];
			at = this.map.position(unit.terrID);
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

		var local = this.map.mapToLocal(at.x, at.y);
		flyout.style.left = local.x + 'px';
		flyout.style.top = local.y + 'px';
		this.map.overlay.appendChild(flyout);
		this.flyout = flyout;
	};

	CanvasBoard.prototype.hideFlyout = function () {
		if (this.flyout && this.flyout.parentNode) this.flyout.parentNode.removeChild(this.flyout);
		this.flyout = null;
	};

	CanvasBoard.prototype.onKey = function (e) {
		if (e.target && ['INPUT', 'TEXTAREA', 'SELECT'].indexOf(e.target.tagName) !== -1) return;
		if (e.key === 'Escape' && this.entry && this.entry.current) {
			e.preventDefault();
			this.afterEntry(this.entry.cancel());
			return;
		}
		if (e.shiftKey && (e.key === 'ArrowLeft' || e.key === 'ArrowRight')) {
			e.preventDefault();
			this.navigate(e.key === 'ArrowLeft' ? 'back' : 'forward');
			return;
		}
		if (this.entry && this.entry.current && !this.entry.current.type) {
			var option = this.entry.flyoutOptions().filter(function (o) { return o.shortcut === e.key.toLowerCase(); })[0];
			if (option) {
				e.preventDefault();
				this.afterEntry(this.entry.chooseType(option.key));
			}
		}
	};

	/* Navigation */

	CanvasBoard.prototype.navigate = function (key) {
		var last = this.phases.length - 1;
		var index = this.viewIndex;
		if (key === 'first') index = 0;
		else if (key === 'back') index -= 1;
		else if (key === 'forward') index += 1;
		else if (key === 'last') index = last;
		index = Math.max(0, Math.min(last, index));
		if (index === this.viewIndex) return;
		this.viewIndex = index;
		if (this.entry && this.entry.current) this.entry.cancel();
		this.render();
	};

	CanvasBoard.prototype.setMapSize = function (size) {
		var self = this;
		this.mapSize = size === 'large' ? 'large' : 'small';
		storage(MAP_SIZE_KEY, this.mapSize);
		this.hideFlyout();
		this.map.setSize(this.mapSize).then(function () {
			self.renderMap();
		}).catch(function (error) {
			if (self.toast) self.toast.show({ text: 'Could not load the ' + size + ' map: ' + error.message, kind: 'error' });
		});
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
		this.renderBar();
		this.notice('');

		this.source.saveOrders(submission).then(function (result) {
			self.saving = null;
			var outcome = self.entry.applySaveResult(result);

			if (result.newContext && result.newContextKey) {
				self.orderContext = { context: JSON.stringify(result.newContext), contextKey: result.newContextKey };
				self.orderStatus = String(result.newContext.orderStatus || '').split(',').filter(function (s) { return s; });
				self.updateMemberStatus(result);
			}

			var html = '';
			if (result.statusIcon && result.statusIcon.indexOf('<') !== -1) html += result.statusIcon + ' '; // '-' when there are no orders
			if (result.notice) html += result.notice;
			outcome.notices.forEach(function (n) {
				var order = self.entry.orders.filter(function (o) { return o.id === n.orderID; })[0];
				var where = order && order.terrID ? self.variant.territory(order.terrID).name : 'Order ' + n.orderID;
				html += (html ? '<br />' : '') + '<strong>' + where + ':</strong> ' + n.notice;
			});
			self.notice(html, outcome.invalid ? 'error' : (result.notice ? 'warning' : ''));

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
			self.notice('Could not save: ' + (error && error.message ? error.message : 'network error'), 'error');
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
	 * Fetch what changed and redraw. If the game moved on, the view stays on the phase it was showing, which now has
	 * its results, and a toast offers the new phase; the orders being entered belonged to the old phase and are gone.
	 */
	CanvasBoard.prototype.reload = function () {
		var self = this;
		if (this.reloading) return this.reloading;
		var before = phaseKey(this.game.turn, this.game.phase);
		var wasLatest = this.isLatestView();
		var hadUnsaved = this.entry && this.entry.hasUnsaved();

		this.reloading = this.source.getContext().then(function (context) {
			self.context = context;
			return self.source.getFiles(['game', 'status', 'history']);
		}).then(function (files) {
			self.game = files.game;
			self.status = files.status;
			self.history = files.history;
			var oldCount = self.phases.length;
			self.phases = self.buildPhases();
			var after = phaseKey(self.game.turn, self.game.phase);

			if (after !== before) {
				// Processed: show the results of the phase that was being played, and offer the new one
				if (wasLatest) self.viewIndex = Math.min(oldCount - 1, self.phases.length - 1);
				self.hideFlyout();
				self.setupOrderEntry();
				if (self.toast) {
					var text = 'Game processed: now ' + (self.game.turnText || '') + ' ' + self.game.phase;
					if (self.game.phase === 'Finished') text = 'The game has finished.';
					self.toast.show({
						text: text, key: 'processed', timeout: 0, kind: 'success',
						actions: [{ label: 'Show', onClick: function () { self.navigate('last'); } }]
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
