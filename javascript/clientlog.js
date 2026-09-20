/*
    Copyright (C) 2004-2026 Kestas J. Kuliukas

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

/*
 * What the browser sees, sent back to the server: script errors and unhandled promise rejections to
 * api.php?route=client/error, which writes them to the error log beside the server's own, and timings to
 * client/metrics, which status.php lists.
 *
 * Loaded in the head, before everything else, so an error in another script is caught. It has no
 * dependencies for the same reason, and nothing in here may throw: a page is never worth breaking to
 * report on it. Both routes accept a logged-out browser.
 */
var wdClientLog = (function() {
	var MAX_ERRORS_PER_PAGE = 5;

	var reported = {};
	var errorsReported = 0;

	function post(route, payload) {
		try {
			var url = 'api.php?route=' + route;
			var body = JSON.stringify(payload);

			// A beacon still goes when the page is being navigated away from, which is exactly when a
			// page that is failing tends to be closed
			if (navigator.sendBeacon) {
				navigator.sendBeacon(url, new Blob([body], { type: 'application/json' }));
				return;
			}

			var xhr = new XMLHttpRequest();
			xhr.open('POST', url, true);
			xhr.setRequestHeader('Content-Type', 'application/json');
			xhr.send(body);
		} catch (e) {
			// Reporting is never worth an error of its own
		}
	}

	/**
	 * Report one error. The same error is only sent once per page load, and a page which is failing in a
	 * loop stops after MAX_ERRORS_PER_PAGE; the server counts and de-duplicates as well, but a broken page
	 * shouldn't spend the user's bandwidth saying so.
	 */
	function reportError(kind, error) {
		try {
			if (errorsReported >= MAX_ERRORS_PER_PAGE) return;

			var signature = kind + '|' + (error.message || '') + '|' + (error.source || '') + '|' + (error.line || 0);
			if (reported[signature]) return;
			reported[signature] = true;
			errorsReported++;

			post('client/error', {
				kind: kind,
				message: String(error.message || 'Unknown error').slice(0, 1000),
				source: String(error.source || '').slice(0, 500),
				line: error.line || 0,
				column: error.column || 0,
				stack: String(error.stack || '').slice(0, 4000),
				componentStack: String(error.componentStack || '').slice(0, 4000),
				url: String(window.location.href).slice(0, 500)
			});
		} catch (e) {
			// As above
		}
	}

	/**
	 * Add to one of the counters status.php lists. The name has to be one the server knows
	 * (libMetrics::clientParts()); anything else is ignored there.
	 */
	function metric(name, ms) {
		post('client/metrics', { metrics: [{ name: name, ms: (typeof ms === 'number' && isFinite(ms)) ? Math.round(ms) : null }] });
	}

	window.addEventListener('error', function(event) {
		// An error on an element (a failed image or script tag) has no message and isn't ours to fix
		if (!event || !event.message) return;

		reportError('script', {
			message: event.message,
			source: event.filename,
			line: event.lineno,
			column: event.colno,
			stack: event.error && event.error.stack
		});
	});

	window.addEventListener('unhandledrejection', function(event) {
		var reason = event ? event.reason : null;

		reportError('promise', {
			message: (reason && (reason.message || reason.toString())) || 'Unhandled promise rejection',
			stack: reason && reason.stack
		});
	});

	// How long this page took as the browser saw it, which includes the network and the user's machine;
	// the server's own METRICS_PAGE_* time is only how long it took to build the HTML.
	window.addEventListener('load', function() {
		try {
			if (typeof wdClientPage === 'undefined' || !window.performance) return;

			// setTimeout because loadEventEnd isn't set until the load event has finished
			window.setTimeout(function() {
				var loadTime = null;

				if (window.performance.getEntriesByType) {
					var navigation = window.performance.getEntriesByType('navigation')[0];
					if (navigation && navigation.loadEventEnd > 0) loadTime = navigation.loadEventEnd;
				}
				if (loadTime === null && window.performance.timing) {
					var timing = window.performance.timing;
					if (timing.loadEventEnd > 0 && timing.navigationStart > 0)
						loadTime = timing.loadEventEnd - timing.navigationStart;
				}

				if (loadTime !== null && loadTime > 0) metric(wdClientPage, loadTime);
			}, 0);
		} catch (e) {
			// As above
		}
	});

	return { error: reportError, metric: metric };
})();
