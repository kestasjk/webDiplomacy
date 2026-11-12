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
 * wD.LegalOrders: which orders each unit may be given this phase, worked out once from the board so that clicks
 * can be checked at once. A port of the /game/ board's precomputeLegalOrders.ts to territory IDs and any variant.
 * The server checks everything again when the orders are saved; this only decides what the map offers.
 *
 *   var legal = wD.LegalOrders.compute({ variant, phase, units, provinceStatus, members, ownCountryIDs });
 *
 *   units          { unitID: { id, countryID, type: 'Army'|'Fleet', terrID } } every unit on the board
 *   provinceStatus { provinceID: { ownerCountryID, unitID, standoff, occupiedFromTerrID } } from game.json territories
 *   members        [{ countryID, supplyCenterNo, unitNo }]
 *   ownCountryIDs  [countryID] the countries being ordered (all of them in a sandbox)
 *
 * Returns:
 *   moveDests[unitID]      territory IDs a unit may move to directly
 *   retreatDests[unitID]   territory IDs a dislodged unit may retreat to
 *   buildDests             territory IDs a unit may be built on (a coast's ID for a fleet on that coast)
 *   destroyUnits           { unitID: true } units which may be destroyed
 *   vias[unitID]           [{ dest: provinceID, path: [provinceID...] }] moves by convoy; a path starts with the
 *                          army's province and ends with the last fleet, which is what ajax.php's convoyPath wants
 *   convoys[unitID]        { armyProvinceID: { destProvinceID: { path: [provinceID...] } } } convoys a fleet may give
 *   supports[unitID]       { supportedProvinceID: [{ dest: provinceID, convoyPath: [..]|null }] } a dest equal to
 *                          the supported province is a support-hold
 */
var wD = window.wD || {};
window.wD = wD;

wD.LegalOrders = (function () {
	'use strict';

	function values(obj) {
		return Object.keys(obj).map(function (key) { return obj[key]; });
	}

	function moveDestinations(variant, units) {
		var result = {};
		values(units).forEach(function (unit) {
			result[unit.id] = variant.movesFrom(unit.terrID, unit.type);
		});
		return result;
	}

	function retreatDestinations(variant, units, provinceStatus, isOwn) {
		var result = {};
		values(units).forEach(function (unit) {
			if (!isOwn(unit)) return;
			var provinceID = variant.provinceID(unit.terrID);
			var status = provinceStatus[provinceID];
			// The unit holding the province isn't the one retreating
			if (!status || status.unitID === unit.id) return;

			var occupiedFrom = status.occupiedFromTerrID ? variant.provinceID(status.occupiedFromTerrID) : null;
			result[unit.id] = variant.movesFrom(unit.terrID, unit.type).filter(function (terrID) {
				var destProvince = variant.provinceID(terrID);
				var destStatus = provinceStatus[destProvince];
				// Not back where the attacker came from, not onto a unit, not into a standoff
				return occupiedFrom !== destProvince && (!destStatus || (!destStatus.unitID && !destStatus.standoff));
			});
		});
		return result;
	}

	function buildDestinations(variant, provinceStatus, members, ownCountryIDs, isSandbox) {
		var dests = [];
		// Some variants (Modern2, ClassicChaos, BuildAnywhere) let a country build on any supply centre it owns
		var anywhere = !!(variant.rules && variant.rules.buildAnywhere);
		members.forEach(function (member) {
			if (ownCountryIDs.indexOf(member.countryID) === -1 || member.supplyCenterNo <= member.unitNo) return;
			variant.territoryIDs().forEach(function (terrID) {
				var territory = variant.territory(terrID);
				if (territory.coast === 'Child' || !territory.supply || territory.type === 'Sea') return;
				var status = provinceStatus[terrID];
				if (!status || status.unitID) return;
				if (anywhere) {
					// Any supply centre the country owns, with no unit on it
					if (status.ownerCountryID !== member.countryID) return;
				} else {
					// A home supply centre of the country, owned by it, with no unit on it
					if (territory.homeCountryID !== member.countryID) return;
					if (status.ownerCountryID !== member.countryID && !isSandbox) return;
				}
				dests.push(terrID);
				variant.coastsOf(terrID).forEach(function (coastID) { dests.push(coastID); });
			});
		});
		return dests;
	}

	function destroyableUnits(units, members, ownCountryIDs) {
		var result = {};
		members.forEach(function (member) {
			if (ownCountryIDs.indexOf(member.countryID) === -1 || member.supplyCenterNo >= member.unitNo) return;
			values(units).forEach(function (unit) {
				if (unit.countryID === member.countryID) result[unit.id] = true;
			});
		});
		return result;
	}

	/**
	 * The fleets at sea as a graph, which is all a convoy can run through: for each sea with a unit in it, the
	 * occupied seas next to it and the coasts it touches.
	 * @returns {{next: Object, coasts: Object}} both keyed by the sea's province ID
	 */
	function seaGraph(variant, provinceStatus) {
		var occupied = {};
		variant.territoryIDs().forEach(function (terrID) {
			if (variant.isSea(terrID) && provinceStatus[terrID] && provinceStatus[terrID].unitID) occupied[terrID] = true;
		});
		var graph = { next: {}, coasts: {} };
		Object.keys(occupied).forEach(function (key) {
			var borders = variant.bordersFrom(Number(key), 'Fleet');
			graph.next[key] = borders.filter(function (id) { return occupied[id]; });
			graph.coasts[key] = borders.filter(function (id) { return !variant.isSea(id); });
		});
		return graph;
	}

	/**
	 * Where the fleets at sea could carry an army from a province: each coast a chain of them leads to, and the
	 * shortest chain there. A search outwards a sea at a time, which looks at each fleet once. (Listing every
	 * chain, as this used to, takes minutes once twenty-odd fleets are at sea together, as in a World game.)
	 * @param {number|null} withoutSeaID a sea the chains must keep out of
	 * @returns {Object} { destProvinceID: [startProvinceID, seaID...] } each path ending with the last fleet
	 */
	function convoyRoutes(variant, graph, startProvinceID, withoutSeaID) {
		var chains = {};
		var routes = {};
		var queue = [];
		var reach = function (seaID, chain) {
			if (!graph.next[seaID] || seaID === withoutSeaID || chains[seaID]) return;
			chains[seaID] = chain.concat([seaID]);
			queue.push(seaID);
		};
		variant.bordersFrom(startProvinceID, 'Fleet').forEach(function (seaID) { reach(seaID, [startProvinceID]); });
		for (var i = 0; i < queue.length; i++) {
			var chain = chains[queue[i]];
			graph.coasts[queue[i]].forEach(function (coastID) {
				if (coastID !== startProvinceID && !routes[coastID]) routes[coastID] = chain;
			});
			graph.next[queue[i]].forEach(function (seaID) { reach(seaID, chain); });
		}
		return routes;
	}

	/**
	 * The convoys which could run through one fleet for one army: the coasts the army could be carried to, with a
	 * path for each. A path through the fleet is two chains out from it which share no other sea, one to the army
	 * and one to the destination. The first is given; the second is searched for in a way which may take seas from
	 * the first and send it another way round (an augmenting path, as in a maximum flow), so a pair is found
	 * wherever there is one, without trying every pairing.
	 * @param {number[]} toArmy the chain from the fleet to the army: the fleet's sea first, a sea touching the
	 *   army's province last
	 * @returns {Object} { destProvinceID: { path: [armyProvinceID, seaID...] } }
	 */
	function convoysThrough(graph, fleetSeaID, armyProvinceID, toArmy) {
		var byDest = {};
		var lastSeaID = toArmy[toArmy.length - 1];
		var touchesArmy = function (seaID) { return graph.coasts[seaID].indexOf(armyProvinceID) !== -1; };
		var offer = function (seaID, path) {
			graph.coasts[seaID].forEach(function (coastID) {
				if (coastID !== armyProvinceID && !byDest[coastID]) byDest[coastID] = { path: path };
			});
		};

		// The coasts the fleet itself touches need no second chain
		offer(fleetSeaID, [armyProvinceID].concat(toArmy.slice(1).reverse(), [fleetSeaID]));

		var nextOnFirst = {};
		var prevOnFirst = {};
		for (var i = 1; i < toArmy.length; i++) {
			nextOnFirst[toArmy[i - 1]] = toArmy[i];
			prevOnFirst[toArmy[i]] = toArmy[i - 1];
		}

		// The search is over the ways into and out of each sea ('i' and 'o'), so that a sea is only passed through
		// once, and 'x', arriving at the army by another sea than the first chain's. from[] is how each was reached.
		var from = {};
		var queue = [];
		var reach = function (state, seaID, previous) {
			var key = state + seaID;
			if (from[key]) return;
			from[key] = { state: state, seaID: seaID, previous: previous };
			queue.push(from[key]);
		};
		graph.next[fleetSeaID].forEach(function (seaID) {
			if (seaID !== nextOnFirst[fleetSeaID]) reach('i', seaID, null);
		});
		if (lastSeaID !== fleetSeaID && touchesArmy(fleetSeaID)) reach('x', 0, null);

		for (var q = 0; q < queue.length; q++) {
			var at = queue[q];
			var seaID = at.seaID;
			if (at.state === 'i') {
				if (prevOnFirst[seaID] === undefined) {
					reach('o', seaID, at);
				} else if (prevOnFirst[seaID] !== fleetSeaID) {
					// A sea of the first chain can only be entered by taking it from that chain, which then has to
					// leave the sea before it another way
					reach('o', prevOnFirst[seaID], at);
				}
			} else if (at.state === 'o') {
				for (var n = 0; n < graph.next[seaID].length; n++) {
					var nextID = graph.next[seaID][n];
					if (nextID !== fleetSeaID && nextID !== nextOnFirst[seaID]) reach('i', nextID, at);
				}
				if (prevOnFirst[seaID] !== undefined) reach('i', seaID, at);
				if (seaID !== lastSeaID && touchesArmy(seaID)) reach('x', 0, at);
			} else if (lastSeaID !== fleetSeaID) {
				// The army is reached another way, so the first chain's last sea is free to go on from
				reach('o', lastSeaID, at);
			}
		}

		// Each sea the search could leave gives a destination chain ending there, once the first chain has made way
		queue.forEach(function (end) {
			if (end.state !== 'o') return;
			if (graph.coasts[end.seaID].every(function (coastID) { return coastID === armyProvinceID || byDest[coastID]; })) return;

			var steps = [];
			for (var reached = end; reached; reached = reached.previous) steps.unshift(reached);

			var next = {};
			Object.keys(nextOnFirst).forEach(function (key) { if (Number(key) !== fleetSeaID) next[key] = nextOnFirst[key]; });
			var firstSeas = (toArmy.length > 1) ? [toArmy[1]] : [];
			var armySeaID = lastSeaID;
			steps.forEach(function (step, index) {
				var previous = index ? steps[index - 1] : null;
				if (step.state === 'i') {
					if (!previous) firstSeas.push(step.seaID);
					else if (previous.seaID !== step.seaID) next[previous.seaID] = step.seaID;
				} else if (step.state === 'o') {
					// A sea taken from the first chain: the sea before it no longer leads there
					if (previous && previous.state === 'i' && previous.seaID !== step.seaID) delete next[step.seaID];
				} else {
					armySeaID = previous ? previous.seaID : fleetSeaID;
				}
			});

			var toArmySeas = (armySeaID === fleetSeaID) ? [] : null;
			var toDestSeas = null;
			firstSeas.forEach(function (firstID) {
				var chain = [firstID];
				while (next[chain[chain.length - 1]] !== undefined && chain.length < toArmy.length + steps.length) chain.push(next[chain[chain.length - 1]]);
				var chainEnd = chain[chain.length - 1];
				if (chainEnd === armySeaID && toArmySeas === null) toArmySeas = chain;
				else if (chainEnd === end.seaID && toDestSeas === null) toDestSeas = chain;
			});
			if (toArmySeas === null || toDestSeas === null) return;

			offer(end.seaID, [armyProvinceID].concat(toArmySeas.slice().reverse(), [fleetSeaID], toDestSeas));
		});

		return byDest;
	}

	function convoyOrders(variant, units, provinceStatus, isOwn) {
		var graph = seaGraph(variant, provinceStatus);
		var vias = {};
		var convoys = {};
		var routes = {};

		// Where each army could be carried, and by which chain
		values(units).forEach(function (unit) {
			if (unit.type !== 'Army') return;
			var byDest = convoyRoutes(variant, graph, variant.provinceID(unit.terrID), null);
			routes[unit.id] = byDest;
			vias[unit.id] = Object.keys(byDest).map(function (dest) { return { dest: Number(dest), path: byDest[dest] }; });
		});

		var isArmyAt = function (provinceID) {
			var status = provinceStatus[provinceID];
			return !!(status && status.unitID && units[status.unitID] && units[status.unitID].type === 'Army');
		};

		// Which armies each of our fleets at sea could carry, and where
		values(units).forEach(function (unit) {
			if (!isOwn(unit) || unit.type !== 'Fleet' || !variant.isSea(unit.terrID)) return;
			var fleetSeaID = variant.provinceID(unit.terrID);
			convoys[unit.id] = {};
			if (!graph.next[fleetSeaID]) return;

			// The seas the fleet is linked to, nearest first, and the way back to it from each
			var order = [fleetSeaID];
			var back = {};
			back[fleetSeaID] = null;
			for (var i = 0; i < order.length; i++) {
				graph.next[order[i]].forEach(function (seaID) {
					if (back[seaID] === undefined) {
						back[seaID] = order[i];
						order.push(seaID);
					}
				});
			}

			// The armies on the coasts of those seas, each with the nearest of the seas
			var nearestSea = {};
			order.forEach(function (seaID) {
				graph.coasts[seaID].forEach(function (coastID) {
					if (nearestSea[coastID] === undefined && isArmyAt(coastID)) nearestSea[coastID] = seaID;
				});
			});

			Object.keys(nearestSea).forEach(function (key) {
				var toArmy = [];
				for (var seaID = nearestSea[key]; seaID !== null; seaID = back[seaID]) toArmy.unshift(seaID);
				var byDest = convoysThrough(graph, fleetSeaID, Number(key), toArmy);
				if (Object.keys(byDest).length) convoys[unit.id][key] = byDest;
			});
		});

		/** A path carrying an army to a coast which keeps out of a province, or null */
		var routeWithout = {};
		var route = function (army, destProvinceID, withoutProvinceID) {
			var path = (routes[army.id] || {})[destProvinceID];
			if (!path || path.indexOf(withoutProvinceID) === -1) return path || null;
			var key = army.id + ' ' + withoutProvinceID;
			if (!routeWithout[key]) routeWithout[key] = convoyRoutes(variant, graph, variant.provinceID(army.terrID), withoutProvinceID);
			return routeWithout[key][destProvinceID] || null;
		};

		return { vias: vias, convoys: convoys, route: route };
	}

	function supportOrders(variant, units, isOwn, moveDests, convoyRoute) {
		var supports = {};
		values(units).forEach(function (unit) {
			if (!isOwn(unit)) return;
			var bySource = {};
			var unitProvince = variant.provinceID(unit.terrID);
			var add = function (source, dest, convoyPath) {
				(bySource[source] = bySource[source] || []).push({ dest: dest, convoyPath: convoyPath });
			};

			// Every province this unit could move to, it could support a unit into (or support the unit there)
			variant.bordersFrom(unit.terrID, unit.type).forEach(function (destProvince) {
				values(units).forEach(function (other) {
					if (other.id === unit.id) return;
					var otherProvince = variant.provinceID(other.terrID);
					if (otherProvince === destProvince) {
						add(otherProvince, destProvince, null); // Support hold
					} else if ((moveDests[other.id] || []).some(function (terrID) { return variant.provinceID(terrID) === destProvince; })) {
						add(otherProvince, destProvince, null); // Support move
					} else if (other.type === 'Army') {
						// Support a move by convoy, as long as the convoy doesn't run through this unit
						var path = convoyRoute(other, destProvince, unitProvince);
						if (path) add(otherProvince, destProvince, path);
					}
				});
			});
			supports[unit.id] = bySource;
		});
		return supports;
	}

	function compute(board) {
		var variant = board.variant;
		var units = board.units || {};
		var provinceStatus = board.provinceStatus || {};
		var members = board.members || [];
		var ownCountryIDs = board.ownCountryIDs || [];
		var isOwn = function (unit) { return ownCountryIDs.indexOf(unit.countryID) !== -1; };

		var result = {
			moveDests: {}, retreatDests: {}, buildDests: [], destroyUnits: {},
			vias: {}, convoys: {}, supports: {}
		};

		if (board.phase === 'Diplomacy') {
			result.moveDests = moveDestinations(variant, units);
			var convoy = convoyOrders(variant, units, provinceStatus, isOwn);
			result.vias = convoy.vias;
			result.convoys = convoy.convoys;
			result.supports = supportOrders(variant, units, isOwn, result.moveDests, convoy.route);
		} else if (board.phase === 'Retreats') {
			result.retreatDests = retreatDestinations(variant, units, provinceStatus, isOwn);
		} else if (board.phase === 'Builds') {
			result.buildDests = buildDestinations(variant, provinceStatus, members, ownCountryIDs, !!board.isSandbox);
			result.destroyUnits = destroyableUnits(units, members, ownCountryIDs);
		}
		return result;
	}

	return { compute: compute };
})();
