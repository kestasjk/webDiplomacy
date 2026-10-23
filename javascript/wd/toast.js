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
 * wD.Toast: short notices stacked in the bottom-left corner of the page, in place of the notices the board used
 * to print at the top of the page ("Game has been processed: click here to refresh").
 *
 *   wD.Toast.show({ text: 'Game processed', actions: [{ label: 'Refresh', onClick: fn }], timeout: 0, key: 'processed' });
 *
 * A toast with a key replaces any earlier toast with the same key. timeout is in milliseconds; 0 keeps the toast
 * until it is dismissed. Styles are in css/global.css (.wdtoast*).
 */
var wD = window.wD || {};
window.wD = wD;

wD.Toast = (function () {
	'use strict';

	var container = null;
	var byKey = {};

	function getContainer() {
		if (container && document.body.contains(container)) return container;
		container = document.createElement('div');
		container.id = 'wdToasts';
		container.className = 'wdtoasts';
		container.setAttribute('role', 'status');
		container.setAttribute('aria-live', 'polite');
		document.body.appendChild(container);
		return container;
	}

	function dismiss(el) {
		if (!el || !el.parentNode) return;
		el.classList.add('wdtoast-leaving');
		setTimeout(function () {
			if (el.parentNode) el.parentNode.removeChild(el);
		}, 250);
	}

	/**
	 * @param {Object} opts { text, html, actions: [{label, onClick}], timeout (ms, default 8000, 0 = sticky), kind:
	 *   'info'|'success'|'error'|'warning', key, closable (default true) }
	 * @returns {{ element: HTMLElement, dismiss: function }}
	 */
	function show(opts) {
		opts = opts || {};
		var root = getContainer();

		if (opts.key && byKey[opts.key]) {
			dismiss(byKey[opts.key]);
			delete byKey[opts.key];
		}

		var el = document.createElement('div');
		el.className = 'wdtoast wdtoast-' + (opts.kind || 'info');

		var body = document.createElement('div');
		body.className = 'wdtoast-body';
		if (opts.html) body.innerHTML = opts.html;
		else body.textContent = opts.text || '';
		el.appendChild(body);

		var handle = {
			element: el,
			dismiss: function () {
				dismiss(el);
				if (opts.key && byKey[opts.key] === el) delete byKey[opts.key];
			}
		};

		if (opts.actions && opts.actions.length) {
			var actions = document.createElement('div');
			actions.className = 'wdtoast-actions';
			opts.actions.forEach(function (action) {
				var button = document.createElement('button');
				button.type = 'button';
				button.className = 'wdtoast-action';
				button.textContent = action.label;
				button.addEventListener('click', function (e) {
					e.preventDefault();
					if (action.onClick) action.onClick(handle);
					if (action.keepOpen !== true) handle.dismiss();
				});
				actions.appendChild(button);
			});
			el.appendChild(actions);
		}

		if (opts.closable !== false) {
			var close = document.createElement('button');
			close.type = 'button';
			close.className = 'wdtoast-close';
			close.setAttribute('aria-label', 'Dismiss');
			close.innerHTML = '&times;';
			close.addEventListener('click', function () { handle.dismiss(); });
			el.appendChild(close);
		}

		root.appendChild(el);
		if (opts.key) byKey[opts.key] = el;

		var timeout = (typeof opts.timeout === 'number') ? opts.timeout : 8000;
		if (timeout > 0) setTimeout(handle.dismiss, timeout);

		return handle;
	}

	return { show: show };
})();
