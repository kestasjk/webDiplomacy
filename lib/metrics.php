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
