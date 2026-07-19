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
 * Web Push (PWA) notification registration for the classic UI.
 *
 * Asks the server whether push is enabled for this user (feature-flagged while being trialled;
 * for everyone else this script does nothing), registers the root service worker, and either
 * quietly re-syncs an existing subscription or offers a small banner asking the user to enable
 * notifications. Note that on iOS window.Notification only exists once the site has been added
 * to the home screen, so nothing is shown in a plain iOS Safari tab.
 */
(function() {
	'use strict';

	if( !('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window) )
		return;

	var DISMISSED_KEY = 'wD_pushPromptDismissed';

	// The applicationServerKey must be passed as a Uint8Array of the raw P-256 point
	function urlBase64ToUint8Array(base64String) {
		var padding = '='.repeat((4 - base64String.length % 4) % 4);
		var base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
		var rawData = window.atob(base64);
		var outputArray = new Uint8Array(rawData.length);
		for (var i = 0; i < rawData.length; ++i)
			outputArray[i] = rawData.charCodeAt(i);
		return outputArray;
	}

	function syncSubscription(registration, vapidPublicKey) {
		return registration.pushManager.subscribe({
			userVisibleOnly: true,
			applicationServerKey: urlBase64ToUint8Array(vapidPublicKey)
		}).then(function(subscription) {
			var json = subscription.toJSON();
			return fetch('/api.php?route=push/subscribe', {
				method: 'POST',
				credentials: 'same-origin',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({
					endpoint: json.endpoint,
					p256dh: json.keys.p256dh,
					auth: json.keys.auth
				})
			});
		});
	}

	function showEnableBanner(registration, vapidPublicKey) {
		if( window.localStorage && localStorage.getItem(DISMISSED_KEY) )
			return;

		var banner = document.createElement('div');
		banner.setAttribute('style',
			'position:fixed;bottom:0;left:0;right:0;z-index:10000;padding:10px 14px;' +
			'background:#3d5765;color:#fff;font-size:13px;text-align:center;' +
			'box-shadow:0 -1px 4px rgba(0,0,0,0.3);');

		var text = document.createElement('span');
		text.appendChild(document.createTextNode(
			'Get notified when your games\' turns process and messages arrive. '));
		banner.appendChild(text);

		var enable = document.createElement('button');
		enable.appendChild(document.createTextNode('Enable notifications'));
		enable.setAttribute('style',
			'margin:0 10px;padding:4px 10px;cursor:pointer;border:none;border-radius:3px;' +
			'background:#4CAF50;color:#fff;font-size:13px;');
		enable.onclick = function() {
			// Permission must be requested from a user gesture (required on iOS and Chrome)
			Notification.requestPermission().then(function(permission) {
				if( permission === 'granted' )
					syncSubscription(registration, vapidPublicKey).catch(function() {});
				banner.parentNode.removeChild(banner);
			});
		};
		banner.appendChild(enable);

		var dismiss = document.createElement('button');
		dismiss.appendChild(document.createTextNode('×'));
		dismiss.setAttribute('title', 'Dismiss');
		dismiss.setAttribute('style',
			'padding:4px 8px;cursor:pointer;border:none;border-radius:3px;' +
			'background:transparent;color:#fff;font-size:15px;');
		dismiss.onclick = function() {
			if( window.localStorage )
				localStorage.setItem(DISMISSED_KEY, '1');
			banner.parentNode.removeChild(banner);
		};
		banner.appendChild(dismiss);

		document.body.appendChild(banner);
	}

	fetch('/api.php?route=push/config', { credentials: 'same-origin' })
		.then(function(response) { return response.json(); })
		.then(function(config) {
			if( !config.success || !config.data.enabled )
				return; // Feature not enabled for this user; do nothing at all
			navigator.serviceWorker.register('/service-worker.js', { scope: '/' })
				.then(function(registration) {
					if( Notification.permission === 'granted' )
						// Already opted in; quietly make sure the stored subscription is current
						syncSubscription(registration, config.data.vapidPublicKey).catch(function() {});
					else if( Notification.permission === 'default' )
						showEnableBanner(registration, config.data.vapidPublicKey);
					// 'denied': respect it, never nag
				});
		})
		.catch(function() {});
})();
