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

/**
 * Records the cost of one part of a request under its own METRICS_<name>_{COUNT,TIME_MS,DB_GET,DB_PUT,DB_TIME_MS}
 * counters, in the same form as the per-route metrics that status.php shows. This splits up requests that do
 * several unrelated jobs, such as gamemaster.php, which processes games and runs the background tasks.
 *
 * @package Base
 */
class libMetrics
{
	/**
	 * The parts of a gamemaster.php run that are recorded separately, for status.php to list.
	 * - IDLE: a whole run which found no games to check and ran no background tasks
	 * - SETUP: applying votes and finding the games to check, on every run
	 * - GAME_<phase>_<players>: a game whose turn was processed, by the phase processed and the game's playerTypes
	 * - GAME_CHECKED: a game which was loaded and checked but didn't need processing
	 * - GAME_CRASHED / GAME_FAILED: a game marked as crashed, or whose processing threw an exception
	 * - TASK_<name>: a background task from gamemaster/backgroundTasks.php
	 *
	 * @return string[]
	 */
	public static function gamemasterParts()
	{
		$parts = array('IDLE', 'SETUP', 'GAME_CHECKED', 'GAME_CRASHED', 'GAME_FAILED');
		foreach(array('PREGAME', 'DIPLOMACY', 'RETREATS', 'BUILDS') as $phase)
			foreach(array('HUMAN', 'MIXED', 'BOT') as $players)
				$parts[] = 'GAME_'.$phase.'_'.$players;
		foreach(array('SESSIONS', 'ONLINEUSERS', 'MISCSTATS', 'RELIABILITY', 'RELIABILITYREFRESH', 'GRRANKS', 'NMRWARNINGS',
			'GROUPS', 'USERCONNECTIONS', 'WATCHEDGAMES', 'POINTSCHECK', 'ANONBOTGAMES', 'BOTGAMECLEANUP', 'BACKUP', 'PUSHRESULTS') as $task)
			$parts[] = 'TASK_'.$task;

		return array_map(function($part) { return 'GAMEMASTER_'.$part; }, $parts);
	}

	/**
	 * The name of the part a processed game is recorded under, e.g. GAMEMASTER_GAME_DIPLOMACY_BOT
	 *
	 * @param string $phase The phase that was processed
	 * @param string $playerTypes The game's playerTypes
	 * @return string
	 */
	public static function gamemasterGamePart($phase, $playerTypes)
	{
		$players = array('Members' => 'HUMAN', 'Mixed' => 'MIXED', 'MemberVsBots' => 'BOT');

		return 'GAMEMASTER_GAME_'.strtoupper(str_replace('-', '', $phase)).'_'.($players[$playerTypes] ?? 'HUMAN');
	}

	/**
	 * The things a browser is allowed to report a timing or a count for, recorded as
	 * METRICS_CLIENT_<name>_{COUNT,TIME_MS} by the client/metrics API route.
	 *
	 * A browser can say anything, so the names it may use are listed here rather than taken from the
	 * request: they become Redis keys, and status.php lists them.
	 *
	 * - PAGE_<name>: a classic page, from the browser's navigation timing (TIME_MS is load time)
	 * - BOARD_LOAD / BOARD_FILES / BOARD_CONTEXT: the React board's time to first render, time spent
	 *   fetching the public game files, and time in game/playercontext
	 * - SSE_CONNECT / SSE_RECONNECT / SSE_RESYNC: the SSE connection as the browser sees it
	 * - ERROR_SCRIPT / ERROR_PROMISE / ERROR_REACT: client errors, counted even when the log is
	 *   de-duplicated or the beacon is rate limited
	 *
	 * @return string[]
	 */
	public static function clientParts()
	{
		$parts = array('PAGE_HOME', 'PAGE_BOARD', 'PAGE_GAMELISTINGS', 'PAGE_FORUM', 'PAGE_PROFILE', 'PAGE_OTHER',
			'BOARD_LOAD', 'BOARD_FILES', 'BOARD_CONTEXT',
			'SSE_CONNECT', 'SSE_RECONNECT', 'SSE_RESYNC',
			'ERROR_SCRIPT', 'ERROR_PROMISE', 'ERROR_REACT');

		return array_map(function($part) { return 'CLIENT_'.$part; }, $parts);
	}

	/**
	 * The name of the page being served, as the METRICS_PAGE_* counters and status.php's page list spell it:
	 * the script's own name, with index.php as HOME.
	 *
	 * @return string
	 */
	public static function pageName()
	{
		$pageName = '';
		if (isset($_SERVER['PHP_SELF']) && $_SERVER['PHP_SELF']) {
			$pageName = strtoupper(basename($_SERVER['PHP_SELF'], '.php'));
		} elseif (isset($_SERVER['SCRIPT_NAME']) && $_SERVER['SCRIPT_NAME']) {
			$pageName = strtoupper(basename($_SERVER['SCRIPT_NAME'], '.php'));
		}

		if ($pageName === 'INDEX' || $pageName === '') {
			$pageName = 'HOME';
		}

		return $pageName;
	}

	/**
	 * The counter a page's own load time is added to, for the head of the page to tell the browser. Pages
	 * which aren't listed in clientParts() share PAGE_OTHER rather than each making a counter of their own.
	 *
	 * @return string A name recordClient() accepts
	 */
	public static function clientPageName()
	{
		$name = 'PAGE_'.preg_replace('/[^A-Z0-9_]/', '', self::pageName());

		return in_array('CLIENT_'.$name, self::clientParts()) ? $name : 'PAGE_OTHER';
	}

	/**
	 * Add a browser's report to one of the client counters. Unlike record() there is no time or query count
	 * of our own to add: the browser gives the milliseconds, and only names from clientParts() are accepted.
	 *
	 * @param string $name A name from clientParts(), without the CLIENT_ prefix
	 * @param int $count How many times it happened
	 * @param int|null $ms The milliseconds to add, if the name is a timing
	 * @return bool Whether the name was one we record
	 */
	public static function recordClient($name, $count = 1, $ms = null)
	{
		global $Redis;

		$name = 'CLIENT_'.strtoupper(preg_replace('/[^A-Za-z0-9_]/', '', (string)$name));
		if( !in_array($name, self::clientParts()) )
			return false;

		$increments = array('METRICS_'.$name.'_COUNT' => max(1, min(1000, intval($count))));
		if( !is_null($ms) )
			$increments['METRICS_'.$name.'_TIME_MS'] = max(0, min(600000, intval($ms)));

		try
		{
			$Redis->incrementMany($increments);
		}
		catch(Exception $e)
		{
			// Metrics are never worth breaking the request over
		}

		return true;
	}

	/**
	 * Mark the start of a part of the request.
	 *
	 * @return array A marker to pass to record() at the end of the part
	 */
	public static function start()
	{
		return array(microtime(true), self::dbTotals());
	}

	/**
	 * The marker for a part which started with the request, for recording the whole request.
	 *
	 * @return array
	 */
	public static function requestStart()
	{
		global $pageStartTime;

		return array($pageStartTime ?? microtime(true), array(0, 0, 0.0));
	}

	/**
	 * Add one hit to a part's counters, with the time and queries since the given start marker.
	 *
	 * @param string $name The part, e.g. GAMEMASTER_IDLE; stored as METRICS_<name>_*
	 * @param array $start The marker returned by start() when the part began
	 */
	public static function record($name, array $start)
	{
		global $Redis;

		list($startTime, list($startGets, $startPuts, $startDBTime)) = $start;
		list($gets, $puts, $dbTime) = self::dbTotals();

		try
		{
			$Redis->incrementMany(array(
				'METRICS_'.$name.'_COUNT' => 1,
				'METRICS_'.$name.'_TIME_MS' => round((microtime(true) - $startTime) * 1000),
				'METRICS_'.$name.'_DB_GET' => $gets - $startGets,
				'METRICS_'.$name.'_DB_PUT' => $puts - $startPuts,
				'METRICS_'.$name.'_DB_TIME_MS' => round(($dbTime - $startDBTime) * 1000),
			));
		}
		catch(Exception $e)
		{
			// Metrics are never worth breaking the request over
		}
	}

	/**
	 * The query counts and time so far in this request, if the database is counting them.
	 *
	 * @return array (fetch count, put count, seconds)
	 */
	private static function dbTotals()
	{
		global $DB;

		if( !($DB instanceof MetricsDatabase) )
			return array(0, 0, 0.0);

		return array($DB->metricsFetchCount, $DB->metricsPutCount, $DB->metricsFetchTime + $DB->metricsPutTime);
	}
}
