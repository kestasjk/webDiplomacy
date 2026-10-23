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
 *   vias[unitID]           [{ dest: terrID, paths: [[provinceID...]] }] moves by convoy; a path starts with the
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
		members.forEach(function (member) {
			if (ownCountryIDs.indexOf(member.countryID) === -1 || member.supplyCenterNo <= member.unitNo) return;
			variant.territoryIDs().forEach(function (terrID) {
				var territory = variant.territory(terrID);
				if (territory.coast === 'Child' || !territory.supply) return;
				// A home supply centre of the country, owned by it, with no unit on it
				if (territory.homeCountryID !== member.countryID) return;
				var status = provinceStatus[terrID];
				if (!status || status.unitID) return;
				if (status.ownerCountryID !== member.countryID && !isSandbox) return;
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
	 * Walks the fleets at sea from a province: every chain of occupied sea provinces leading to a coast.
	 * @param {function} onCoast called with (coastProvinceID, pathSoFar) for each coast reached; pathSoFar starts
	 *   with startProvinceID and ends with the last sea province
	 * @param {boolean} firstStepMustBeSea whether the first province entered must be a sea (for a convoyed army)
	 */
	function walkConvoyChains(variant, provinceStatus, startProvinceID, firstStepMustBeSea, onCoast) {
		var reached = {};
		reached[startProvinceID] = true;

		var search = function (provinceID, path) {
			variant.bordersFrom(provinceID, 'Fleet').forEach(function (nextID) {
				if (reached[nextID]) return;
				var isSea = variant.isSea(nextID);
				if (firstStepMustBeSea && path.length <= 1 && !isSea) return;
				if (isSea) {
					// A sea can be crossed if a fleet is there
					if (provinceStatus[nextID] && provinceStatus[nextID].unitID) {
						reached[nextID] = true;
						path.push(nextID);
						search(nextID, path);
						path.pop();
						delete reached[nextID];
					}
				} else {
					onCoast(nextID, path);
				}
			});
		};
		search(startProvinceID, [startProvinceID]);
	}

	function convoyOrders(variant, units, provinceStatus, isOwn) {
		var vias = {};
		var convoys = {};

		// Where each army could be carried, and by which chains
		values(units).forEach(function (unit) {
			if (unit.type !== 'Army') return;
			var start = variant.provinceID(unit.terrID);
			var byDest = {};
			walkConvoyChains(variant, provinceStatus, start, true, function (destID, path) {
				if (destID === start) return;
				if (!byDest[destID]) byDest[destID] = { dest: destID, paths: [] };
				byDest[destID].paths.push(path.slice());
			});
			vias[unit.id] = values(byDest);
		});

		// Which armies each of our fleets at sea could carry, and where: chains out from the fleet to a coastal
		// army, joined to chains out from the fleet to another coast which share no sea with the first
		values(units).forEach(function (unit) {
			if (!isOwn(unit) || unit.type !== 'Fleet' || !variant.isSea(unit.terrID)) return;
			var fleetProvince = variant.provinceID(unit.terrID);
			var toArmies = [];
			var toCoasts = [];
			walkConvoyChains(variant, provinceStatus, fleetProvince, false, function (coastID, path) {
				var record = { dest: coastID, path: path.slice() };
				toCoasts.push(record);
				var status = provinceStatus[coastID];
				if (status && status.unitID && units[status.unitID] && units[status.unitID].type === 'Army') toArmies.push(record);
			});

			var byArmy = {};
			toArmies.forEach(function (toArmy) {
				// From the army back to this fleet, the fleet itself left off as the second half starts with it
				var firstHalf = toArmy.path.slice();
				firstHalf.push(toArmy.dest);
				firstHalf.reverse();
				firstHalf.pop();
				var used = {};
				firstHalf.forEach(function (id) { used[id] = true; });

				var byDest = {};
				toCoasts.forEach(function (toCoast) {
					if (toCoast.dest === toArmy.dest || byDest[toCoast.dest]) return;
					if (toCoast.path.every(function (id) { return !used[id]; })) {
						byDest[toCoast.dest] = { path: firstHalf.concat(toCoast.path) };
					}
				});
				if (Object.keys(byDest).length) byArmy[toArmy.dest] = byDest;
			});
			convoys[unit.id] = byArmy;
		});

		return { vias: vias, convoys: convoys };
	}

	function supportOrders(variant, units, isOwn, moveDests, vias) {
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
					} else if (vias[other.id]) {
						// Support a move by convoy, as long as the convoy doesn't run through this unit
						var via = vias[other.id].filter(function (v) { return v.dest === destProvince; })[0];
						if (via) {
							var path = via.paths.filter(function (p) { return p.indexOf(unitProvince) === -1; })[0];
							if (path) add(otherProvince, destProvince, path);
						}
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
			result.supports = supportOrders(variant, units, isOwn, result.moveDests, result.vias);
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
