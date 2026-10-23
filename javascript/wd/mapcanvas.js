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
 * wD.MapCanvas: draws a variant's map on a <canvas> from the same images map.php uses, and says which territory
 * a click landed on. It knows nothing about games or orders beyond what it is told to draw, so the board, the
 * sandbox set-up page and the registration challenge can all use it.
 *
 *   var map = new wD.MapCanvas(container, variant, { size: 'small' });
 *   map.load().then(function () {
 *     map.setState({ ownership: {6: 1}, units: [{id: 1, type: 'Fleet', countryID: 1, terrID: 6}], orders: [] });
 *     map.onClick = function (hit, event) { ... hit.terrID, hit.coastTerrID ... };
 *   });
 *
 * The map images are colour-coded, each land territory one colour of its own, which is how map.php recolours a
 * territory: the same colours give each pixel's territory here (seas may share a colour, and are told apart by
 * the nearest territory position). Everything is drawn in the image's own coordinates; the canvas is rendered at
 * a multiple of the image size so that lines and icons stay sharp when the page scales it.
 *
 * State given to setState():
 *   ownership   { provinceID: countryID }                  who owns each land province (0 or missing: neutral)
 *   units       [{ id, type, countryID, terrID, retreating }]
 *   orders      [{ type, countryID, unitType, terrID, toTerrID, fromTerrID, viaConvoy, success, dislodged, pending }]
 *               type as the database spells it: Hold, Move, Retreat, Support hold, Support move, Convoy,
 *               Build Army, Build Fleet, Destroy, Disband, Wait. success null means not adjudicated (being entered)
 *   standoffs   [provinceID]
 *   highlights  { terrID: 'selected' | 'target' | 'source' | 'invalid' }
 */
var wD = window.wD || {};
window.wD = wD;

wD.MapCanvas = (function () {
	'use strict';

	var MAX_RENDER_SCALE = 3;
	var COLORS = {
		move: '#000000',
		pending: '#cc5500',
		support: '#2a7a2a',
		convoy: '#1f5fa8',
		retreat: '#7a1fa8',
		fail: '#d01010',
		selected: 'rgba(255, 215, 0, 0.9)',
		target: 'rgba(40, 160, 60, 0.55)',
		source: 'rgba(30, 110, 220, 0.55)',
		invalid: 'rgba(220, 30, 30, 0.6)',
		hover: 'rgba(0, 0, 0, 0.18)'
	};

	function loadImage(url) {
		return new Promise(function (resolve, reject) {
			var image = new Image();
			image.onload = function () { resolve(image); };
			image.onerror = function () { reject(new Error('Could not load ' + url)); };
			image.src = url;
		});
	}

	/** The unit and name overlays use white for transparency */
	function whiteToTransparent(image) {
		var canvas = document.createElement('canvas');
		canvas.width = image.width;
		canvas.height = image.height;
		var ctx = canvas.getContext('2d');
		ctx.drawImage(image, 0, 0);
		var imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
		var data = imageData.data;
		for (var i = 0; i < data.length; i += 4) {
			if (data[i] > 245 && data[i + 1] > 245 && data[i + 2] > 245) data[i + 3] = 0;
		}
		ctx.putImageData(imageData, 0, 0);
		return canvas;
	}

	function rgb(color, alpha) {
		if (alpha === undefined) return 'rgb(' + color[0] + ',' + color[1] + ',' + color[2] + ')';
		return 'rgba(' + color[0] + ',' + color[1] + ',' + color[2] + ',' + alpha + ')';
	}

	function MapCanvas(container, variant, options) {
		options = options || {};
		this.container = container;
		this.variant = variant;
		this.siteRoot = options.siteRoot || '';
		this.size = options.size === 'large' ? 'large' : 'small';

		this.state = { ownership: {}, units: [], orders: [], standoffs: [], highlights: {} };
		this.hoverTerrID = null;
		this.onClick = null;
		this.onHover = null;

		this.images = null;
		this.index = null;
		this.baseCanvas = null;
		this.ownershipKey = null;
		this.renderScale = 1;
		this.loaded = false;

		this.root = document.createElement('div');
		this.root.className = 'wdmap';
		this.canvas = document.createElement('canvas');
		this.canvas.className = 'wdmap-canvas';
		this.root.appendChild(this.canvas);
		this.overlay = document.createElement('div');
		this.overlay.className = 'wdmap-overlay';
		this.root.appendChild(this.overlay);
		container.appendChild(this.root);

		this.bindEvents();
	}

	/* Loading */

	MapCanvas.prototype.load = function () {
		var self = this;
		var spec = this.variant.images(this.size);
		if (!spec) return Promise.reject(new Error('This variant has no map images in its variant.json'));

		var urls = [spec.url, spec.army, spec.fleet, spec.standoff, spec.namesUrl].map(function (url) {
			return url ? self.siteRoot + url : null;
		});
		return Promise.all(urls.map(function (url) { return url ? loadImage(url) : Promise.resolve(null); })).then(function (images) {
			self.images = {
				map: images[0],
				army: whiteToTransparent(images[1]),
				fleet: whiteToTransparent(images[2]),
				standoff: images[3] ? whiteToTransparent(images[3]) : null,
				names: images[4] ? whiteToTransparent(images[4]) : null
			};
			self.width = images[0].width;
			self.height = images[0].height;
			self.buildIndex();
			self.ownershipKey = null;
			self.loaded = true;
			self.fitCanvas();
			self.draw();
		});
	};

	/** Switch between the small and large map images, keeping what is drawn */
	MapCanvas.prototype.setSize = function (size) {
		size = size === 'large' ? 'large' : 'small';
		if (size === this.size && this.loaded) return Promise.resolve();
		this.size = size;
		this.loaded = false;
		return this.load();
	};

	/** The unit marker's radius, which everything else is scaled from: a ring of country colour around the icon */
	MapCanvas.prototype.unitRadius = function () {
		var icon = this.images.army;
		return Math.max(6, Math.round(Math.max(icon.width, icon.height) * 0.7));
	};

	/**
	 * Which territory each pixel belongs to. Each land territory has a colour of its own, found at its position;
	 * territories sharing a colour (the seas) are told apart by whichever is nearest. Pixels of no territory's
	 * colour (borders, names, land that isn't a territory) are 0.
	 */
	MapCanvas.prototype.buildIndex = function () {
		var self = this;
		var width = this.width;
		var height = this.height;
		var canvas = document.createElement('canvas');
		canvas.width = width;
		canvas.height = height;
		var ctx = canvas.getContext('2d');
		ctx.drawImage(this.images.map, 0, 0);
		var data = ctx.getImageData(0, 0, width, height).data;
		this.baseImageData = ctx.getImageData(0, 0, width, height);

		var byColor = {};
		var positions = {};
		this.variant.territoryIDs().forEach(function (terrID) {
			var territory = self.variant.territory(terrID);
			if (territory.coast === 'Child') return;
			var pos = self.variant.position(terrID, self.size);
			if (pos.x < 0 || pos.y < 0 || pos.x >= width || pos.y >= height) return;
			var i = (pos.y * width + pos.x) * 4;
			var key = (data[i] << 16) | (data[i + 1] << 8) | data[i + 2];
			(byColor[key] = byColor[key] || []).push(terrID);
			positions[terrID] = pos;
		});

		var index = new Int16Array(width * height);
		for (var y = 0; y < height; y++) {
			for (var x = 0; x < width; x++) {
				var p = y * width + x;
				var i = p * 4;
				var candidates = byColor[(data[i] << 16) | (data[i + 1] << 8) | data[i + 2]];
				if (!candidates) continue;
				if (candidates.length === 1) {
					index[p] = candidates[0];
					continue;
				}
				var best = 0;
				var bestDistance = Infinity;
				for (var c = 0; c < candidates.length; c++) {
					var pos = positions[candidates[c]];
					var dx = pos.x - x;
					var dy = pos.y - y;
					var distance = dx * dx + dy * dy;
					if (distance < bestDistance) { bestDistance = distance; best = candidates[c]; }
				}
				index[p] = best;
			}
		}
		this.index = index;
		this.positions = positions;
	};

	/* Geometry */

	/**
	 * Size the canvas to a whole multiple of the image for the space it has, so lines stay sharp. Setting a canvas's
	 * size wipes it, so it is only set when it changes.
	 * @returns {boolean} Whether the size changed, and the map needs drawing again
	 */
	MapCanvas.prototype.fitCanvas = function () {
		if (!this.images) return false;
		var displayWidth = this.root.clientWidth || this.width;
		var dpr = window.devicePixelRatio || 1;
		var scale = Math.min(MAX_RENDER_SCALE, Math.max(1, Math.ceil((displayWidth * dpr) / this.width)));
		var width = this.width * scale;
		var height = this.height * scale;
		if (scale === this.renderScale && this.canvas.width === width && this.canvas.height === height) return false;
		this.renderScale = scale;
		this.canvas.width = width;
		this.canvas.height = height;
		this.canvas.style.aspectRatio = this.width + ' / ' + this.height;
		return true;
	};

	/** Map image coordinates for a point on the page */
	MapCanvas.prototype.clientToMap = function (clientX, clientY) {
		var rect = this.canvas.getBoundingClientRect();
		if (!rect.width || !rect.height) return { x: -1, y: -1 };
		return {
			x: Math.floor((clientX - rect.left) * this.width / rect.width),
			y: Math.floor((clientY - rect.top) * this.height / rect.height)
		};
	};

	/** Where a map coordinate is within the map's root element, in CSS pixels, for placing HTML over the map */
	MapCanvas.prototype.mapToLocal = function (x, y) {
		var rect = this.canvas.getBoundingClientRect();
		return { x: x * rect.width / this.width, y: y * rect.height / this.height };
	};

	MapCanvas.prototype.position = function (terrID) {
		return this.variant.position(terrID, this.size);
	};

	/**
	 * The territory at a map coordinate, or null. Border lines and names belong to no territory, so a few pixels
	 * around the point are tried before giving up.
	 */
	MapCanvas.prototype.territoryAtMap = function (x, y) {
		if (!this.index) return null;
		var width = this.width;
		var height = this.height;
		for (var radius = 0; radius <= 4; radius++) {
			for (var dy = -radius; dy <= radius; dy++) {
				for (var dx = -radius; dx <= radius; dx++) {
					if (Math.abs(dx) !== radius && Math.abs(dy) !== radius) continue;
					var px = x + dx;
					var py = y + dy;
					if (px < 0 || py < 0 || px >= width || py >= height) continue;
					var terrID = this.index[py * width + px];
					if (terrID) return terrID;
				}
			}
		}
		return null;
	};

	/**
	 * For a province with coasts, the coast nearest to a point (a fleet must be ordered to a coast); otherwise the
	 * province itself.
	 */
	MapCanvas.prototype.coastNearest = function (provinceID, x, y) {
		var coasts = this.variant.coastsOf(provinceID);
		if (!coasts.length) return provinceID;
		var best = coasts[0];
		var bestDistance = Infinity;
		for (var i = 0; i < coasts.length; i++) {
			var pos = this.position(coasts[i]);
			var distance = (pos.x - x) * (pos.x - x) + (pos.y - y) * (pos.y - y);
			if (distance < bestDistance) { bestDistance = distance; best = coasts[i]; }
		}
		return best;
	};

	/** @returns {{terrID, coastTerrID, x, y}|null} What a page point lands on */
	MapCanvas.prototype.hitTest = function (clientX, clientY) {
		var point = this.clientToMap(clientX, clientY);
		var terrID = this.territoryAtMap(point.x, point.y);
		if (!terrID) return null;
		return { terrID: terrID, coastTerrID: this.coastNearest(terrID, point.x, point.y), x: point.x, y: point.y };
	};

	MapCanvas.prototype.bindEvents = function () {
		var self = this;
		this.canvas.addEventListener('click', function (e) {
			if (!self.loaded || !self.onClick) return;
			var hit = self.hitTest(e.clientX, e.clientY);
			self.onClick(hit, e);
		});
		this.canvas.addEventListener('mousemove', function (e) {
			if (!self.loaded) return;
			var hit = self.hitTest(e.clientX, e.clientY);
			var terrID = hit ? hit.terrID : null;
			if (terrID !== self.hoverTerrID) {
				self.hoverTerrID = terrID;
				if (self.onHover) self.onHover(hit);
				self.draw();
			}
		});
		this.canvas.addEventListener('mouseleave', function () {
			if (self.hoverTerrID !== null) {
				self.hoverTerrID = null;
				if (self.onHover) self.onHover(null);
				self.draw();
			}
		});
		var resize = function () {
			if (!self.loaded) return;
			if (self.fitCanvas()) self.draw();
		};
		if (typeof ResizeObserver === 'function') {
			new ResizeObserver(resize).observe(this.root);
		} else {
			window.addEventListener('resize', resize);
		}
	};

	/* State and drawing */

	MapCanvas.prototype.setState = function (state) {
		var self = this;
		Object.keys(state).forEach(function (key) { self.state[key] = state[key]; });
		if (this.loaded) this.draw();
	};

	/** The map with its land coloured by owner; redone only when the ownership changes */
	MapCanvas.prototype.baseLayer = function () {
		var ownership = this.state.ownership || {};
		var key = JSON.stringify(ownership);
		if (this.baseCanvas && this.ownershipKey === key) return this.baseCanvas;

		var width = this.width;
		var height = this.height;
		var source = this.baseImageData.data;
		var imageData = new ImageData(new Uint8ClampedArray(source), width, height);
		var data = imageData.data;
		var index = this.index;

		var colorByTerr = {};
		var self = this;
		Object.keys(ownership).forEach(function (provinceID) {
			var countryID = ownership[provinceID];
			if (!countryID) return;
			var territory = self.variant.territory(parseInt(provinceID, 10));
			if (!territory || territory.type === 'Sea') return;
			colorByTerr[provinceID] = self.variant.countryColor(countryID);
		});

		if (Object.keys(colorByTerr).length) {
			for (var p = 0, i = 0; p < index.length; p++, i += 4) {
				var color = index[p] ? colorByTerr[index[p]] : undefined;
				if (!color) continue;
				data[i] = color[0];
				data[i + 1] = color[1];
				data[i + 2] = color[2];
			}
		}

		if (!this.baseCanvas) {
			this.baseCanvas = document.createElement('canvas');
		}
		this.baseCanvas.width = width;
		this.baseCanvas.height = height;
		this.baseCanvas.getContext('2d').putImageData(imageData, 0, 0);
		this.ownershipKey = key;
		return this.baseCanvas;
	};

	MapCanvas.prototype.draw = function () {
		if (!this.loaded) return;
		var ctx = this.canvas.getContext('2d');
		var scale = this.renderScale;
		ctx.setTransform(1, 0, 0, 1, 0, 0);
		ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
		ctx.setTransform(scale, 0, 0, scale, 0, 0);
		ctx.imageSmoothingEnabled = true;

		ctx.drawImage(this.baseLayer(), 0, 0);
		if (this.images.names) ctx.drawImage(this.images.names, 0, 0);

		this.drawHighlights(ctx);
		this.drawStandoffs(ctx);
		this.drawOrders(ctx);
		this.drawUnits(ctx);
		if (this.hoverTerrID) this.drawRing(ctx, this.position(this.hoverTerrID), this.unitRadius() + 4, COLORS.hover, 2);
	};

	MapCanvas.prototype.drawRing = function (ctx, pos, radius, color, width, dash) {
		ctx.save();
		ctx.beginPath();
		ctx.arc(pos.x, pos.y, radius, 0, Math.PI * 2);
		ctx.lineWidth = width || 2;
		ctx.strokeStyle = color;
		if (dash) ctx.setLineDash(dash);
		ctx.stroke();
		ctx.restore();
	};

	MapCanvas.prototype.drawHighlights = function (ctx) {
		var self = this;
		var highlights = this.state.highlights || {};
		var radius = this.unitRadius();
		Object.keys(highlights).forEach(function (terrID) {
			var kind = highlights[terrID];
			var pos = self.position(parseInt(terrID, 10));
			ctx.save();
			ctx.beginPath();
			ctx.arc(pos.x, pos.y, radius + 5, 0, Math.PI * 2);
			if (kind === 'selected') {
				ctx.lineWidth = 3;
				ctx.strokeStyle = COLORS.selected;
				ctx.stroke();
			} else {
				ctx.fillStyle = COLORS[kind] || COLORS.target;
				ctx.fill();
			}
			ctx.restore();
		});
	};

	MapCanvas.prototype.drawStandoffs = function (ctx) {
		var self = this;
		(this.state.standoffs || []).forEach(function (provinceID) {
			var pos = self.position(provinceID);
			if (self.images.standoff) {
				var image = self.images.standoff;
				ctx.drawImage(image, pos.x - image.width / 2, pos.y - image.height / 2);
			} else {
				self.drawCross(ctx, pos, self.unitRadius() * 0.6, '#c81414');
			}
		});
	};

	MapCanvas.prototype.drawCross = function (ctx, pos, size, color, width) {
		ctx.save();
		ctx.strokeStyle = color;
		ctx.lineWidth = width || 3;
		ctx.lineCap = 'round';
		ctx.beginPath();
		ctx.moveTo(pos.x - size, pos.y - size);
		ctx.lineTo(pos.x + size, pos.y + size);
		ctx.moveTo(pos.x + size, pos.y - size);
		ctx.lineTo(pos.x - size, pos.y + size);
		ctx.stroke();
		ctx.restore();
	};

	MapCanvas.prototype.drawUnits = function (ctx) {
		var self = this;
		var radius = this.unitRadius();
		var ownership = this.state.ownership || {};
		(this.state.units || []).forEach(function (unit) {
			var pos = self.position(unit.terrID);
			var x = pos.x;
			var y = pos.y;
			if (unit.retreating) {
				// A dislodged unit shares its province with the attacker; it is drawn to one side with a red ring
				x += radius * 1.4;
				y -= radius * 0.9;
			}
			var image = unit.type === 'Fleet' ? self.images.fleet : self.images.army;
			var color = self.variant.countryColor(unit.countryID);

			ctx.save();
			ctx.beginPath();
			ctx.arc(x, y, radius, 0, Math.PI * 2);
			ctx.fillStyle = rgb(color, 0.95);
			ctx.fill();
			ctx.lineWidth = 1.5;
			ctx.strokeStyle = unit.retreating ? COLORS.fail : '#000';
			if (unit.retreating) ctx.setLineDash([3, 2]);
			ctx.stroke();
			ctx.restore();

			// A small flag when a unit stands in a province its country doesn't own, as map.php draws
			var provinceID = self.variant.provinceID(unit.terrID);
			if (!self.variant.isSea(unit.terrID) && ownership[provinceID] !== unit.countryID && !unit.retreating) {
				ctx.save();
				ctx.fillStyle = rgb(color);
				ctx.strokeStyle = '#000';
				ctx.lineWidth = 1;
				ctx.fillRect(x + radius * 0.55, y - radius * 1.1, radius * 0.6, radius * 0.6);
				ctx.strokeRect(x + radius * 0.55, y - radius * 1.1, radius * 0.6, radius * 0.6);
				ctx.restore();
			}

			ctx.drawImage(image, x - image.width / 2, y - image.height / 2);
		});
	};

	/** The point a distance back from the end of a line, so arrows stop short of the unit they point at */
	function shorten(from, to, by) {
		var dx = to.x - from.x;
		var dy = to.y - from.y;
		var length = Math.sqrt(dx * dx + dy * dy);
		if (length <= by) return to;
		return { x: to.x - dx * by / length, y: to.y - dy * by / length };
	}

	function along(from, to, fraction) {
		return { x: from.x + (to.x - from.x) * fraction, y: from.y + (to.y - from.y) * fraction };
	}

	MapCanvas.prototype.drawArrow = function (ctx, from, to, style) {
		var radius = this.unitRadius();
		var end = shorten(from, to, style.stopShort === undefined ? radius + 2 : style.stopShort);
		var start = shorten(to, from, radius * 0.8);
		var headLength = Math.max(6, radius * 0.9);
		var angle = Math.atan2(end.y - start.y, end.x - start.x);

		ctx.save();
		ctx.strokeStyle = style.color;
		ctx.fillStyle = style.color;
		ctx.lineWidth = style.width || Math.max(2, radius / 5);
		ctx.lineCap = 'round';
		ctx.lineJoin = 'round';
		if (style.dash) ctx.setLineDash(style.dash);

		// Outline the line in white so it stands out on every colour of land
		ctx.save();
		ctx.strokeStyle = 'rgba(255,255,255,0.7)';
		ctx.lineWidth = ctx.lineWidth + 2;
		ctx.setLineDash([]);
		ctx.beginPath();
		ctx.moveTo(start.x, start.y);
		ctx.lineTo(end.x, end.y);
		ctx.stroke();
		ctx.restore();

		ctx.beginPath();
		ctx.moveTo(start.x, start.y);
		ctx.lineTo(end.x, end.y);
		ctx.stroke();

		if (style.head !== false) {
			ctx.setLineDash([]);
			ctx.beginPath();
			ctx.moveTo(end.x, end.y);
			ctx.lineTo(end.x - headLength * Math.cos(angle - Math.PI / 6), end.y - headLength * Math.sin(angle - Math.PI / 6));
			ctx.lineTo(end.x - headLength * Math.cos(angle + Math.PI / 6), end.y - headLength * Math.sin(angle + Math.PI / 6));
			ctx.closePath();
			ctx.fill();
		} else {
			// A support-hold ends in a hollow circle rather than a head
			ctx.setLineDash([]);
			ctx.beginPath();
			ctx.arc(end.x, end.y, Math.max(3, radius / 3), 0, Math.PI * 2);
			ctx.stroke();
		}
		ctx.restore();
	};

	MapCanvas.prototype.drawOrders = function (ctx) {
		var self = this;
		var radius = this.unitRadius();
		(this.state.orders || []).forEach(function (order) {
			if (!order.type || order.type === 'Wait') return;
			var color = order.pending ? COLORS.pending : null;
			var from = order.terrID ? self.position(order.terrID) : null;
			var failed = (order.success === false);

			switch (order.type) {
				case 'Hold':
					if (from && order.pending) self.drawRing(ctx, from, radius + 3, color, 2);
					break;

				case 'Move':
				case 'Retreat': {
					if (!from || !order.toTerrID) break;
					var to = self.position(order.toTerrID);
					var style = { color: color || (order.type === 'Retreat' ? COLORS.retreat : COLORS.move) };
					if (order.viaConvoy === true || order.viaConvoy === 'Yes') style.dash = [radius * 0.6, radius * 0.4];
					self.drawArrow(ctx, from, to, style);
					if (failed) self.drawCross(ctx, along(from, to, 0.7), radius * 0.45, COLORS.fail);
					if (order.type === 'Retreat' && failed) self.drawCross(ctx, from, radius * 0.7, COLORS.fail);
					break;
				}

				case 'Support hold': {
					if (!from || !order.toTerrID) break;
					var held = self.position(order.toTerrID);
					self.drawArrow(ctx, from, held, { color: color || COLORS.support, head: false, dash: [radius * 0.5, radius * 0.35] });
					if (failed) self.drawCross(ctx, along(from, held, 0.6), radius * 0.45, COLORS.fail);
					break;
				}

				case 'Support move':
				case 'Convoy': {
					if (!from || !order.toTerrID || !order.fromTerrID) break;
					// Points a third of the way back along the supported or convoyed move, as map.php draws them
					var moveFrom = self.position(order.fromTerrID);
					var moveTo = self.position(order.toTerrID);
					var target = along(moveTo, moveFrom, 1 / 3);
					var isConvoy = order.type === 'Convoy';
					self.drawArrow(ctx, from, target, {
						color: color || (isConvoy ? COLORS.convoy : COLORS.support),
						dash: isConvoy ? [2, radius * 0.4] : [radius * 0.5, radius * 0.35],
						stopShort: 0
					});
					if (order.pending) {
						// Show the move being supported or convoyed, faintly
						self.drawArrow(ctx, moveFrom, moveTo, { color: 'rgba(0,0,0,0.3)', dash: [3, 4] });
					}
					if (failed) self.drawCross(ctx, along(from, target, 0.6), radius * 0.45, COLORS.fail);
					break;
				}

				case 'Build Army':
				case 'Build Fleet': {
					if (!order.toTerrID) break;
					var at = self.position(order.toTerrID);
					var image = order.type === 'Build Fleet' ? self.images.fleet : self.images.army;
					var builtColor = self.variant.countryColor(order.countryID);
					ctx.save();
					ctx.beginPath();
					ctx.arc(at.x, at.y, radius, 0, Math.PI * 2);
					ctx.fillStyle = rgb(builtColor, 0.9);
					ctx.fill();
					ctx.lineWidth = 2;
					ctx.strokeStyle = color || COLORS.support;
					ctx.setLineDash([3, 3]);
					ctx.stroke();
					ctx.restore();
					ctx.drawImage(image, at.x - image.width / 2, at.y - image.height / 2);
					break;
				}

				case 'Destroy':
				case 'Disband': {
					var where = order.toTerrID ? self.position(order.toTerrID) : from;
					if (!where) break;
					self.drawCross(ctx, where, radius * 0.9, color || COLORS.fail, 4);
					break;
				}

				default:
					break;
			}

			if (order.dislodged && from) self.drawRing(ctx, from, radius + 2, COLORS.fail, 2, [4, 3]);
		});
	};

	return MapCanvas;
})();
