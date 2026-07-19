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
// is required inside sendToUsers() only once the feature-flag filter has passed, so requests
// which don't result in a push send never pay the autoload cost.
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

/**
 * Web Push (PWA) notifications: subscription storage and sending, gated by
 * Config::$pushEnabledUserIDs while the feature is being trialled.
 *
 * @package Base
 */
class libPush
{
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
	 * Whether push is enabled for the given user. While the feature is being trialled this is
	 * limited to the userIDs in Config::$pushEnabledUserIDs.
	 */
	public static function isEnabledForUser($userID)
	{
		if( !self::isConfigured() ) return false;
		if( !isset(Config::$pushEnabledUserIDs) || !is_array(Config::$pushEnabledUserIDs) ) return false;
		return in_array(intval($userID), Config::$pushEnabledUserIDs);
	}

	/**
	 * Validate and store a browser push subscription. The unique key is the endpoint hash alone,
	 * not (userID, endpoint), so a browser re-subscribed under a different account is rebound
	 * rather than left sending one user's notifications to another's device.
	 *
	 * The caller is responsible for issuing a COMMIT.
	 *
	 * @return bool false if the subscription failed validation
	 */
	public static function registerSubscription($userID, $endpoint, $p256dh, $auth, $userAgent = '')
	{
		global $DB;

		$userID = intval($userID);

		if( !is_string($endpoint) || strlen($endpoint) > 1500 || strncmp($endpoint, 'https://', 8) !== 0
			|| !filter_var($endpoint, FILTER_VALIDATE_URL)
			|| !preg_match('#^[A-Za-z0-9_\-.:/=%]+$#', $endpoint) )
			return false;
		if( !is_string($p256dh) || strlen($p256dh) > 255 || !preg_match('/^[A-Za-z0-9_\-=]+$/', $p256dh) )
			return false;
		if( !is_string($auth) || strlen($auth) > 64 || !preg_match('/^[A-Za-z0-9_\-=]+$/', $auth) )
			return false;

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

		return true;
	}

	/**
	 * Remove one of the calling user's subscriptions. The caller is responsible for a COMMIT.
	 */
	public static function unregisterSubscription($userID, $endpoint)
	{
		global $DB;

		$DB->sql_put("DELETE FROM wD_PushSubscriptions
			WHERE userID = ".intval($userID)." AND endpointHash = '".md5($endpoint)."'");
	}

	/**
	 * Send a push notification to every subscription of the given users (filtered against the
	 * feature flag first). Expired/revoked subscriptions reported back by the push services are
	 * deleted. Never throws: push delivery must not be able to break the calling code path
	 * (message sending, game processing).
	 *
	 * Sends are currently done inline; at feature-flag scale this is a single batched flush of a
	 * handful of requests. Before general availability this should move to a queue drained by
	 * gamemaster/backgroundTasks.php.
	 *
	 * @param array $userIDs Users to notify (non-flagged users are filtered out here)
	 * @param string $title Notification title (plain text)
	 * @param string $body Notification body (plain text)
	 * @param string $url Site-absolute link to open on click, e.g. '/board.php?gameID=1'
	 * @param string $tag Collapse key; a new notification replaces an older one with the same tag
	 */
	public static function sendToUsers(array $userIDs, $title, $body, $url, $tag)
	{
		global $DB;

		$errorReporting = null;
		try
		{
			$userIDs = array_unique(array_filter(array_map('intval', $userIDs),
				array('libPush', 'isEnabledForUser')));
			if( count($userIDs) == 0 ) return;

			// The web-push library's dependencies raise deprecation notices on newer PHP versions;
			// with display_errors on those would be printed into the middle of API JSON responses
			$errorReporting = error_reporting(error_reporting() & ~E_DEPRECATED);

			$subscriptions = array();
			$tabl = $DB->sql_tabl("SELECT endpointHash, endpoint, p256dh, auth
				FROM wD_PushSubscriptions WHERE userID IN (".implode(',', $userIDs).")");
			while( $row = $DB->tabl_hash($tabl) )
				$subscriptions[] = $row;
			if( count($subscriptions) == 0 ) return;

			require_once('vendor/autoload.php');

			$webPush = new WebPush(
				array('VAPID' => array(
					'subject' => Config::$vapidSubject,
					'publicKey' => Config::$vapidPublicKey,
					'privateKey' => Config::$vapidPrivateKey
				)),
				array('TTL' => 3600),
				10 // Overall client timeout in seconds; a slow push service can't hang the caller for long
			);

			$payload = json_encode(array('title' => $title, 'body' => $body, 'url' => $url, 'tag' => $tag));

			foreach( $subscriptions as $sub )
			{
				$webPush->queueNotification(Subscription::create(array(
					'endpoint' => html_entity_decode($sub['endpoint'], ENT_QUOTES, 'UTF-8'),
					'keys' => array('p256dh' => $sub['p256dh'], 'auth' => $sub['auth'])
				)), $payload);
			}

			$sentHashes = array();
			$wroteToDB = false;
			foreach( $webPush->flush() as $report )
			{
				if( $report->isSuccess() )
				{
					$sentHashes[] = "'".md5($report->getEndpoint())."'";
				}
				elseif( $report->isSubscriptionExpired() )
				{
					// The push service says this subscription is gone (endpoint expired / permission
					// revoked / browser profile deleted); remove it so we stop trying.
					$DB->sql_put("DELETE FROM wD_PushSubscriptions
						WHERE endpointHash = '".md5($report->getEndpoint())."'");
					$wroteToDB = true;
				}
				else
				{
					// error_log, not trigger_error: the site error handler turns triggered errors
					// into a fatal response, which would break the calling message/process path
					error_log('Web Push send failed: '.substr($report->getReason(), 0, 200));
				}
			}
			if( count($sentHashes) > 0 )
			{
				$DB->sql_put("UPDATE wD_PushSubscriptions SET timeLastUsed = ".time()."
					WHERE endpointHash IN (".implode(',', $sentHashes).")");
				$wroteToDB = true;
			}
			// sendToUsers only runs after the caller's own writes are committed (the message path
			// commits in libGameMessage::notify, gamemaster commits after processing), so this only
			// commits our subscription housekeeping; without it these writes roll back with the
			// request (same pattern as the explicit COMMITs in libGameMessage::notify).
			if( $wroteToDB )
				$DB->sql_put("COMMIT");
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
}
