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
 * Web Push (PWA) notifications for the classic UI.
 *
 * Only included when push is enabled for the user, with window.wD_pushConfig set by the page
 * (libPush::pageConfigScript()) to the VAPID public key and whether this browser's subscription is
 * registered. This script:
 * - offers notifications with a small banner, if this browser isn't subscribed and the offer hasn't
 *   been dismissed
 * - checks that a subscribed browser still holds the subscription it was registered with, updating
 *   the site's copy if the push service replaced it, or removing it if notifications were blocked;
 *   no request is made unless something changed
 * - runs the push notification buttons on the settings page (usercp.php)
 * Note that on iOS window.Notification only exists once the site has been added to the home screen,
 * so nothing is offered in a plain iOS Safari tab.
 */
(function() {
	'use strict';

	var config = window.wD_pushConfig;
	if( !config )
		return;

	var supported = ('serviceWorker' in navigator) && ('PushManager' in window) && ('Notification' in window);

	var DISMISSED_KEY = 'wD_pushPromptDismissed';
	// The endpoint of the subscription this browser was last registered with, to spot it changing
	var ENDPOINT_KEY = 'wD_pushEndpoint';

	// localStorage can be unavailable, or throw, e.g. in private windows or with site data blocked
	function storageGet(key) {
		try { return window.localStorage.getItem(key); } catch(e) { return null; }
	}
	function storageSet(key, value) {
		try { window.localStorage.setItem(key, value); } catch(e) {}
	}
	function storageRemove(key) {
		try { window.localStorage.removeItem(key); } catch(e) {}
	}

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

	function postJSON(route, data) {
		return fetch('/api.php?route=' + route, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify(data)
		}).then(function(response) { return response.json(); });
	}

	// Register a browser subscription with the site. With resync the site only accepts it in place of the
	// subscription this browser was registered with, so a browser unsubscribed from another device stays so.
	function registerSubscription(subscription, resync) {
		var json = subscription.toJSON();
		return postJSON('push/subscribe', {
			endpoint: json.endpoint,
			p256dh: json.keys.p256dh,
			auth: json.keys.auth,
			resync: resync ? 1 : 0
		}).then(function(result) {
			if( result.success && result.data.subscribed )
				storageSet(ENDPOINT_KEY, json.endpoint);
			return result;
		});
	}

	// Subscribe this browser and register it for the user, asking for permission if it hasn't been given.
	// Browsers only allow the permission prompt in response to a click.
	function subscribe() {
		return Notification.requestPermission().then(function(permission) {
			if( permission !== 'granted' )
				throw new Error('Notification permission ' + permission);
			return navigator.serviceWorker.register('/service-worker.js', { scope: '/' });
		}).then(function() {
			// Subscribing needs an active service worker, which a new registration may not have yet
			return navigator.serviceWorker.ready;
		}).then(function(registration) {
			return registration.pushManager.subscribe({
				userVisibleOnly: true,
				applicationServerKey: urlBase64ToUint8Array(config.vapidPublicKey)
			});
		}).then(function(subscription) {
			return registerSubscription(subscription, false);
		}).then(function(result) {
			if( !result.success || !result.data.subscribed )
				throw new Error(result.msg);
			return result;
		});
	}

	// This browser's push subscription, or null
	function getSubscription() {
		return navigator.serviceWorker.getRegistration('/').then(function(registration) {
			return registration ? registration.pushManager.getSubscription() : null;
		});
	}

	// Unsubscribe this browser at its push service, so nothing more can be sent to it
	function unsubscribeBrowser() {
		return getSubscription().then(function(subscription) {
			return subscription ? subscription.unsubscribe() : false;
		}).catch(function() {}).then(function() {
			storageRemove(ENDPOINT_KEY);
		});
	}

	function checkSubscription() {
		getSubscription().then(function(subscription) {
			if( !subscription || Notification.permission !== 'granted' )
				// Notifications were blocked or reset for the site, so nothing can be delivered here any more
				return postJSON('push/unsubscribe', { endpoint: null });
			if( subscription.endpoint !== storageGet(ENDPOINT_KEY) )
				// The push service replaced the subscription, or it was registered before it was tracked here
				return registerSubscription(subscription, true);
		}).catch(function() {});
	}

	function showEnableBanner() {
		if( storageGet(DISMISSED_KEY) )
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
			subscribe().catch(function() {}).then(function() {
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
			storageSet(DISMISSED_KEY, '1');
			banner.parentNode.removeChild(banner);
		};
		banner.appendChild(dismiss);

		document.body.appendChild(banner);
	}

	// The push notification section of the settings page
	function initSettings(settings) {
		var status = document.getElementById('pushStatus');
		var subscribeButton = document.getElementById('pushSubscribe');
		var unsubscribeForm = document.getElementById('pushUnsubscribeForm');

		if( !supported ) {
			status.textContent = 'This browser can\'t receive push notifications. On an iPhone or iPad, add ' +
				'webDiplomacy to your Home Screen (Share, then Add to Home Screen) and open it from there.';
			return;
		}

		// The form removes every device's subscription from the site; this browser is also unsubscribed at its
		// push service, and not offered the banner again
		unsubscribeForm.onsubmit = function(event) {
			event.preventDefault();
			storageSet(DISMISSED_KEY, '1');
			unsubscribeBrowser().then(function() {
				unsubscribeForm.submit();
			});
		};

		if( Notification.permission === 'denied' ) {
			status.textContent = 'Notifications are blocked for this site in your browser\'s settings; ' +
				'allow them there to subscribe this device.';
			return;
		}

		if( settings.getAttribute('data-subscribed-here') === '1' )
			return;

		subscribeButton.style.display = '';
		subscribeButton.onclick = function() {
			subscribeButton.disabled = true;
			status.textContent = '';
			subscribe().then(function(result) {
				document.getElementById('pushDeviceCount').textContent = result.data.devices;
				document.getElementById('pushThisDevice').textContent = 'including this one';
				document.getElementById('pushUnsubscribe').disabled = false;
				subscribeButton.style.display = 'none';
				status.textContent = 'This device is now subscribed.';
			}).catch(function() {
				subscribeButton.disabled = false;
				status.textContent = ( Notification.permission === 'granted' )
					? 'This device couldn\'t be subscribed; please try again.'
					: 'Notifications weren\'t allowed, so this device wasn\'t subscribed.';
			});
		};
	}

	var settings = document.getElementById('pushSettings');
	if( settings )
		initSettings(settings);

	if( !supported )
		return;

	if( config.subscribedHere )
		checkSubscription();
	else if( !settings && Notification.permission !== 'denied' )
		showEnableBanner(); // Not on the settings page, which has its own button
})();
