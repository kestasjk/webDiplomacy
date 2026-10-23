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
 * wD.OrderEntry: turning clicks on the map into orders, the way the /game/ board does it
 * (game-src/src/utils/state/gameApiSlice/reducers/processMapClick.ts): click a unit, pick Hold / Move / Support /
 * Convoy, then click what the order applies to. Builds and retreats take their clicks too. It holds the orders as
 * ajax.php wants them and knows nothing about the page; the board asks it what to show.
 *
 *   var entry = new wD.OrderEntry({ variant, phase, units, ownCountryIDs, members, isSandbox, orders, legal });
 *   var result = entry.click(hit);        // { result: 'flyout' | 'buildmenu' | 'changed' | 'cancelled' | 'invalid' | 'ignored', ... }
 *   entry.chooseType('Move');             // from the flyout
 *   entry.chooseBuild('Build Fleet', 7);  // from the build menu
 *   entry.orderUpdates();                 // for GameSource.saveOrders()
 *
 * orders are game/playercontext's orders[]: { id, countryID, unitID, unitType, terrID, type, toTerrID, fromTerrID,
 * viaConvoy }. legal is wD.LegalOrders.compute()'s result for the same board.
 */
var wD = window.wD || {};
window.wD = wD;

wD.OrderEntry = (function () {
	'use strict';

	function OrderEntry(options) {
		this.variant = options.variant;
		this.phase = options.phase;
		this.units = options.units || {};
		this.ownCountryIDs = options.ownCountryIDs || [];
		this.members = options.members || [];
		this.isSandbox = !!options.isSandbox;
		this.legal = options.legal;
		this.current = null;
		this.loadOrders(options.orders || []);
	}

	/* Orders as the server holds them */

	/**
	 * Take the orders from the server. A half-entered order the server kept (a Move with no destination, say) is
	 * shown as a Hold, as the /game/ board does; builds with nothing chosen are "Wait".
	 */
	OrderEntry.prototype.loadOrders = function (orders) {
		var self = this;
		this.orders = orders.map(function (order) {
			var type = order.type || '';
			var toTerrID = order.toTerrID;
			var fromTerrID = order.fromTerrID;
			if (((type === 'Move' || type === 'Support hold') && !toTerrID) ||
				((type === 'Support move' || type === 'Convoy') && (!toTerrID || !fromTerrID))) {
				type = 'Hold';
				toTerrID = null;
				fromTerrID = null;
			}
			return {
				id: order.id,
				countryID: order.countryID,
				unitID: order.unitID,
				unitType: order.unitType,
				terrID: order.terrID,
				type: type,
				toTerrID: toTerrID,
				fromTerrID: fromTerrID,
				viaConvoy: (order.viaConvoy === true || order.viaConvoy === 'Yes') ? 'Yes' : 'No'
			};
		});
		this.meta = {};
		this.orders.forEach(function (order) {
			var update;
			if (self.phase === 'Builds') {
				update = { type: order.toTerrID ? order.type : 'Wait', toTerrID: order.toTerrID };
			} else {
				update = { type: order.type, toTerrID: order.toTerrID, fromTerrID: order.fromTerrID, viaConvoy: order.viaConvoy };
			}
			self.meta[order.id] = { saved: true, update: update };
		});
		this.current = null;
	};

	OrderEntry.prototype.orderForUnit = function (unitID) {
		return this.orders.filter(function (order) { return order.unitID === unitID; })[0] || null;
	};

	OrderEntry.prototype.isOwn = function (unit) {
		return !!unit && this.ownCountryIDs.indexOf(unit.countryID) !== -1;
	};

	/** The unit in a province; in retreats the dislodged one when it is ours, as the attacker is also there */
	OrderEntry.prototype.unitInProvince = function (provinceID) {
		var self = this;
		var inProvince = Object.keys(this.units).map(function (id) { return self.units[id]; }).filter(function (unit) {
			return self.variant.provinceID(unit.terrID) === provinceID;
		});
		if (!inProvince.length) return null;
		if (inProvince.length === 1) return inProvince[0];
		var retreating = inProvince.filter(function (unit) { return unit.retreating && self.isOwn(unit) && self.orderForUnit(unit.id); })[0];
		return retreating || inProvince.filter(function (unit) { return !unit.retreating; })[0] || inProvince[0];
	};

	/* What the board shows */

	/** Every order as it stands, merged with what has been entered, for drawing; pending marks the unsaved ones */
	OrderEntry.prototype.pendingOrders = function () {
		var self = this;
		return this.orders.map(function (order) {
			var meta = self.meta[order.id];
			var update = meta ? meta.update : {};
			var merged = {
				type: update.type !== undefined ? update.type : order.type,
				countryID: order.countryID,
				unitType: order.unitType,
				terrID: order.terrID,
				toTerrID: update.toTerrID !== undefined ? update.toTerrID : order.toTerrID,
				fromTerrID: update.fromTerrID !== undefined ? update.fromTerrID : order.fromTerrID,
				viaConvoy: update.viaConvoy !== undefined ? update.viaConvoy : order.viaConvoy,
				success: null,
				pending: !(meta && meta.saved)
			};
			return merged;
		}).filter(function (order) { return order.type && order.type !== 'Wait' && order.type !== ''; });
	};

	/** Which territories a click would do something to now, for highlighting: { terrID: 'selected'|'target'|'source' } */
	OrderEntry.prototype.highlights = function () {
		var self = this;
		var marks = {};
		var mark = function (terrID, kind) { if (terrID) marks[terrID] = kind; };
		var markProvinceUnits = function (provinceIDs, kind) {
			provinceIDs.forEach(function (provinceID) {
				var unit = self.unitInProvince(parseInt(provinceID, 10));
				mark(unit ? unit.terrID : provinceID, kind);
			});
		};
		var legal = this.legal;
		var current = this.current;

		if (this.phase === 'Builds') {
			if (!current) {
				(legal.buildDests || []).forEach(function (terrID) { mark(self.variant.provinceID(terrID), 'target'); });
				Object.keys(legal.destroyUnits || {}).forEach(function (unitID) {
					var unit = self.units[unitID];
					if (unit) mark(unit.terrID, 'source');
				});
			} else if (current.unitID === null && current.provinceID) {
				mark(current.provinceID, 'selected');
			}
			return marks;
		}

		if (!current) return marks;
		var unit = this.units[current.unitID];
		if (unit) mark(unit.terrID, 'selected');

		if (this.phase === 'Retreats') {
			(legal.retreatDests[current.unitID] || []).forEach(function (terrID) { mark(self.variant.provinceID(terrID), 'target'); });
			return marks;
		}

		switch (current.type) {
			case 'Move':
				if (current.viaConvoy === 'Yes') {
					(legal.vias[current.unitID] || []).forEach(function (via) { mark(via.dest, 'target'); });
				} else {
					(legal.moveDests[current.unitID] || []).forEach(function (terrID) { mark(self.variant.provinceID(terrID), 'target'); });
				}
				break;
			case 'Support': {
				var supports = legal.supports[current.unitID] || {};
				if (!current.fromTerrID) {
					markProvinceUnits(Object.keys(supports), 'source');
				} else {
					mark(current.fromTerrID, 'source');
					(supports[current.fromTerrID] || []).forEach(function (support) { mark(support.dest, 'target'); });
				}
				break;
			}
			case 'Convoy': {
				var convoys = legal.convoys[current.unitID] || {};
				if (!current.fromTerrID) {
					markProvinceUnits(Object.keys(convoys), 'source');
				} else {
					mark(current.fromTerrID, 'source');
					Object.keys(convoys[current.fromTerrID] || {}).forEach(function (dest) { mark(dest, 'target'); });
				}
				break;
			}
			default:
				break;
		}
		return marks;
	};

	/** The order types the flyout offers for the selected unit */
	OrderEntry.prototype.flyoutOptions = function () {
		if (!this.current || this.current.type || this.phase !== 'Diplomacy') return [];
		var unit = this.units[this.current.unitID];
		if (!unit) return [];
		var options = [
			{ key: 'Hold', label: 'Hold', shortcut: 'h' },
			{ key: 'Move', label: 'Move', shortcut: 'm' },
			{ key: 'Support', label: 'Support', shortcut: 's' }
		];
		var convoys = this.legal.convoys[unit.id];
		if (unit.type === 'Fleet' && this.variant.isSea(unit.terrID) && convoys && Object.keys(convoys).length) {
			options.push({ key: 'Convoy', label: 'Convoy', shortcut: 'c' });
		}
		if (unit.type === 'Army' && (this.legal.vias[unit.id] || []).length) {
			options.push({ key: 'ViaConvoy', label: 'Via convoy', shortcut: 'v' });
		}
		return options;
	};

	/* Changing orders */

	OrderEntry.prototype.setUpdate = function (orderID, update) {
		var order = this.orders.filter(function (o) { return o.id === orderID; })[0];
		if (!order) return;
		if (update.type === 'Support') update.type = (update.fromTerrID === update.toTerrID) ? 'Support hold' : 'Support move';
		if (update.type === 'Support hold') update.fromTerrID = null;
		if (this.phase === 'Diplomacy' && !update.viaConvoy) update.viaConvoy = 'No';
		if (!update.convoyPath) delete update.convoyPath;
		this.meta[orderID] = { saved: false, update: update };
	};

	OrderEntry.prototype.commit = function (update) {
		var current = this.current;
		if (!current) return { result: 'ignored' };
		var merged = {
			type: current.type,
			toTerrID: current.toTerrID || null,
			fromTerrID: current.fromTerrID || null,
			viaConvoy: current.viaConvoy || 'No',
			convoyPath: current.convoyPath || null
		};
		Object.keys(update || {}).forEach(function (key) { merged[key] = update[key]; });
		this.setUpdate(current.orderID, merged);
		this.current = null;
		return { result: 'changed', orderID: current.orderID };
	};

	OrderEntry.prototype.cancel = function () {
		var had = !!this.current;
		this.current = null;
		return { result: had ? 'cancelled' : 'ignored' };
	};

	/** Pick the territory in a province a unit type can reach: the chosen coast, or the only reachable one */
	function chooseInProvince(variant, provinceID, preferredTerrID, allowed) {
		var candidates = variant.provinceTerritoryIDs(provinceID).filter(function (terrID) { return allowed.indexOf(terrID) !== -1; });
		if (!candidates.length) return null;
		if (candidates.indexOf(preferredTerrID) !== -1) return preferredTerrID;
		return candidates.length === 1 ? candidates[0] : candidates[0];
	}

	/**
	 * A click on the map.
	 * @param {{terrID, coastTerrID}} hit From wD.MapCanvas.hitTest(); terrID is a province or a sea
	 */
	OrderEntry.prototype.click = function (hit) {
		if (!hit) return this.current ? this.cancel() : { result: 'ignored' };
		var provinceID = this.variant.provinceID(hit.terrID);
		if (this.phase === 'Builds') return this.clickBuilds(provinceID, hit);
		if (this.phase === 'Retreats') return this.clickRetreats(provinceID, hit);
		if (this.phase === 'Diplomacy') return this.clickDiplomacy(provinceID, hit);
		return { result: 'ignored' };
	};

	OrderEntry.prototype.startOrder = function (unit, type) {
		var order = this.orderForUnit(unit.id);
		if (!order) return { result: 'invalid', terrID: unit.terrID };
		this.current = { orderID: order.id, unitID: unit.id, countryID: unit.countryID, type: type || null, fromTerrID: null, toTerrID: null, viaConvoy: 'No', convoyPath: null };
		return { result: type ? 'started' : 'flyout', unitID: unit.id, terrID: unit.terrID };
	};

	/** From the flyout: Hold, Move, Support, Convoy or ViaConvoy */
	OrderEntry.prototype.chooseType = function (key) {
		if (!this.current || this.phase !== 'Diplomacy') return { result: 'ignored' };
		var allowed = this.flyoutOptions().some(function (option) { return option.key === key; });
		if (!allowed) return { result: 'ignored' };
		if (key === 'Hold') {
			this.current.type = 'Hold';
			return this.commit({ toTerrID: null, fromTerrID: null, viaConvoy: 'No' });
		}
		if (key === 'ViaConvoy') {
			this.current.type = 'Move';
			this.current.viaConvoy = 'Yes';
		} else {
			this.current.type = key;
		}
		return { result: 'started', unitID: this.current.unitID };
	};

	OrderEntry.prototype.clickDiplomacy = function (provinceID, hit) {
		var self = this;
		var current = this.current;
		var clicked = this.unitInProvince(provinceID);

		if (!current) {
			if (clicked && this.isOwn(clicked)) return this.startOrder(clicked, null);
			return { result: 'invalid', terrID: provinceID };
		}
		if (!current.type || (clicked && clicked.id === current.unitID)) return this.cancel();

		var unit = this.units[current.unitID];
		var legal = this.legal;

		if (current.type === 'Move') {
			if (current.viaConvoy !== 'Yes') {
				var dest = chooseInProvince(this.variant, provinceID, hit.coastTerrID, legal.moveDests[unit.id] || []);
				if (dest === null) return { result: 'invalid', terrID: provinceID };
				return this.commit({ toTerrID: unit.type === 'Army' ? provinceID : dest, viaConvoy: 'No' });
			}
			var via = (legal.vias[unit.id] || []).filter(function (v) { return v.dest === provinceID; })[0];
			if (!via) return { result: 'invalid', terrID: provinceID };
			return this.commit({ toTerrID: provinceID, viaConvoy: 'Yes', convoyPath: via.paths[0] });
		}

		if (current.type === 'Support') {
			var supports = legal.supports[unit.id] || {};
			if (!current.fromTerrID) {
				if (clicked && supports[provinceID] && supports[provinceID].length) {
					current.fromTerrID = provinceID;
					return { result: 'changed', unitID: unit.id };
				}
				return { result: 'invalid', terrID: provinceID };
			}
			var support = (supports[current.fromTerrID] || []).filter(function (s) { return s.dest === provinceID; })[0];
			if (!support) return { result: 'invalid', terrID: provinceID };
			var fromTerrID = current.fromTerrID;
			var result = this.commit({ toTerrID: provinceID, fromTerrID: fromTerrID, convoyPath: support.convoyPath || null });
			// Supporting one of our own units into a province: give that unit the move too
			var supported = this.unitInProvince(fromTerrID);
			if (supported && this.isOwn(supported) && fromTerrID !== provinceID && this.orderForUnit(supported.id)) {
				var moveTo = supported.type === 'Army' ? provinceID
					: chooseInProvince(this.variant, provinceID, hit.coastTerrID, legal.moveDests[supported.id] || []);
				if (moveTo !== null) {
					this.setUpdate(this.orderForUnit(supported.id).id, {
						type: 'Move', toTerrID: moveTo, fromTerrID: null,
						viaConvoy: support.convoyPath ? 'Yes' : 'No', convoyPath: support.convoyPath || null
					});
				}
			}
			return result;
		}

		if (current.type === 'Convoy') {
			var convoys = legal.convoys[unit.id] || {};
			if (!current.fromTerrID) {
				if (clicked && clicked.type === 'Army' && convoys[provinceID]) {
					current.fromTerrID = provinceID;
					return { result: 'changed', unitID: unit.id };
				}
				return { result: 'invalid', terrID: provinceID };
			}
			var convoy = (convoys[current.fromTerrID] || {})[provinceID];
			if (!convoy) return { result: 'invalid', terrID: provinceID };
			var armyProvince = current.fromTerrID;
			var convoyResult = this.commit({ toTerrID: provinceID, fromTerrID: armyProvince, convoyPath: convoy.path });
			var army = this.unitInProvince(armyProvince);
			if (army && this.isOwn(army) && this.orderForUnit(army.id)) {
				this.setUpdate(this.orderForUnit(army.id).id, { type: 'Move', toTerrID: provinceID, fromTerrID: null, viaConvoy: 'Yes', convoyPath: convoy.path });
			}
			return convoyResult;
		}

		return self.cancel();
	};

	OrderEntry.prototype.clickRetreats = function (provinceID, hit) {
		var clicked = this.unitInProvince(provinceID);
		var current = this.current;
		if (!current) {
			if (clicked && this.isOwn(clicked) && this.orderForUnit(clicked.id) && this.legal.retreatDests[clicked.id] !== undefined) {
				return this.startOrder(clicked, 'Retreat');
			}
			return { result: 'invalid', terrID: provinceID };
		}
		if (clicked && clicked.id === current.unitID) {
			current.type = 'Disband';
			return this.commit({ toTerrID: null, fromTerrID: null });
		}
		var unit = this.units[current.unitID];
		var dest = chooseInProvince(this.variant, provinceID, hit.coastTerrID, this.legal.retreatDests[unit.id] || []);
		if (dest === null) return { result: 'invalid', terrID: provinceID };
		return this.commit({ type: 'Retreat', toTerrID: unit.type === 'Army' ? provinceID : dest, fromTerrID: null });
	};

	/** The member whose build or disband a click in a province would be: ours, or in a sandbox whoever's it is */
	OrderEntry.prototype.actingMember = function (provinceID, clickedUnit) {
		var self = this;
		var own = this.members.filter(function (m) { return self.ownCountryIDs.indexOf(m.countryID) !== -1; });
		if (!this.isSandbox) return own[0] || null;
		var territory = this.variant.territory(provinceID);
		var candidates = own.filter(function (m) {
			if (m.supplyCenterNo === m.unitNo) return false;
			if (clickedUnit && clickedUnit.countryID === m.countryID) return true;
			return territory && territory.homeCountryID === m.countryID;
		});
		return candidates[0] || null;
	};

	OrderEntry.prototype.availableBuildOrder = function (countryID) {
		var self = this;
		return this.orders.filter(function (order) {
			if (order.countryID !== countryID) return false;
			var meta = self.meta[order.id];
			return !meta || !meta.update.toTerrID || meta.update.type === 'Wait';
		})[0] || null;
	};

	OrderEntry.prototype.clickBuilds = function (provinceID, hit) {
		var self = this;
		this.current = null;

		// Clicking a province with a build or disband already placed takes it back
		var existing = Object.keys(this.meta).filter(function (orderID) {
			var update = self.meta[orderID].update;
			return update.toTerrID && self.variant.provinceID(update.toTerrID) === provinceID && update.type !== 'Wait';
		})[0];
		if (existing) {
			this.meta[existing] = { saved: false, update: { type: 'Wait', toTerrID: null } };
			return { result: 'changed', orderID: parseInt(existing, 10) };
		}

		var clicked = this.unitInProvince(provinceID);
		var member = this.actingMember(provinceID, clicked);
		if (!member) return { result: 'invalid', terrID: provinceID };
		var isBuild = member.supplyCenterNo > member.unitNo;
		var isDestroy = member.supplyCenterNo < member.unitNo;
		var order = this.availableBuildOrder(member.countryID);
		if (!order) return { result: 'invalid', terrID: provinceID };

		if (isDestroy) {
			if (!clicked || clicked.countryID !== member.countryID || !this.legal.destroyUnits[clicked.id]) return { result: 'invalid', terrID: provinceID };
			this.setUpdate(order.id, { type: 'Destroy', toTerrID: provinceID });
			return { result: 'changed', orderID: order.id };
		}
		if (!isBuild) return { result: 'invalid', terrID: provinceID };

		var dests = this.variant.provinceTerritoryIDs(provinceID).filter(function (terrID) { return self.legal.buildDests.indexOf(terrID) !== -1; });
		if (!dests.length) return { result: 'invalid', terrID: provinceID };

		var territory = this.variant.territory(provinceID);
		if (territory.type === 'Land') {
			this.setUpdate(order.id, { type: 'Build Army', toTerrID: provinceID });
			return { result: 'changed', orderID: order.id };
		}

		// A coastal province: army, or a fleet on one of its coasts
		var options = [{ type: 'Build Army', terrID: provinceID, label: 'Army' }];
		var coasts = this.variant.coastsOf(provinceID);
		if (coasts.length) {
			coasts.forEach(function (coastID) {
				if (dests.indexOf(coastID) !== -1) options.push({ type: 'Build Fleet', terrID: coastID, label: 'Fleet (' + self.variant.territory(coastID).name.replace(territory.name, '').trim() + ')' });
			});
		} else {
			options.push({ type: 'Build Fleet', terrID: provinceID, label: 'Fleet' });
		}
		this.current = { orderID: order.id, unitID: null, countryID: member.countryID, provinceID: provinceID, type: 'Build', options: options };
		return { result: 'buildmenu', orderID: order.id, terrID: provinceID, options: options };
	};

	/** From the build menu */
	OrderEntry.prototype.chooseBuild = function (type, terrID) {
		var current = this.current;
		if (!current || current.type !== 'Build') return { result: 'ignored' };
		var allowed = current.options.some(function (option) { return option.type === type && option.terrID === terrID; });
		if (!allowed) return { result: 'ignored' };
		this.setUpdate(current.orderID, { type: type, toTerrID: terrID });
		this.current = null;
		return { result: 'changed', orderID: current.orderID };
	};

	/* Saving */

	OrderEntry.prototype.hasUnsaved = function () {
		var self = this;
		return Object.keys(this.meta).some(function (orderID) { return !self.meta[orderID].saved; });
	};

	/** Whether every order has been given something the server could accept as complete */
	OrderEntry.prototype.allEntered = function () {
		var self = this;
		if (!this.orders.length) return false;
		return this.orders.every(function (order) {
			var update = self.meta[order.id].update;
			if (self.phase === 'Builds') return true; // Waiting is allowed
			if (!update.type || update.type === '') return false;
			if (['Hold', 'Disband', 'Destroy'].indexOf(update.type) !== -1) return true;
			if (update.type === 'Support move' || update.type === 'Convoy') return !!(update.toTerrID && update.fromTerrID);
			return !!update.toTerrID;
		});
	};

	/** The orders as ajax.php takes them */
	OrderEntry.prototype.orderUpdates = function () {
		var self = this;
		return this.orders.map(function (order) {
			var update = self.meta[order.id] ? self.meta[order.id].update : {};
			var data = {
				id: order.id,
				unitID: order.unitID,
				type: update.type !== undefined ? update.type : order.type,
				toTerrID: update.toTerrID !== undefined ? update.toTerrID : order.toTerrID,
				fromTerrID: update.fromTerrID !== undefined ? update.fromTerrID : order.fromTerrID,
				viaConvoy: update.viaConvoy !== undefined ? update.viaConvoy : order.viaConvoy
			};
			if (update.convoyPath && update.convoyPath.length) data.convoyPath = update.convoyPath;
			return data;
		});
	};

	/**
	 * Take in ajax.php's answer: what it accepted is now saved.
	 * @returns {{ invalid: boolean, notices: [{orderID, notice}] }}
	 */
	OrderEntry.prototype.applySaveResult = function (result) {
		var self = this;
		var notices = [];
		var orders = (result && result.orders) || {};
		Object.keys(orders).forEach(function (orderID) {
			var outcome = orders[orderID];
			var meta = self.meta[orderID];
			if (meta && outcome.status === 'Complete') meta.saved = true;
			if (outcome.notice) notices.push({ orderID: parseInt(orderID, 10), notice: outcome.notice, status: outcome.status });
		});
		return { invalid: !!(result && result.invalid), notices: notices };
	};

	return OrderEntry;
})();
