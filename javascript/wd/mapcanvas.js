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
 * wD.MapCanvas: draws a variant's map on a <canvas> exactly as map.php and map/drawMap.php draw it (the same
 * images, the same colours, arrow shapes, flags and markers), at the image's natural size, and says which territory
 * a click landed on. It knows nothing about games or orders beyond what it is told to draw, so the board, the
 * sandbox set-up page and the registration challenge can all use it.
 *
 *   var map = new wD.MapCanvas(container, variant, { size: 'small' });
 *   map.load().then(function () {
 *     map.setScene({ ownership: {6: 1}, ops: [{ op: 'unit', terrID: 6, type: 'Fleet', countryID: 1 }] });
 *     map.onClick = function (hit, event) { ... hit.terrID, hit.coastTerrID ... };
 *   });
 *
 * The map images are colour-coded, each land territory one colour of its own, which is how map.php recolours a
 * territory: the same colours give each pixel's territory here (seas may share a colour, and are told apart by the
 * nearest territory position). Everything is drawn in the image's own coordinates, rendered at the device's pixel
 * ratio so that it stays sharp.
 *
 * A scene, given to setScene(), is:
 *   ownership   { provinceID: countryID }   who owns each land province; land not listed is neutral (country 0)
 *   standoffs   [provinceID]                 where a standoff cross is drawn
 *   highlights  { terrID: 'selected' | 'target' | 'source' | 'invalid' }   territories tinted whole
 *   ops         drawing operations, in order, as map.php makes them:
 *     { op: 'arrow', kind: 'Move'|'MoveGrey'|'Retreat'|'Support hold'|'Support move'|'Convoy', from, to, moveFrom,
 *       moveTo, success }   (Support move and Convoy point a third of the way back along moveFrom -> moveTo)
 *     { op: 'unit', terrID, type: 'Army'|'Fleet', countryID, flag: true|false|undefined }  (flag by default when the
 *       province isn't the unit's country's, as map.php does)
 *     { op: 'dislodgedUnit', terrID, type, countryID }  a dislodged unit, drawn on its dislodgement marker to the
 *       upper right of the territory, so that it can be seen and clicked beside the unit which took its place
 *     { op: 'destroyed', terrID }   { op: 'dislodged', terrID }   { op: 'created', terrID, unitType }
 *     { op: 'caption', text }
 *
 * MapCanvas.composeMoves() and MapCanvas.previewOps() build the ops for a turn's archived moves and for orders being
 * entered, as map.php's two drawing loops do.
 */
var wD = window.wD || {};
window.wD = wD;

wD.MapCanvas = (function () {
	'use strict';

	var MAX_RENDER_SCALE = 3;

	// map/drawMap.php's $orderArrows; two-value arrays are [small map, large map]
	var ARROWS = {
		'Move':         { color: [196, 32, 0],    thickness: [2, 4], headAngle: Math.PI / 7, headStart: 0.1, headLength: [12, 30] },
		'MoveGrey':     { color: [100, 100, 100], thickness: [2, 4], headAngle: Math.PI / 7, headStart: 0.1, headLength: [12, 30] },
		'Support hold': { color: [67, 206, 16],   thickness: [2, 4], headAngle: Math.PI / 2, headStart: 0.2, headLength: [8, 24] },
		'Support move': { color: [249, 249, 47],  thickness: [2, 4], headAngle: Math.PI / 7, headStart: 0.4, headLength: [12, 30] },
		'Convoy':       { color: [4, 113, 160],   thickness: [2, 4], headAngle: Math.PI / 7, headStart: 0.1, headLength: [0, 0] },
		'Retreat':      { color: [198, 39, 159],  thickness: [2, 4], headAngle: Math.PI / 7, headStart: 0.1, headLength: [12, 30] }
	};
	var BORDER = [0, 0, 0];
	var STANDOFF = [200, 20, 20];
	var NEUTRAL = [226, 198, 158];

	// The tints laid over a whole territory
	var TINTS = {
		selected: [255, 255, 255, 0.55],
		target: [40, 170, 40, 0.42],
		source: [40, 110, 230, 0.42],
		invalid: [230, 30, 30, 0.5],
		hover: [0, 0, 0, 0.13]
	};

	function loadImage(url) {
		return new Promise(function (resolve, reject) {
			var image = new Image();
			image.onload = function () { resolve(image); };
			image.onerror = function () { reject(new Error('Could not load ' + url)); };
			image.src = url;
		});
	}

	/** The unit and name overlays use white for transparency, as drawMap.php's setTransparancy() does */
	function whiteToTransparent(image) {
		var canvas = document.createElement('canvas');
		canvas.width = image.width;
		canvas.height = image.height;
		var ctx = canvas.getContext('2d');
		ctx.drawImage(image, 0, 0);
		var imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
		var data = imageData.data;
		for (var i = 0; i < data.length; i += 4) {
			if (data[i] === 255 && data[i + 1] === 255 && data[i + 2] === 255) data[i + 3] = 0;
		}
		ctx.putImageData(imageData, 0, 0);
		return canvas;
	}

	function rgb(color) {
		return 'rgb(' + color[0] + ',' + color[1] + ',' + color[2] + ')';
	}

	/** drawMap.php's rotate(): x,y,x,y... coordinates turned about a point, y growing downwards */
	function rotate(coords, around, rad) {
		var cos = Math.cos(rad);
		var sin = Math.sin(rad);
		var out = coords.slice();
		for (var i = 0; i < out.length; i += 2) {
			var x = out[i] - around[0];
			var y = out[i + 1] - around[1];
			out[i] = x * cos - y * sin + around[0];
			out[i + 1] = x * sin + y * cos + around[1];
		}
		return out;
	}

	/** drawMap.php's polygonMap(): an n-pointed symmetrical polygon about 0,0, every other point at the inner radius */
	function polygonMap(corners, outerRadius, innerRadius) {
		var coords = [];
		for (var i = 0; i < corners; i++) {
			coords.push(0);
			coords.push((i % 2 !== 0 && innerRadius) ? innerRadius : outerRadius);
			coords = rotate(coords, [0, 0], 2 * Math.PI / corners);
		}
		return coords;
	}

	function MapCanvas(container, variant, options) {
		options = options || {};
		this.container = container;
		this.variant = variant;
		this.siteRoot = options.siteRoot || '';
		this.size = options.size === 'large' ? 'large' : 'small';

		this.scene = { ownership: {}, standoffs: [], highlights: {}, ops: [] };
		this.hoverTerrID = null;
		this.onClick = null;
		this.onHover = null;

		this.images = null;
		this.index = null;
		this.bounds = null;
		this.baseCanvas = null;
		this.baseKey = null;
		this.tintCanvas = null;
		this.tintKey = null;
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

		var urls = [spec.url, spec.army, spec.fleet, spec.namesUrl].map(function (url) {
			return url ? self.siteRoot + url : null;
		});
		return Promise.all(urls.map(function (url) { return url ? loadImage(url) : Promise.resolve(null); })).then(function (images) {
			self.images = {
				map: images[0],
				army: whiteToTransparent(images[1]),
				fleet: whiteToTransparent(images[2]),
				names: images[3] ? whiteToTransparent(images[3]) : null
			};
			self.width = images[0].width;
			self.height = images[0].height;
			self.buildIndex();
			self.baseKey = null;
			self.tintKey = null;
			self.loaded = true;
			self.fitCanvas();
			self.draw();
		});
	};

	MapCanvas.prototype.destroy = function () {
		if (this.root.parentNode) this.root.parentNode.removeChild(this.root);
		this.loaded = false;
	};

	MapCanvas.prototype.isLarge = function () {
		return this.size === 'large';
	};

	/**
	 * Which territory each pixel belongs to. Each land territory has a colour of its own, found at its position;
	 * territories sharing a colour (the seas) are told apart by whichever is nearest. Pixels of no territory's
	 * colour (borders, names, land that isn't a territory) are 0. Each territory's bounding box is kept for tinting.
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
		this.baseImageData = ctx.getImageData(0, 0, width, height);
		var data = this.baseImageData.data;

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
		var bounds = {};
		for (var y = 0; y < height; y++) {
			for (var x = 0; x < width; x++) {
				var p = y * width + x;
				var i = p * 4;
				var candidates = byColor[(data[i] << 16) | (data[i + 1] << 8) | data[i + 2]];
				if (!candidates) continue;
				var terrID = candidates[0];
				if (candidates.length > 1) {
					var bestDistance = Infinity;
					for (var c = 0; c < candidates.length; c++) {
						var pos = positions[candidates[c]];
						var dx = pos.x - x;
						var dy = pos.y - y;
						var distance = dx * dx + dy * dy;
						if (distance < bestDistance) { bestDistance = distance; terrID = candidates[c]; }
					}
				}
				index[p] = terrID;
				var box = bounds[terrID];
				if (!box) bounds[terrID] = { minX: x, minY: y, maxX: x, maxY: y };
				else {
					if (x < box.minX) box.minX = x;
					if (x > box.maxX) box.maxX = x;
					if (y < box.minY) box.minY = y;
					if (y > box.maxY) box.maxY = y;
				}
			}
		}
		this.index = index;
		this.bounds = bounds;
		this.positions = positions;
	};

	/* Geometry */

	/**
	 * The canvas is shown at the image's natural size (shrunk only when the page is narrower) and rendered at the
	 * device's pixel ratio so that lines stay sharp. Setting a canvas's size wipes it, so it is only set when it
	 * changes.
	 * @returns {boolean} Whether the size changed, and the map needs drawing again
	 */
	MapCanvas.prototype.fitCanvas = function () {
		if (!this.images) return false;
		var scale = Math.min(MAX_RENDER_SCALE, Math.max(1, window.devicePixelRatio || 1));
		var width = Math.round(this.width * scale);
		var height = Math.round(this.height * scale);
		this.canvas.style.width = this.width + 'px';
		if (scale === this.renderScale && this.canvas.width === width && this.canvas.height === height) return false;
		this.renderScale = scale;
		this.canvas.width = width;
		this.canvas.height = height;
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
			self.onClick(hit, e, self);
		});
		this.canvas.addEventListener('mousemove', function (e) {
			if (!self.loaded) return;
			var hit = self.hitTest(e.clientX, e.clientY);
			var terrID = hit ? hit.terrID : null;
			if (terrID !== self.hoverTerrID) {
				self.hoverTerrID = terrID;
				if (self.onHover) self.onHover(hit, self);
				self.draw();
			}
		});
		this.canvas.addEventListener('mouseleave', function () {
			if (self.hoverTerrID !== null) {
				self.hoverTerrID = null;
				if (self.onHover) self.onHover(null, self);
				self.draw();
			}
		});
		window.addEventListener('resize', function () {
			if (self.loaded && self.fitCanvas()) self.draw();
		});
	};

	/* Scene and drawing */

	MapCanvas.prototype.setScene = function (scene) {
		var self = this;
		Object.keys(scene).forEach(function (key) { self.scene[key] = scene[key]; });
		if (this.loaded) this.draw();
	};

	/**
	 * The map with every land territory coloured by its owner, neutral land in the variant's neutral colour, as
	 * map.php's colorTerritory() loop leaves it; redone only when the ownership changes. Recoloured for the
	 * colour-blind as the map image was (javascript/Color.Vision.Daltonize.js, from the user's option).
	 */
	MapCanvas.prototype.baseLayer = function () {
		var ownership = this.scene.ownership || {};
		var key = JSON.stringify(ownership);
		if (this.baseCanvas && this.baseKey === key) return this.baseCanvas;

		var self = this;
		var width = this.width;
		var height = this.height;
		var imageData = new ImageData(new Uint8ClampedArray(this.baseImageData.data), width, height);
		var data = imageData.data;
		var index = this.index;

		var colorByTerr = {};
		Object.keys(this.bounds).forEach(function (terrID) {
			terrID = parseInt(terrID, 10);
			var territory = self.variant.territory(terrID);
			if (!territory || territory.type === 'Sea') return;
			colorByTerr[terrID] = self.variant.countryColor(ownership[terrID] || 0) || NEUTRAL;
		});

		for (var p = 0, i = 0; p < index.length; p++, i += 4) {
			var color = index[p] ? colorByTerr[index[p]] : undefined;
			if (!color) continue;
			data[i] = color[0];
			data[i + 1] = color[1];
			data[i + 2] = color[2];
		}

		var canvas = document.createElement('canvas');
		canvas.width = width;
		canvas.height = height;
		canvas.getContext('2d').putImageData(imageData, 0, 0);

		var options = window.useroptions;
		if (options && options.colourblind && options.colourblind !== 'No' && window.Color && window.Color.Vision && window.Color.Vision.Daltonize) {
			try {
				window.Color.Vision.Daltonize(canvas, { type: options.colourblind, callback: function (c) { canvas = c; } });
			} catch (e) { /* the plain colours then */ }
		}

		this.baseCanvas = canvas;
		this.baseKey = key;
		return canvas;
	};

	/** The highlighted territories, and the one under the pointer, tinted whole; redone only when they change */
	MapCanvas.prototype.tintLayer = function () {
		var highlights = this.scene.highlights || {};
		var self = this;
		var tints = {};
		Object.keys(highlights).forEach(function (terrID) {
			var provinceID = self.variant.provinceID(parseInt(terrID, 10));
			tints[provinceID] = highlights[terrID];
		});
		if (this.hoverTerrID && !tints[this.hoverTerrID]) tints[this.hoverTerrID] = 'hover';

		var keys = Object.keys(tints);
		if (!keys.length) return null;
		var key = JSON.stringify(tints);
		if (this.tintCanvas && this.tintKey === key) return this.tintCanvas;

		var width = this.width;
		var height = this.height;
		var imageData = new ImageData(width, height);
		var data = imageData.data;
		var index = this.index;
		keys.forEach(function (terrID) {
			terrID = parseInt(terrID, 10);
			var box = self.bounds[terrID];
			var tint = TINTS[tints[terrID]] || TINTS.target;
			if (!box) return;
			var alpha = Math.round(tint[3] * 255);
			for (var y = box.minY; y <= box.maxY; y++) {
				for (var x = box.minX; x <= box.maxX; x++) {
					var p = y * width + x;
					if (index[p] !== terrID) continue;
					var i = p * 4;
					data[i] = tint[0];
					data[i + 1] = tint[1];
					data[i + 2] = tint[2];
					data[i + 3] = alpha;
				}
			}
		});
		var canvas = document.createElement('canvas');
		canvas.width = width;
		canvas.height = height;
		canvas.getContext('2d').putImageData(imageData, 0, 0);
		this.tintCanvas = canvas;
		this.tintKey = key;
		return canvas;
	};

	MapCanvas.prototype.draw = function () {
		if (!this.loaded) return;
		var self = this;
		var ctx = this.canvas.getContext('2d');
		var scale = this.renderScale;
		ctx.setTransform(1, 0, 0, 1, 0, 0);
		ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
		ctx.setTransform(scale, 0, 0, scale, 0, 0);
		ctx.imageSmoothingEnabled = true;

		ctx.drawImage(this.baseLayer(), 0, 0);
		var tint = this.tintLayer();
		if (tint) ctx.drawImage(tint, 0, 0);

		(this.scene.standoffs || []).forEach(function (provinceID) { self.drawStandoff(ctx, self.position(provinceID)); });
		(this.scene.ops || []).forEach(function (op) { self.drawOp(ctx, op); });

		// Territory names go on last, as map.php's addTerritoryNames() does
		if (this.images.names) ctx.drawImage(this.images.names, 0, 0);
	};

	MapCanvas.prototype.drawOp = function (ctx, op) {
		switch (op.op) {
			case 'arrow': this.drawArrowOp(ctx, op); break;
			case 'unit': this.drawUnit(ctx, op); break;
			case 'dislodgedUnit': this.drawDislodgedUnit(ctx, op); break;
			case 'destroyed': this.drawDestroyed(ctx, this.position(op.terrID)); break;
			case 'dislodged': if (this.isLarge()) this.drawDislodgedMarker(ctx, this.position(op.terrID), false); break;
			case 'created': this.drawCreated(ctx, op); break;
			case 'caption': this.drawCaption(ctx, op.text); break;
			default: break;
		}
	};

	/* Lines */

	/** drawMap.php's imagelinethick(): a straight line of a thickness, with flat ends */
	MapCanvas.prototype.line = function (ctx, x1, y1, x2, y2, color, thickness, cap) {
		ctx.save();
		ctx.strokeStyle = rgb(color);
		ctx.lineWidth = thickness;
		ctx.lineCap = cap || 'butt';
		ctx.beginPath();
		ctx.moveTo(x1, y1);
		ctx.lineTo(x2, y2);
		ctx.stroke();
		ctx.restore();
	};

	/**
	 * drawMap.php's drawOrderArrow(): the shaft, then a head of two lines meeting a little way back from the end
	 * (further back for support-moves, a bar across the line for support-holds, no head for convoys)
	 */
	MapCanvas.prototype.drawOrderArrow = function (ctx, start, end, kind) {
		var params = ARROWS[kind];
		if (!params) return;
		var sizeIndex = this.isLarge() ? 1 : 0;
		var thickness = params.thickness[sizeIndex];
		var headLength = params.headLength[sizeIndex];
		var color = params.color;

		var headStartX = end.x - (end.x - start.x) * params.headStart;
		var headStartY = end.y - (end.y - start.y) * params.headStart;
		var rad = Math.atan2(end.y - start.y, end.x - start.x);

		var head = rotate([
			0, 0,
			-headLength * Math.cos(params.headAngle), -headLength * Math.sin(params.headAngle),
			-headLength * Math.cos(params.headAngle), headLength * Math.sin(params.headAngle)
		], [0, 0], rad);
		for (var i = 0; i < 6; i += 2) {
			head[i] += headStartX;
			head[i + 1] += headStartY;
		}

		if (params.border) {
			this.line(ctx, start.x, start.y, end.x, end.y, BORDER, params.border);
			if (headLength) {
				this.line(ctx, head[2], head[3], headStartX, headStartY, BORDER, params.border);
				this.line(ctx, head[4], head[5], headStartX, headStartY, BORDER, params.border);
			}
		}
		this.line(ctx, start.x, start.y, end.x, end.y, color, thickness);
		if (headLength) {
			this.line(ctx, head[2], head[3], headStartX, headStartY, color, thickness);
			this.line(ctx, head[4], head[5], headStartX, headStartY, color, thickness);
		}
	};

	/** drawMap.php's drawFailure(): a cross a third of the way along a failed order's arrow */
	MapCanvas.prototype.drawFailure = function (ctx, from, to) {
		var height = this.images.army.height / 2;
		var width = this.images.army.width / 2;
		var coords = [height, width, -height, -width, height, -width, -height, width];
		var rad = Math.atan2(to.y - from.y, to.x - from.x);
		coords = rotate(coords, [0, 0], rad + Math.PI);
		var x = from.x - (from.x - to.x) / 3;
		var y = from.y - (from.y - to.y) / 3;
		for (var i = 0; i < 8; i += 2) {
			coords[i] += x;
			coords[i + 1] += y;
		}
		var thickness = this.isLarge() ? 6 : 2;
		this.line(ctx, coords[0], coords[1], coords[2], coords[3], STANDOFF, thickness);
		this.line(ctx, coords[4], coords[5], coords[6], coords[7], STANDOFF, thickness);
	};

	/** The arrow ops: map.php's drawMove / drawRetreat / drawSupportHold / drawSupportMove / drawConvoy */
	MapCanvas.prototype.drawArrowOp = function (ctx, op) {
		var from = this.position(op.from);
		var to;
		var success = op.success !== false;

		switch (op.kind) {
			case 'Move':
			case 'MoveGrey':
			case 'Retreat':
				if (!op.to) return;
				to = this.position(op.to);
				this.drawOrderArrow(ctx, from, to, op.kind);
				if (!success) {
					this.drawFailure(ctx, from, to);
					if (op.kind === 'Retreat') this.drawDestroyed(ctx, from);
				}
				break;

			case 'Support hold':
				// map.php leaves failed supports and convoys off the small map
				if (!op.to || (!this.isLarge() && !success)) return;
				to = this.position(op.to);
				this.drawOrderArrow(ctx, from, to, 'Support hold');
				if (!success) this.drawFailure(ctx, from, to);
				break;

			case 'Support move':
			case 'Convoy': {
				if (!op.moveFrom || !op.moveTo || (!this.isLarge() && !success)) return;
				// Points a third of the way back along the supported or convoyed move
				var moveFrom = this.position(op.moveFrom);
				var moveTo = this.position(op.moveTo);
				to = { x: moveTo.x - (moveTo.x - moveFrom.x) / 3, y: moveTo.y - (moveTo.y - moveFrom.y) / 3 };
				this.drawOrderArrow(ctx, from, to, op.kind);
				if (!success) this.drawFailure(ctx, from, to);
				break;
			}

			default:
				break;
		}
	};

	/* Units and markers */

	/** drawMap.php's addUnit(): the icon centred on the territory */
	MapCanvas.prototype.drawUnitIcon = function (ctx, type, x, y) {
		var image = type === 'Fleet' ? this.images.fleet : this.images.army;
		ctx.drawImage(image, x - Math.floor(image.width / 2), y - Math.floor(image.height / 2));
	};

	/** drawMap.php's countryFlag(): a small square of the country's colour above the unit's position */
	MapCanvas.prototype.drawFlag = function (ctx, countryID, x, y) {
		var fleet = this.images.fleet;
		var left = x - Math.floor(fleet.width / 2);
		var top = y - fleet.height;
		var right = x + Math.floor(fleet.width / 2);
		var bottom = y;
		ctx.save();
		ctx.fillStyle = rgb(BORDER);
		ctx.fillRect(left, top, right - left + 1, bottom - top + 1);
		ctx.fillStyle = rgb(this.variant.countryColor(countryID) || NEUTRAL);
		ctx.fillRect(left + 1, top + 1, right - left - 1, bottom - top - 1);
		ctx.restore();
	};

	/** Whether a unit here would get its flag: when the province isn't its country's (the sea never is) */
	MapCanvas.prototype.needsFlag = function (terrID, countryID) {
		var provinceID = this.variant.provinceID(terrID);
		var owner = this.variant.isSea(provinceID) ? 0 : ((this.scene.ownership || {})[provinceID] || 0);
		return owner !== countryID;
	};

	MapCanvas.prototype.drawUnit = function (ctx, op) {
		var pos = this.position(op.terrID);
		var flag = op.flag === undefined ? this.needsFlag(op.terrID, op.countryID) : !!op.flag;
		if (flag) this.drawFlag(ctx, op.countryID, pos.x, pos.y);
		this.drawUnitIcon(ctx, op.type, pos.x, pos.y);
	};

	/** drawMap.php's drawPolygon(): filled, about a position, optionally at the "small" offset to the upper right */
	MapCanvas.prototype.fillPolygon = function (ctx, pos, color, polygon, small) {
		var x = pos.x;
		var y = pos.y;
		if (small) {
			x += this.images.army.width / 2;
			y -= this.images.army.height / 2;
		}
		ctx.save();
		ctx.fillStyle = rgb(color);
		ctx.beginPath();
		for (var i = 0; i < polygon.length; i += 2) {
			if (i === 0) ctx.moveTo(polygon[i] + x, polygon[i + 1] + y);
			else ctx.lineTo(polygon[i] + x, polygon[i + 1] + y);
		}
		ctx.closePath();
		ctx.fill();
		ctx.restore();
	};

	/** drawMap.php's drawDestroyedUnit(): the orange and red explosion over a destroyed unit */
	MapCanvas.prototype.drawDestroyed = function (ctx, pos) {
		var size = this.images.army.width / 2;
		this.fillPolygon(ctx, pos, BORDER, polygonMap(14, size, size / 2));
		this.fillPolygon(ctx, pos, BORDER, rotate(polygonMap(14, size - 1, (size - 1) / 2), [0, 0], Math.PI / 7));
		this.fillPolygon(ctx, pos, [255, 100, 0], polygonMap(14, size - 2, (size - 2) / 2));
		this.fillPolygon(ctx, pos, [255, 0, 0], rotate(polygonMap(14, size - 3, (size - 3) / 2), [0, 0], Math.PI / 7));
	};

	/**
	 * drawMap.php's drawDislodgedUnit(): the small explosion to the upper right of the unit which took a dislodged
	 * unit's place. map.php leaves it off the small map, where it would be too small; when a unit is to be drawn on
	 * it (enlarge) it is made big enough to be seen there.
	 */
	MapCanvas.prototype.drawDislodgedMarker = function (ctx, pos, enlarge) {
		var size = this.images.army.height / 2;
		if (enlarge && !this.isLarge()) size = Math.max(size, 7);
		this.fillPolygon(ctx, pos, BORDER, polygonMap(10, size, size / 2), true);
		this.fillPolygon(ctx, pos, BORDER, rotate(polygonMap(10, size - 2, (size - 2) / 2), [0, 0], Math.PI / 5), true);
		this.fillPolygon(ctx, pos, [255, 150, 0], polygonMap(10, size - 3, (size - 3) / 2), true);
		this.fillPolygon(ctx, pos, [255, 80, 0], rotate(polygonMap(10, size - 4, (size - 4) / 2), [0, 0], Math.PI / 5), true);
	};

	/** A dislodged unit awaiting its retreat: on its marker, beside the unit which took its place */
	MapCanvas.prototype.drawDislodgedUnit = function (ctx, op) {
		var pos = this.position(op.terrID);
		this.drawDislodgedMarker(ctx, pos, true);
		var x = pos.x + this.images.army.width / 2;
		var y = pos.y - this.images.army.height / 2;
		this.drawFlag(ctx, op.countryID, x, y);
		this.drawUnitIcon(ctx, op.type, x, y);
	};

	/** drawMap.php's drawCreatedUnit(): the yellow star to the upper right of a newly built unit, and the unit */
	MapCanvas.prototype.drawCreated = function (ctx, op) {
		var pos = this.position(op.terrID);
		var size = this.images.army.height / 2;
		this.fillPolygon(ctx, pos, BORDER, rotate(polygonMap(10, size, size / 2), [0, 0], Math.PI), true);
		this.fillPolygon(ctx, pos, [255, 255, 0], rotate(polygonMap(10, size - 2, size / 2 - 1), [0, 0], Math.PI), true);
		if (op.unitType) {
			if (op.countryID !== undefined && this.needsFlag(op.terrID, op.countryID)) this.drawFlag(ctx, op.countryID, pos.x, pos.y);
			this.drawUnitIcon(ctx, op.unitType, pos.x, pos.y);
		}
	};

	/** drawMap.php's drawStandoff(): a red cross with a black edge */
	MapCanvas.prototype.drawStandoff = function (ctx, pos) {
		var width = this.images.army.width / 2;
		var offset = Math.round(width / 2);
		var outer = this.isLarge() ? 9 : 5;
		var inner = this.isLarge() ? 6 : 4;
		var x = pos.x;
		var y = pos.y;
		this.line(ctx, x - offset, y - offset, x + offset, y + offset, BORDER, outer, 'square');
		this.line(ctx, x - offset, y + offset, x + offset, y - offset, BORDER, outer, 'square');
		this.line(ctx, x - offset, y - offset, x + offset, y + offset, STANDOFF, inner, 'square');
		this.line(ctx, x - offset, y + offset, x + offset, y - offset, STANDOFF, inner, 'square');
	};

	/** drawMap.php's caption(): a line of large text at the top right, for a finished game's result */
	MapCanvas.prototype.drawCaption = function (ctx, text) {
		if (!text) return;
		ctx.save();
		ctx.font = 'bold ' + (this.isLarge() ? 28 : 16) + 'px Arial, Helvetica, sans-serif';
		ctx.textAlign = 'right';
		ctx.textBaseline = 'top';
		ctx.lineWidth = 3;
		ctx.strokeStyle = '#fff';
		ctx.fillStyle = '#000';
		var x = this.width - 10;
		var y = 8;
		ctx.strokeText(text, x, y);
		ctx.fillText(text, x, y);
		ctx.restore();
	};

	/* Scenes from a game's data, as map.php composes its two drawing loops */

	/**
	 * map.php's main loop: a turn's archived moves (history.json's orders, every phase of the turn together) into
	 * arrows, the units where they ended up, and the destroyed, dislodged and created markers.
	 *
	 * @param {wD.VariantMap} variant
	 * @param {Object[]} moves [{ type, countryID, unitType, terrID, toTerrID, fromTerrID, success, dislodged }]
	 * @param {Object} options { units: false to leave the units out, when the board has them from elsewhere }
	 * @returns {Object[]} ops
	 */
	MapCanvas.composeMoves = function (variant, moves, options) {
		options = options || {};
		var withUnits = options.units !== false;
		// map.php reads the archive ORDER BY type DESC, which fixes what is drawn over what
		var sorted = moves.slice().sort(function (a, b) {
			var ta = a.type || '';
			var tb = b.type || '';
			return ta < tb ? 1 : (ta > tb ? -1 : 0);
		});

		var ops = [];
		var destroyed = {};
		var dislodged = {};
		var built = {};
		var builtOrder = [];

		sorted.forEach(function (move) {
			var type = move.type;
			var terrID = move.terrID;
			var success = move.success !== false;
			var isDislodged = !!move.dislodged;
			var unitType = move.unitType;
			var drawTo;

			if (type === 'Destroy') {
				destroyed[move.toTerrID || terrID] = true;
				return;
			} else if (isDislodged) {
				dislodged[terrID] = true;
			} else if (type === 'Disband') {
				return;
			}

			if (type === 'Move') {
				if (terrID && move.toTerrID) ops.push({ op: 'arrow', kind: 'Move', from: terrID, to: move.toTerrID, success: success });
				drawTo = success ? move.toTerrID : terrID;
			} else if (type === 'Retreat') {
				if (terrID && move.toTerrID) ops.push({ op: 'arrow', kind: 'Retreat', from: terrID, to: move.toTerrID, success: success });
				if (!success) return;
				drawTo = move.toTerrID;
			} else if ((type === 'Build Army' || type === 'Build Fleet') && success) {
				unitType = type === 'Build Army' ? 'Army' : 'Fleet';
				var at = terrID || move.toTerrID;
				if (!built[at]) builtOrder.push(at);
				built[at] = { unitType: unitType, countryID: move.countryID };
				drawTo = at;
			} else if (!isDislodged) {
				drawTo = terrID;
			}

			if (type === 'Support hold' && terrID && move.toTerrID) {
				ops.push({ op: 'arrow', kind: 'Support hold', from: terrID, to: move.toTerrID, success: success });
			} else if ((type === 'Support move' || type === 'Convoy') && terrID && move.toTerrID && move.fromTerrID) {
				ops.push({ op: 'arrow', kind: type, from: terrID, moveFrom: move.fromTerrID, moveTo: move.toTerrID, success: success });
			}

			if (withUnits && !isDislodged && drawTo && unitType) {
				ops.push({ op: 'unit', terrID: drawTo, type: unitType, countryID: move.countryID });
			}
		});

		Object.keys(destroyed).forEach(function (terrID) { ops.push({ op: 'destroyed', terrID: parseInt(terrID, 10) }); });
		Object.keys(dislodged).forEach(function (terrID) { ops.push({ op: 'dislodged', terrID: parseInt(terrID, 10) }); });
		builtOrder.forEach(function (terrID) {
			ops.push({ op: 'created', terrID: terrID, unitType: withUnits ? built[terrID].unitType : null, countryID: built[terrID].countryID });
		});
		return ops;
	};

	/**
	 * map.php's PREVIEW loop: the orders a player has entered, drawn over the board. Moves come last, so that they
	 * lie over the supports, as map.php's ORDER BY has it.
	 *
	 * @param {Object[]} orders [{ type, countryID, unitType, terrID, toTerrID, fromTerrID }]
	 * @returns {Object[]} ops
	 */
	MapCanvas.previewOps = function (orders) {
		var ops = [];
		var sorted = orders.slice().sort(function (a, b) {
			return (a.type === 'Move' ? 1 : 0) - (b.type === 'Move' ? 1 : 0);
		});
		sorted.forEach(function (order) {
			var type = order.type;
			var terrID = order.terrID;
			var toTerrID = order.toTerrID;
			var fromTerrID = order.fromTerrID;
			switch (type) {
				case 'Move':
					if (terrID && toTerrID) ops.push({ op: 'arrow', kind: 'Move', from: terrID, to: toTerrID, success: true });
					break;
				case 'Support hold':
					if (terrID && toTerrID) ops.push({ op: 'arrow', kind: 'Support hold', from: terrID, to: toTerrID, success: true });
					break;
				case 'Support move':
				case 'Convoy':
					if (terrID && toTerrID && fromTerrID) {
						ops.push({ op: 'arrow', kind: 'MoveGrey', from: fromTerrID, to: toTerrID, success: true });
						ops.push({ op: 'arrow', kind: type, from: terrID, moveFrom: fromTerrID, moveTo: toTerrID, success: true });
					}
					break;
				case 'Build Army':
				case 'Build Fleet':
					if (toTerrID) ops.push({ op: 'created', terrID: toTerrID, unitType: type === 'Build Army' ? 'Army' : 'Fleet', countryID: order.countryID });
					break;
				case 'Retreat':
					if (terrID && toTerrID) ops.push({ op: 'arrow', kind: 'Retreat', from: terrID, to: toTerrID, success: true });
					break;
				case 'Destroy':
					if (toTerrID || terrID) ops.push({ op: 'destroyed', terrID: toTerrID || terrID });
					break;
				case 'Disband':
					if (terrID) ops.push({ op: 'destroyed', terrID: terrID });
					break;
				default:
					break;
			}
		});
		return ops;
	};

	return MapCanvas;
})();
