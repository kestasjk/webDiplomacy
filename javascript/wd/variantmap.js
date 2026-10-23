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
 * wD.VariantMap: a variant's variant.json (doc/gamedata/02-spec.md section 2.1) with the lookups a board needs.
 * Territory IDs are numbers throughout. A "province" is a territory and its coasts: a coast's province is its
 * coastParentID, every other territory is its own province. Units sit on territories (a fleet on a coast), while
 * ownership, standoffs and adjacency for armies are per province.
 *
 *   var variant = new wD.VariantMap(variantJSON);
 *   variant.territory(6).name; variant.provinceID(7); variant.coastsOf(6); variant.position(6, 'small')
 */
var wD = window.wD || {};
window.wD = wD;

wD.VariantMap = (function () {
	'use strict';

	function VariantMap(data) {
		this.data = data;
		this.variantID = data.variantID;
		this.name = data.name;
		this.fullName = data.fullName;
		this.countries = data.countries || [];
		this.supplyCenterCount = data.supplyCenterCount;
		this.supplyCenterTarget = data.supplyCenterTarget;
		this.map = data.map || null;

		this.territories = {};
		this.byName = {};
		this.coasts = {}; // parent id -> [child coast ids]
		(data.territories || []).forEach(function (territory) {
			this.territories[territory.id] = territory;
			this.byName[territory.name] = territory;
		}, this);
		Object.keys(this.territories).forEach(function (id) {
			var territory = this.territories[id];
			if (territory.coast === 'Child') {
				(this.coasts[territory.coastParentID] = this.coasts[territory.coastParentID] || []).push(territory.id);
			}
		}, this);
	}

	VariantMap.prototype.territory = function (terrID) {
		return this.territories[terrID] || null;
	};

	VariantMap.prototype.territoryIDs = function () {
		return Object.keys(this.territories).map(Number);
	};

	VariantMap.prototype.countryName = function (countryID) {
		if (countryID === 0 || countryID === null || countryID === undefined) return 'Neutral';
		var country = this.countries.filter(function (c) { return c.countryID === countryID; })[0];
		return country ? country.name : ('Country ' + countryID);
	};

	/** The province a territory belongs to: itself, or its parent for a coast */
	VariantMap.prototype.provinceID = function (terrID) {
		var territory = this.territories[terrID];
		if (!territory) return terrID;
		return territory.coast === 'Child' ? territory.coastParentID : territory.id;
	};

	VariantMap.prototype.isCoastChild = function (terrID) {
		var territory = this.territories[terrID];
		return !!territory && territory.coast === 'Child';
	};

	/** The coasts of a province, [] if it has none (fleets then sit on the province's own territory) */
	VariantMap.prototype.coastsOf = function (provinceID) {
		return this.coasts[provinceID] || [];
	};

	/** Every territory ID in a province: itself and its coasts */
	VariantMap.prototype.provinceTerritoryIDs = function (provinceID) {
		return [provinceID].concat(this.coastsOf(provinceID));
	};

	VariantMap.prototype.isSea = function (terrID) {
		var territory = this.territories[terrID];
		return !!territory && territory.type === 'Sea';
	};

	VariantMap.prototype.isSupplyCenter = function (terrID) {
		var territory = this.territories[this.provinceID(terrID)];
		return !!territory && territory.supply === true;
	};

	/**
	 * The borders a unit type may cross from a territory, at the territory level (coastalBorders): where a fleet on
	 * this coast or an army here may move to.
	 * @returns {number[]} Territory IDs
	 */
	VariantMap.prototype.movesFrom = function (terrID, unitType) {
		var territory = this.territories[terrID];
		if (!territory) return [];
		var key = unitType === 'Army' ? 'army' : 'fleet';
		return territory.coastalBorders.filter(function (border) { return border[key]; }).map(function (border) { return border.id; });
	};

	/**
	 * The province-level borders a unit type may cross (borders): which provinces are next to this one for that
	 * unit type, regardless of coast.
	 * @returns {number[]} Province IDs
	 */
	VariantMap.prototype.bordersFrom = function (terrID, unitType) {
		var territory = this.territories[terrID];
		if (!territory) return [];
		var key = unitType === 'Army' ? 'army' : 'fleet';
		return territory.borders.filter(function (border) { return border[key]; }).map(function (border) { return border.id; });
	};

	/**
	 * Where a territory is on a map image.
	 * @param {string} size 'small' or 'large'
	 * @returns {{x: number, y: number}}
	 */
	VariantMap.prototype.position = function (terrID, size) {
		var territory = this.territories[terrID];
		if (!territory) return { x: 0, y: 0 };
		if (size === 'large') return { x: territory.mapX, y: territory.mapY };
		return { x: territory.smallMapX, y: territory.smallMapY };
	};

	/** The images for a map size, from variant.json's map block: { url, width, height, namesUrl, army, fleet, standoff } */
	VariantMap.prototype.images = function (size) {
		if (!this.map) return null;
		return this.map[size === 'large' ? 'large' : 'small'] || null;
	};

	/** [r, g, b] for a country, countryID 0 being neutral land */
	VariantMap.prototype.countryColor = function (countryID) {
		var colors = (this.map && this.map.countryColors) || [];
		return colors[countryID] || colors[0] || [226, 198, 158];
	};

	return VariantMap;
})();
