// webDiplomacy root service worker.
//
// Served from the site root so its scope ('/') covers both the classic PHP pages and the React
// app under /beta/. Registered by javascript/push.js (classic UI) and the beta app's pushSync.
//
// This worker only handles Web Push notifications; it deliberately has no fetch handler and does
// no offline caching, so it cannot interfere with normal page loads. If a service worker is ever
// added to the /beta/ build (e.g. via CRA/workbox) keep it scoped to /beta/ or merge it here.

self.addEventListener('install', function () {
	self.skipWaiting();
});

self.addEventListener('activate', function (event) {
	event.waitUntil(self.clients.claim());
});

self.addEventListener('push', function (event) {
	var data = { title: 'webDiplomacy', body: '', url: '/', tag: undefined };
	if (event.data) {
		try {
			data = Object.assign(data, event.data.json());
		} catch (e) {
			data.body = event.data.text();
		}
	}
	event.waitUntil(self.registration.showNotification(data.title, {
		body: data.body,
		tag: data.tag,
		icon: '/beta/logo192.png',
		badge: '/beta/logo192.png',
		data: { url: data.url }
	}));
});

self.addEventListener('notificationclick', function (event) {
	event.notification.close();
	var url = (event.notification.data && event.notification.data.url) || '/';
	event.waitUntil(
		self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (windowClients) {
			for (var i = 0; i < windowClients.length; i++) {
				var client = windowClients[i];
				if (new URL(client.url).origin === self.location.origin && 'focus' in client) {
					return client.focus().then(function (focused) {
						return 'navigate' in focused ? focused.navigate(url) : focused;
					});
				}
			}
			return self.clients.openWindow(url);
		})
	);
});

// The push service can invalidate a subscription and issue a replacement; re-register it
// server-side if possible. If the session cookie is gone this fails silently, and the
// per-page-load re-sync in push.js will repair the subscription next time the user visits.
self.addEventListener('pushsubscriptionchange', function (event) {
	var resubscribe = self.registration.pushManager
		.subscribe(event.oldSubscription ? event.oldSubscription.options : { userVisibleOnly: true })
		.then(function (subscription) {
			var json = subscription.toJSON();
			return fetch('/api.php?route=push/subscribe', {
				method: 'POST',
				credentials: 'include',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({
					endpoint: json.endpoint,
					p256dh: json.keys.p256dh,
					auth: json.keys.auth
				})
			});
		})
		.catch(function () { /* repaired on next page load instead */ });
	event.waitUntil(resubscribe);
});
