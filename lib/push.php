<?php
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

defined('IN_CODE') or die('This script can not be run by itself.');

// These are compile-time aliases and don't trigger the composer autoloader; vendor/autoload.php
// is required inside drain() only once there is something to send, so requests which don't
// send pushes never pay the autoload cost.
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

/**
 * Web Push (PWA) notifications: subscription storage, queueing and sending, gated by
 * Config::$pushEnabledUserIDs while the feature is being trialled.
 *
 * Notifications are never sent while the code that raises them is running. queue() looks up the
 * recipients' subscriptions and adds a job to a Redis list, and the list is drained once the
 * request's response has gone out (see drainAfterResponse()), so neither the sender of a message
 * nor the gamemaster waits on the push services. The drain sends its jobs concurrently, and the
 * results it collects are written back to wD_PushSubscriptions by the gamemaster's background
 * tasks, so the drain never touches the database.
 *
 * Which subscription belongs to this browser is kept in a cookie (COOKIE_NAME), so logging off
 * can remove it and pages can tell whether to offer notifications.
 *
 * @package Base
 */
class libPush
{
	/**
	 * Redis list of queued jobs, each a notification and the subscriptions to send it to
	 */
	const QUEUE_KEY = 'pushQueue';

	/**
	 * Redis key held by the request draining the queue, so only one drains at a time
	 */
	const DRAIN_LOCK_KEY = 'pushDrainLock';

	/**
	 * Redis sets of the endpoint hashes the drain delivered to, and found expired, for applySendResults()
	 */
	const SENT_KEY = 'pushSentEndpoints';
	const EXPIRED_KEY = 'pushExpiredEndpoints';

	/**
	 * Jobs beyond this many are dropped, oldest first, so a queue that isn't being drained can't grow without limit
	 */
	const QUEUE_MAX_JOBS = 10000;

	/**
	 * Seconds a notification is worth delivering: the push services hold it this long for a device that's offline,
	 * and a job still queued after this long is dropped
	 */
	const TTL = 3600;

	/**
	 * Jobs sent per batch. A batch is sent concurrently, and the drain checks its time limit between batches.
	 */
	const DRAIN_BATCH_JOBS = 50;

	/**
	 * Requests to the push services in flight at once
	 */
	const DRAIN_CONCURRENCY = 100;

	/**
	 * Seconds a drain keeps starting new batches; what's left waits for the next drain
	 */
	const DRAIN_TIME_LIMIT = 40;

	/**
	 * Seconds allowed for each request to a push service
	 */
	const REQUEST_TIMEOUT = 5;

	/**
	 * Cookie holding the endpoint hash of the subscription this browser was registered with
	 */
	const COOKIE_NAME = 'wD-PushSub';

	/**
	 * Whether VAPID keys are configured; empty keys disable push site-wide.
	 */
	public static function isConfigured()
	{
		return isset(Config::$vapidPublicKey) && Config::$vapidPublicKey !== ''
			&& isset(Config::$vapidPrivateKey) && Config::$vapidPrivateKey !== ''
			&& isset(Config::$vapidSubject) && Config::$vapidSubject !== '';
	}

	/**
	 * Whether push is enabled for the given user: for everyone when
	 * Config::$pushEnabledUserIDs is true, otherwise only for the userIDs it lists.
	 */
	public static function isEnabledForUser($userID)
	{
		if( !self::isConfigured() ) return false;
		if( !isset(Config::$pushEnabledUserIDs) ) return false;
		if( Config::$pushEnabledUserIDs === true ) return true;
		if( !is_array(Config::$pushEnabledUserIDs) ) return false;
		return in_array(intval($userID), Config::$pushEnabledUserIDs);
	}

	/**
	 * The script tag giving javascript/push.js what it needs for this page, or '' if push isn't enabled for the
	 * user. This goes in the page rather than being fetched so that ordinary page views cost no extra requests.
	 *
	 * @return string
	 */
	public static function pageConfigScript($userID)
	{
		if( !self::isEnabledForUser($userID) ) return '';

		// Only the cookie is checked, not the table, to keep page views free of queries. A subscription removed
		// from another device still has its cookie here, which just means this browser isn't offered the banner.
		return '<script type="text/javascript">window.wD_pushConfig = '.json_encode(array(
			'vapidPublicKey' => Config::$vapidPublicKey,
			'subscribedHere' => self::browserEndpointHash() !== null
		)).';</script>';
	}

	/**
	 * The endpoint hash of the subscription this browser was registered with, from its cookie, or null
	 *
	 * @return string|null
	 */
	public static function browserEndpointHash()
	{
		if( isset($_COOKIE[self::COOKIE_NAME]) && preg_match('/^[0-9a-f]{32}$/', $_COOKIE[self::COOKIE_NAME]) )
			return $_COOKIE[self::COOKIE_NAME];
		return null;
	}

	private static function setBrowserCookie($endpointHash)
	{
		setcookie(self::COOKIE_NAME, $endpointHash,
			['expires'=>(time()+365*24*60*60), 'path'=>'/', 'samesite'=>'Lax', 'httponly'=>true]);
		$_COOKIE[self::COOKIE_NAME] = $endpointHash;
	}

	private static function clearBrowserCookie()
	{
		if( !isset($_COOKIE[self::COOKIE_NAME]) ) return;
		setcookie(self::COOKIE_NAME, '', ['expires'=>(time()-3600), 'path'=>'/', 'samesite'=>'Lax', 'httponly'=>true]);
		unset($_COOKIE[self::COOKIE_NAME]);
	}

	/**
	 * Whether this browser's subscription is registered to the given user
	 */
	public static function isBrowserSubscribed($userID)
	{
		global $DB;

		$endpointHash = self::browserEndpointHash();
		if( $endpointHash === null ) return false;

		list($count) = $DB->sql_row("SELECT COUNT(*) FROM wD_PushSubscriptions
			WHERE userID = ".intval($userID)." AND endpointHash = '".$endpointHash."'");
		return $count > 0;
	}

	/**
	 * The number of devices (browser subscriptions) registered to the given user
	 */
	public static function countSubscriptions($userID)
	{
		global $DB;

		list($count) = $DB->sql_row("SELECT COUNT(*) FROM wD_PushSubscriptions WHERE userID = ".intval($userID));
		return (int)$count;
	}

	/**
	 * Validate and store this browser's push subscription for the given user, and remember it in the browser's
	 * cookie. The unique key is the endpoint hash alone, not (userID, endpoint), so a browser subscribed under a
	 * different account is rebound rather than left sending one user's notifications to another's device.
	 *
	 * If the browser was registered with a different subscription before (its push service replaced it), that
	 * one is removed.
	 *
	 * The caller is responsible for issuing a COMMIT.
	 *
	 * @param bool $resync True when the browser is only updating the subscription it was registered with (its
	 * 	endpoint or keys changed), rather than the user asking to subscribe. It's then only stored if that earlier
	 * 	subscription is still registered to this user, so one removed by unsubscribing stays removed.
	 *
	 * @return bool false if the subscription wasn't stored because $resync found nothing to update
	 * @throws Exception if the subscription failed validation
	 */
	public static function subscribeBrowser($userID, $endpoint, $p256dh, $auth, $userAgent, $resync)
	{
		global $DB;

		$userID = intval($userID);

		if( !is_string($endpoint) || strlen($endpoint) > 1500 || strncmp($endpoint, 'https://', 8) !== 0
			|| !filter_var($endpoint, FILTER_VALIDATE_URL)
			|| !preg_match('#^[A-Za-z0-9_\-.:/=%]+$#', $endpoint) )
			throw new Exception('Invalid push subscription endpoint.');
		if( !is_string($p256dh) || strlen($p256dh) > 255 || !preg_match('/^[A-Za-z0-9_\-=]+$/', $p256dh) )
			throw new Exception('Invalid push subscription key.');
		if( !is_string($auth) || strlen($auth) > 64 || !preg_match('/^[A-Za-z0-9_\-=]+$/', $auth) )
			throw new Exception('Invalid push subscription secret.');

		$previousHash = self::browserEndpointHash();
		if( $resync && !self::isBrowserSubscribed($userID) )
		{
			self::clearBrowserCookie();
			return false;
		}

		$endpointHash = md5($endpoint);
		$endpoint = $DB->escape($endpoint);
		$p256dh = $DB->escape($p256dh);
		$auth = $DB->escape($auth);
		$userAgent = $DB->escape(substr($userAgent, 0, 255));

		$DB->sql_put("INSERT INTO wD_PushSubscriptions
				(userID, endpointHash, endpoint, p256dh, auth, userAgent, timeCreated, timeLastUsed)
			VALUES (".$userID.", '".$endpointHash."', '".$endpoint."', '".$p256dh."', '".$auth."', '".$userAgent."', ".time().", ".time().")
			ON DUPLICATE KEY UPDATE userID = VALUES(userID), p256dh = VALUES(p256dh), auth = VALUES(auth),
				userAgent = VALUES(userAgent), timeLastUsed = VALUES(timeLastUsed)");

		if( $previousHash !== null && $previousHash !== $endpointHash )
			$DB->sql_put("DELETE FROM wD_PushSubscriptions WHERE userID = ".$userID." AND endpointHash = '".$previousHash."'");

		self::setBrowserCookie($endpointHash);

		return true;
	}

	/**
	 * Remove one of the calling user's subscriptions, given its endpoint, or this browser's if no endpoint is
	 * given. The caller is responsible for a COMMIT.
	 */
	public static function unsubscribeBrowser($userID, $endpoint = null)
	{
		global $DB;

		$endpointHash = ( $endpoint === null ) ? self::browserEndpointHash() : md5($endpoint);
		if( $endpointHash === null ) return;

		$DB->sql_put("DELETE FROM wD_PushSubscriptions
			WHERE userID = ".intval($userID)." AND endpointHash = '".$endpointHash."'");

		if( $endpointHash === self::browserEndpointHash() )
			self::clearBrowserCookie();
	}

	/**
	 * Remove all of a user's subscriptions, on every device. The caller is responsible for a COMMIT.
	 *
	 * @return int The number of subscriptions removed
	 */
	public static function unsubscribeAll($userID)
	{
		global $DB;

		$DB->sql_put("DELETE FROM wD_PushSubscriptions WHERE userID = ".intval($userID));
		$removed = $DB->last_affected();

		self::clearBrowserCookie();

		return $removed;
	}

	/**
	 * Remove this browser's subscription as it's logged off, whichever user it's registered to, so that it
	 * doesn't go on receiving that user's notifications. Commits, as a log-off can end in an error page, which
	 * skips the usual commit at the end of the page.
	 */
	public static function logOffBrowser()
	{
		global $DB;

		$endpointHash = self::browserEndpointHash();
		if( $endpointHash === null ) return;

		if( is_object($DB) )
		{
			$DB->sql_put("DELETE FROM wD_PushSubscriptions WHERE endpointHash = '".$endpointHash."'");
			$DB->sql_put("COMMIT");
		}

		self::clearBrowserCookie();
	}

	/**
	 * Queue a push notification to every subscription of the given users (filtered against the feature flag
	 * first). It's sent after this request's response has gone out. Never throws: push delivery must not be able
	 * to break the calling code path (message sending, game processing).
	 *
	 * Call this after the caller's own writes are committed, so a rolled back change is never notified.
	 *
	 * @param array $userIDs Users to notify (non-flagged users are filtered out here)
	 * @param string $title Notification title (plain text)
	 * @param string $body Notification body (plain text)
	 * @param string $url Site-absolute link to open on click, e.g. '/board.php?gameID=1'
	 * @param string $tag Collapse key; a new notification replaces an older one with the same tag
	 */
	public static function queue(array $userIDs, $title, $body, $url, $tag)
	{
		global $DB, $Redis;

		try
		{
			$userIDs = array_unique(array_filter(array_map('intval', $userIDs),
				array('libPush', 'isEnabledForUser')));
			if( count($userIDs) == 0 ) return;

			$subscriptions = array();
			$tabl = $DB->sql_tabl("SELECT endpoint, p256dh, auth
				FROM wD_PushSubscriptions WHERE userID IN (".implode(',', $userIDs).")");
			while( $row = $DB->tabl_hash($tabl) )
				$subscriptions[] = array(
					'endpoint' => html_entity_decode($row['endpoint'], ENT_QUOTES, 'UTF-8'),
					'p256dh' => $row['p256dh'],
					'auth' => $row['auth']
				);
			if( count($subscriptions) == 0 ) return;

			$Redis->listPush(self::QUEUE_KEY, json_encode(array(
				'time' => time(),
				'tag' => $tag,
				'payload' => json_encode(array('title' => $title, 'body' => $body, 'url' => $url, 'tag' => $tag)),
				'subscriptions' => $subscriptions
			)), self::QUEUE_MAX_JOBS);

			self::scheduleDrain();
		}
		catch( \Throwable $e )
		{
			error_log('Web Push queue error: '.substr($e->getMessage(), 0, 200));
		}
	}

	private static $drainScheduled = false;
	private static $drainEvenIfBlocking = false;

	/**
	 * Drain the queue at the end of this request, once its response has been sent. queue() calls this; the
	 * gamemaster calls it on every run, so anything left queued (e.g. by a drain that hit its time limit) is
	 * picked up.
	 *
	 * @param bool $evenIfBlocking Drain even if the response can't be sent first, which is only the case outside
	 * 	PHP-FPM. The gamemaster passes true: its caller is a cron job, and the drain comes after all its work.
	 */
	public static function scheduleDrain($evenIfBlocking = false)
	{
		if( $evenIfBlocking ) self::$drainEvenIfBlocking = true;

		if( self::$drainScheduled ) return;
		self::$drainScheduled = true;

		register_shutdown_function(array('libPush', 'drainAfterResponse'));
	}

	/**
	 * Shutdown function: finish the response, then drain the queue.
	 */
	public static function drainAfterResponse()
	{
		global $DB;

		if( function_exists('fastcgi_finish_request') )
		{
			// Release the session lock first, or the user's next request would wait for the drain
			if( session_status() == PHP_SESSION_ACTIVE )
				session_write_close();
			fastcgi_finish_request();
		}
		elseif( !self::$drainEvenIfBlocking && php_sapi_name() != 'cli' )
		{
			return; // The response would wait for the drain; leave the queue to the gamemaster
		}

		ignore_user_abort(true);

		// The drain doesn't use the database, and the request is over, so let go of the connection and its locks
		// now; otherwise e.g. the gamemaster's 'gamemaster' lock would keep the next run waiting for the drain.
		if( is_object($DB) )
			$DB->disconnect();

		self::drain();
	}

	/**
	 * Send queued notifications until the queue is empty or DRAIN_TIME_LIMIT is reached. Only one request drains
	 * at a time; the others return straight away and leave their jobs to it. Never throws.
	 */
	public static function drain()
	{
		global $Redis;

		$errorReporting = null;
		try
		{
			if( !self::isConfigured() || $Redis->listLength(self::QUEUE_KEY) == 0 ) return;

			// A batch that starts just before the time limit can run on well past it if the push services are slow
			$startTime = time();
			set_time_limit(self::DRAIN_TIME_LIMIT + 120);

			// The web-push library's dependencies raise deprecation notices on newer PHP versions
			// (guzzle/psr7 raises them via trigger_error as E_USER_DEPRECATED, not E_DEPRECATED).
			// The site error handler only logs deprecations, but masking them keeps library notices
			// we can't fix out of the error logs.
			$errorReporting = error_reporting(error_reporting() & ~(E_DEPRECATED | E_USER_DEPRECATED));

			$webPush = null;
			do
			{
				// The lock outlasts the time limit, so it only expires early if the drain was killed
				$lockToken = $Redis->acquireLock(self::DRAIN_LOCK_KEY, self::DRAIN_TIME_LIMIT + 120);
				if( $lockToken === false ) return;

				try
				{
					while( time() - $startTime < self::DRAIN_TIME_LIMIT )
					{
						$jobs = $Redis->listPopMany(self::QUEUE_KEY, self::DRAIN_BATCH_JOBS);
						if( count($jobs) == 0 ) break;

						if( $webPush === null )
							$webPush = self::createWebPush();
						self::sendJobs($webPush, $jobs);
					}
				}
				finally
				{
					$Redis->releaseLock(self::DRAIN_LOCK_KEY, $lockToken);
				}

				// A request that queued a job while the lock was held was turned away from draining it, so look
				// again now the lock is free; otherwise the job would wait for the next gamemaster run.
			}
			while( time() - $startTime < self::DRAIN_TIME_LIMIT && $Redis->listLength(self::QUEUE_KEY) > 0 );
		}
		catch( \Throwable $e )
		{
			error_log('Web Push error: '.substr($e->getMessage(), 0, 200));
		}
		finally
		{
			if( $errorReporting !== null )
				error_reporting($errorReporting);
		}
	}

	private static function createWebPush()
	{
		// An absolute path, as shutdown functions can run with a different working directory
		require_once(__DIR__.'/../vendor/autoload.php');

		$webPush = new WebPush(
			array('VAPID' => array(
				'subject' => Config::$vapidSubject,
				'publicKey' => Config::$vapidPublicKey,
				'privateKey' => Config::$vapidPrivateKey
			)),
			// A string: web-push copies TTL into the request headers as given, and guzzle/psr7 2.11+
			// raises a deprecation notice for any header value that isn't a string
			array('TTL' => (string)self::TTL),
			self::REQUEST_TIMEOUT
		);
		// Sign one VAPID token per push service for each batch, instead of one per notification
		$webPush->setReuseVAPIDHeaders(true);

		return $webPush;
	}

	/**
	 * Send a batch of queued jobs concurrently, and record which endpoints were delivered to or have expired.
	 */
	private static function sendJobs(WebPush $webPush, array $jobs)
	{
		global $Redis;

		// Keyed on endpoint and tag, so when several notifications for the same device and tag have built up
		// (e.g. a run of messages in one game) only the newest is sent; it would replace the others anyway.
		$notifications = array();
		foreach( $jobs as $json )
		{
			$job = json_decode($json, true);
			if( !is_array($job) || $job['time'] < time() - self::TTL ) continue; // Too old to be worth showing

			foreach( $job['subscriptions'] as $subscription )
				$notifications[$subscription['endpoint'].' '.$job['tag']] = array($subscription, $job['payload']);
		}
		if( count($notifications) == 0 ) return;

		foreach( $notifications as $notification )
		{
			list($subscription, $payload) = $notification;
			$webPush->queueNotification(Subscription::create(array(
				'endpoint' => $subscription['endpoint'],
				'keys' => array('p256dh' => $subscription['p256dh'], 'auth' => $subscription['auth'])
			)), $payload);
		}

		$sentHashes = array();
		$expiredHashes = array();
		$webPush->flushPooled(function($report) use (&$sentHashes, &$expiredHashes) {
			if( $report->isSuccess() )
			{
				$sentHashes[] = md5($report->getEndpoint());
			}
			elseif( $report->isSubscriptionExpired() )
			{
				// The push service says this subscription is gone (endpoint expired / permission
				// revoked / browser profile deleted); it's removed so we stop trying.
				$expiredHashes[] = md5($report->getEndpoint());
			}
			else
			{
				// error_log, not trigger_error: the site error handler turns triggered errors
				// into a fatal response
				error_log('Web Push send failed: '.substr($report->getReason(), 0, 200));
			}
		}, null, self::DRAIN_CONCURRENCY);

		if( count($sentHashes) > 0 )
			$Redis->setAddMany(self::SENT_KEY, array_unique($sentHashes));
		if( count($expiredHashes) > 0 )
			$Redis->setAddMany(self::EXPIRED_KEY, array_unique($expiredHashes));
	}

	/**
	 * Write the drain's results to wD_PushSubscriptions: remove the subscriptions the push services reported
	 * expired, and mark the ones delivered to as used. Run by the gamemaster's background tasks.
	 *
	 * @return int The number of subscriptions updated, 0 if there was nothing to do
	 */
	public static function applySendResults()
	{
		global $DB, $Redis;

		$isHash = function($hash) { return is_string($hash) && preg_match('/^[0-9a-f]{32}$/', $hash); };

		$updated = 0;
		while( count($expiredHashes = array_filter($Redis->setPopMany(self::EXPIRED_KEY, 1000), $isHash)) > 0 )
		{
			$DB->sql_put("DELETE FROM wD_PushSubscriptions
				WHERE endpointHash IN ('".implode("','", $expiredHashes)."')");
			$DB->sql_put("COMMIT");
			$updated += count($expiredHashes);
		}
		while( count($sentHashes = array_filter($Redis->setPopMany(self::SENT_KEY, 1000), $isHash)) > 0 )
		{
			$DB->sql_put("UPDATE wD_PushSubscriptions SET timeLastUsed = ".time()."
				WHERE endpointHash IN ('".implode("','", $sentHashes)."')");
			$DB->sql_put("COMMIT");
			$updated += count($sentHashes);
		}

		return $updated;
	}
}
