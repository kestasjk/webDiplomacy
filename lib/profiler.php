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
 * A sampling profiler, for finding where the site's PHP time goes so optimization can be aimed at what matters.
 *
 * Uses the Excimer extension (apt install php8.4-excimer), and is off unless Config::$profilerSampleRate and
 * Config::$profilerDirectory are set. That fraction of requests is sampled every PERIOD seconds, both in
 * wall-clock time, which includes waiting for MariaDB and Redis (counted against the PHP function that was
 * waiting), and in CPU time, which is PHP's own work only. Each profiled request's call stacks are appended to
 * daily files in the "collapsed stack" format that flame graph tools read, with the page as the first frame.
 * scripts/profile-report.php adds them up.
 *
 * @package Base
 */
class libProfiler
{
	/**
	 * Seconds between samples
	 */
	const PERIOD = 0.01;

	/**
	 * A day's file stops growing at this size, in case the sample rate is set too high for the traffic
	 */
	const MAX_FILE_BYTES = 1073741824;

	/**
	 * @var ExcimerProfiler[] mode (wall or cpu) => profiler, while this request is being profiled
	 */
	private static $profilers = array();

	/**
	 * Start profiling this request if profiling is on and this request is picked. header.php calls this as soon
	 * as config.php is loaded.
	 */
	public static function start()
	{
		// isset(), so a config.php without these settings leaves profiling off
		if( !isset(Config::$profilerSampleRate, Config::$profilerDirectory) || Config::$profilerDirectory == '' )
			return;

		$rate = (float)Config::$profilerSampleRate;
		if( $rate <= 0 || count(self::$profilers) || !extension_loaded('excimer') )
			return;
		if( $rate < 1 && mt_rand() / mt_getrandmax() >= $rate )
			return;

		foreach( array('wall' => EXCIMER_REAL, 'cpu' => EXCIMER_CPU) as $mode => $eventType )
		{
			$profiler = new ExcimerProfiler();
			$profiler->setPeriod(self::PERIOD);
			$profiler->setEventType($eventType);
			$profiler->start();
			self::$profilers[$mode] = $profiler;
		}

		// Shutdown functions run in the order they were registered. Registering the save once shutdown has
		// started puts it after any registered during the request, so their time is included too.
		register_shutdown_function(function() {
			register_shutdown_function(array('libProfiler', 'save'));
		});
	}

	/**
	 * Stop profiling and append this request's stacks to today's files. Runs at shutdown, and must never be
	 * able to break the request.
	 */
	public static function save()
	{
		// Stop both first, so writing the files isn't itself profiled
		foreach( self::$profilers as $profiler )
			$profiler->stop();

		try
		{
			$dir = rtrim(Config::$profilerDirectory, '/');
			if( !is_dir($dir) )
				@mkdir($dir, 0775, true);

			$page = self::pageName();
			$root = dirname(__DIR__).'/';

			foreach( self::$profilers as $mode => $profiler )
			{
				$stacks = rtrim($profiler->getLog()->formatCollapsed(), "\n");
				if( $stacks === '' )
					continue; // The request finished before the first sample

				// Samples due at the very end of the request (the last output, exit) are only taken when PHP next
				// checks for them, which is on entering the first shutdown function: the closure registered in
				// start(), or this function. Name them for what they are rather than as the profiler's own time.
				$stacks = preg_replace('/^(\{closure:[^;]*lib\/profiler\.php\(\d+\)\}|libProfiler::save) /m', '(end of request) ', $stacks);

				// The page as the bottom frame of each stack, and file paths relative to the webroot
				$stacks = $page.';'.str_replace(array("\n", $root), array("\n".$page.';', ''), $stacks)."\n";

				$file = $dir.'/'.$mode.'-'.gmdate('Y-m-d').'.collapsed';
				clearstatcache(true, $file);
				$size = @filesize($file);
				if( $size > self::MAX_FILE_BYTES )
					continue;

				// Whoever starts a day's file (the web server, or a cron job running as another user) leaves it
				// writable by the others
				if( @file_put_contents($file, $stacks, FILE_APPEND | LOCK_EX) && $size === false )
					@chmod($file, 0666);
			}
		}
		catch( \Throwable $e ) { }

		self::$profilers = array();
	}

	/**
	 * What the request was, for the bottom frame: the script, plus the route for api.php and ajax.php
	 *
	 * @return string
	 */
	private static function pageName()
	{
		global $ajaxRoute;

		if( php_sapi_name() == 'cli' )
			$page = 'cli:'.basename(isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : 'unknown');
		else
			$page = ltrim(isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : 'unknown', '/');

		if( $page == 'api.php' && isset($_GET['route']) && is_string($_GET['route']) )
			$page .= ':'.strtolower(trim($_GET['route'], " /"));
		elseif( $page == 'ajax.php' && isset($ajaxRoute) && is_string($ajaxRoute) )
			$page .= ':'.$ajaxRoute;

		// In the file format frames are separated by ; and the sample count follows the last space
		return substr(preg_replace('/[^A-Za-z0-9_.:\/-]/', '_', $page), 0, 80);
	}
}
