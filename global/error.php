<?php
/*
    Copyright (C) 2004-2010 Kestas J. Kuliukas

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
 * The error handling page; where the errors come to get logged and saved for "careful analysis"
 *
 * @package Base
 */

set_exception_handler('exception_handler');
set_error_handler('error_handler');
//assert_options (ASSERT_CALLBACK, 'assert_handler'); // 8.4 deprecated
//assert_options (ASSERT_WARNING, 0);
error_reporting( E_ALL | E_NOTICE);

function assert_handler ($file, $line, $expr)
{
	trigger_error("An assertion, ".$expr.", was not met as required.");
}

function exception_handler( $exception)
{
	$file = $exception->getFile();
	$trace = $exception->getTraceAsString();
	$line = $exception->getLine();

	error_handler(E_ERROR, 'A software exception was not caught: "'.$exception->getMessage().'"',
		$file, $line, array(
			'exception' => $exception,
			'trace' => $trace
		));
}

function error_handler($errno, $errstr, $errfile=false, $errline=false, $errcontext=false)
{
	global $DB, $User, $Game;

	// PHP calls this handler even for errors that error_reporting() masks, including ones silenced with @, and
	// leaves the check to the handler. Libraries rely on it: symfony's trigger_deprecation() raises its notices
	// silenced (guzzle/psr7 does on every Web Push send), and lib/push.php masks deprecations while it sends.
	// Returning false hands them to PHP's own handler, which ignores them but still sets error_get_last().
	if( !(error_reporting() & $errno) )
		return false;

	// A deprecation warns that the code will break in some future PHP or library version, it doesn't mean
	// anything has gone wrong now, so it is logged without ending the request
	if( $errno == E_DEPRECATED || $errno == E_USER_DEPRECATED )
	{
		libError::logDeprecation($errstr, $errfile, $errline);
		return true;
	}

	if ( defined('ERROR') )
		define('ERRORINERROR',true);
	else
		define('ERROR',true);

	if( !defined('JSERROR') )
		define('JSERROR',($errstr=='JavaScript error logged'));

	if( strpos($errstr, 'Unable to save result set') )
		libHTML::error("Database error saving result, this may be due to high server load; please refresh or click back and try again. If the problem repeats itself please report the problem in the forum.");
	elseif( strpos($errstr, 'Lock wait timeout exceeded') )
		libHTML::error("Database error waiting for a record lock, this may be due to high server load; please refresh or click back and try again. If the problem repeats itself please report the problem in the forum.");

	$error = 'Error: "'.$errstr."\"\n";

	if ( $errfile )
		$error .= 'Raised: "'.$errfile."\"\n";

	if ( $errline )
		$error .= 'Line: "'.$errline."\"\n";

	// Identifies this kind of error for de-duplication; must use the message as raised, before it is decorated below
	$signature = libError::signature($errstr, $errfile, $errline);

	if ( isset($User) and $User instanceof User )
	{
		$error .= 'userID = '.$User->id;

		if ( isset($Game) and $Game instanceof Game )
			$error .= ', gameID = '.$Game->id;
	}
	
	// If the error handler is called twice ensure this doesn't redeclare:
	if (!function_exists('recursiveprint'))
	{
		// PHP's print_r() is terrible, heap corruption errors all the time
		function recursiveprint ( &$array, $depth )
		{
			$tab = '';
			$tracetxt = '';
			for ( $i=1; $i<$depth; $i++ )
				$tab .= "\t";

			if ( $depth == 7 ) return $tab."*Max depth reached*\n";

			foreach ( $array as $name => $sub )
			{
				if ( $name === "_REQUEST" or $name === "defined_vars" or $name === "_SERVER" ) continue;

				$tracetxt .= $tab.$name.' => ';

				if ( is_object($sub) or is_array ( $sub ) )
				{
					$tracetxt .= "Array: (\n";
					$depth++;
					$tracetxt .= recursiveprint ( $sub, $depth );
					$depth--;
					$tracetxt .= $tab.")\n";
				}
				else
					$tracetxt .= $sub."\n";
			}

			return $tracetxt;
		}
	}

	$error .= ($errcontext ? 'Variable dump: '.recursiveprint($errcontext, 1)."\n\n" : '');

	$bt = debug_backtrace();
	$error .= "Trace:\n".recursiveprint( $bt, 1 );
	if ( Config::$debug )
	{
		/*
		 * If we're an admin (probably a developer), or are looking at a DATC test
		 * (which will have removed the $User data), then display the full error.
		 * Normal users shouldn't see the full error.
		 */
		$errstr .= '<br /><br />'.nl2br($error);
	}
	else
	{
		htmlentities($errstr, ENT_QUOTES, 'UTF-8', false);
	}

	if( defined('ERRORINERROR') )
		libHTML::error('Error while outputting an error: '.$errstr);

	// By setting Database and User to null libHTML knows something isn't right.
	// TODO: Set a define instead
	$User = null;
	if ( is_object($DB) )
	{
		// Must not raise another error while handling this one (e.g. if the connection has gone away),
		// so roll back without going through the checked sql_put()
		$DB->rollbackQuietly();
		$DB = null;
	}

	$message = '<strong>Error triggered:</strong> '.$errstr.'.'.
			'<p>This was probably caused by a software bug. ';

	if( !libError::isLoggingEnabled() )
		libHTML::error($message.' If these occur often try enabling error-logging via
				config.php, and report errors to the official devs for help.</p>');

	// Check error log directory
	$errorlogDirectory = Config::errorlogDirectory();

	if ( ! is_dir($errorlogDirectory) )
	{
		mkdir($errorlogDirectory) or libHTML::error('Error creating errorlog directory');
	}

	if ( ! is_file($errorlogDirectory.'/index.html') )
	{
		touch($errorlogDirectory.'/index.html') or libHTML::error('Error creating index file for errorlog directory');
	}

	if( JSERROR )
	{
		$errorlogDirectory .= '/js';
		if ( ! is_dir($errorlogDirectory) )
		{
			mkdir($errorlogDirectory) or libHTML::error('Error creating errorlog JavaScript directory');
		}
	}

	if ( ! is_writable($errorlogDirectory) )
	{
		libHTML::error("Error log directory not ready; does not exist, or no protective index file");
	}

	/*
	 * One file per error, named <unix time>_<pid>.txt, so that errors raised in the same second by different
	 * processes don't overwrite each other (the old <unix time>.txt naming kept only the last one).
	 *
	 * Repeats of the same error (same message with numbers masked, file and line) within libError::DEDUP_WINDOW
	 * seconds of the first are counted in <log dir>/.dedup/<signature>.json instead of being written out in
	 * full, so a crawler or a bot hitting the same bug in a loop produces one trace and a count rather than
	 * thousands of identical files. The admin status list shows the counts next to the log files.
	 */
	$errorlogFile = libError::newLogFile($errorlogDirectory);
	$occurrence = libError::recordOccurrence($errorlogDirectory, $signature, basename($errorlogFile));

	if( $occurrence['duplicate'] )
	{
		error_log("Error signature $signature repeated: occurrence ".$occurrence['count']." since ".date('c', $occurrence['first']).", trace in ".$occurrence['file']);
		$message .= 'The details of this error have already been logged recently and will be attended to by a developer.';
	}
	else
	{
		$header = 'Signature: '.$signature."\n";
		if( $occurrence['previous'] )
			$header .= 'Previously: '.$occurrence['previous']['count'].' occurrence(s) between '.date('c', $occurrence['previous']['first']).
				' and '.date('c', $occurrence['previous']['last']).', trace in '.$occurrence['previous']['file']."\n";

		error_log("Error logged to $errorlogFile");
		if ( @file_put_contents($errorlogFile, $header.$error) )
		{
			$message .= 'The details of this error have been successfully logged and will be attended to by a developer.';
		}
		else
		{
			$message .= 'This error could not be logged! Please contact the administrator about this error.';
		}
	}
	$message .= '</p>';
	
	// Don't return 200 if erroring out, return 509 so API calls can recognize a failure
	if( http_response_code() == 200 ) http_response_code(509);

	libHTML::error($message);

}

class libError
{
	/**
	 * Errors with the same signature within this many seconds of the first are counted, not logged in full
	 */
	const DEDUP_WINDOW = 3600;

	/**
	 * Files in the log directory matching this are error logs; capture 1 is the unix time the error was logged.
	 * Matches both the current <time>_<pid>.txt naming and the old <time>.txt naming.
	 */
	const LOG_FILE_PATTERN = '/^(\d+)(?:_\d+)*\.txt$/';

	public static function isLoggingEnabled()
	{
		return !( false === Config::errorlogDirectory() );
	}

	/**
	 * A hash identifying a kind of error, for de-duplication. Numbers in the message are masked so that
	 * e.g. different game IDs or SQL LIMIT offsets in otherwise identical messages share a signature.
	 *
	 * @return string
	 */
	public static function signature($errstr, $errfile, $errline)
	{
		return md5(preg_replace('/\d+/', '#', (string)$errstr).'|'.$errfile.'|'.$errline);
	}

	/**
	 * Choose a name for a new error log file in $dir which no other process will pick in the same second
	 *
	 * @return string Full path
	 */
	public static function newLogFile($dir)
	{
		$base = $dir.'/'.time().'_'.getmypid();
		$file = $base.'.txt';
		for( $i = 1; file_exists($file); $i++ )
			$file = $base.'_'.$i.'.txt';
		return $file;
	}

	/**
	 * Log a deprecation notice without ending the request. It is written out like an error, including the
	 * de-duplication, but at most once per request for each kind, and nothing here may end the request.
	 */
	public static function logDeprecation($errstr, $errfile, $errline)
	{
		static $logged = array();

		if( !self::isLoggingEnabled() )
			return;

		$signature = self::signature($errstr, $errfile, $errline);
		if( isset($logged[$signature]) )
			return;
		$logged[$signature] = true;

		$dir = Config::errorlogDirectory();
		if( !is_dir($dir) || !is_writable($dir) )
			return;

		$logFile = self::newLogFile($dir);
		$occurrence = self::recordOccurrence($dir, $signature, basename($logFile));
		if( $occurrence['duplicate'] )
			return;

		$log = 'Signature: '.$signature."\n";
		if( $occurrence['previous'] )
			$log .= 'Previously: '.$occurrence['previous']['count'].' occurrence(s) between '.date('c', $occurrence['previous']['first']).
				' and '.date('c', $occurrence['previous']['last']).', trace in '.$occurrence['previous']['file']."\n";
		$log .= 'Error: "'.$errstr."\"\n";
		$log .= "Deprecation notice; logged only, the request carried on\n";
		if( $errfile )
			$log .= 'Raised: "'.$errfile."\"\n";
		if( $errline )
			$log .= 'Line: "'.$errline."\"\n";
		$log .= "Trace:\n".(new Exception())->getTraceAsString()."\n";

		@file_put_contents($logFile, $log);
	}

	/**
	 * Record that an error with $signature has just occurred, in $dir/.dedup/<signature>.json.
	 *
	 * @param string $dir The error log directory
	 * @param string $signature From signature()
	 * @param string $logFile Basename of the file the trace will be written to if this is not a duplicate
	 * @return array duplicate => true if the error was already logged within DEDUP_WINDOW (only the count was
	 *   updated); count => occurrences in the current window; first => start of the window; file => basename
	 *   of the file holding the trace for this window; previous => the expired window's entry
	 *   (count/first/last/file) if a new window has just started, else null
	 */
	public static function recordOccurrence($dir, $signature, $logFile)
	{
		$now = time();
		$result = array('duplicate' => false, 'count' => 1, 'first' => $now, 'file' => $logFile, 'previous' => null);

		// This runs inside the error handler, so warnings from the file operations below (e.g. a race
		// creating the directory) must not reach it and trigger an error-within-error
		set_error_handler(function() { return true; });
		try
		{
			$indexDir = $dir.'/.dedup';
			if( !is_dir($indexDir) && !mkdir($indexDir) && !is_dir($indexDir) )
				return $result; // Can't de-duplicate; log everything as before

			$fh = fopen($indexDir.'/'.$signature.'.json', 'c+');
			if( !$fh )
				return $result;

			flock($fh, LOCK_EX);
			$entry = json_decode((string)stream_get_contents($fh), true);

			if( is_array($entry) && isset($entry['first'], $entry['count'], $entry['file']) && ($now - $entry['first']) < self::DEDUP_WINDOW )
			{
				$entry['count']++;
				$entry['last'] = $now;
				$result = array('duplicate' => true, 'count' => $entry['count'], 'first' => $entry['first'], 'file' => $entry['file'], 'previous' => null);
			}
			else
			{
				if( is_array($entry) && isset($entry['first'], $entry['count'], $entry['file']) )
					$result['previous'] = $entry;
				$entry = array('count' => 1, 'first' => $now, 'last' => $now, 'file' => $logFile);
			}

			ftruncate($fh, 0);
			rewind($fh);
			fwrite($fh, json_encode($entry));
			fflush($fh);
			flock($fh, LOCK_UN);
			fclose($fh);
		}
		finally
		{
			restore_error_handler();
		}

		return $result;
	}

	/**
	 * The error log files in the log directory, newest first
	 *
	 * @return array basename => unix time the error was logged
	 */
	public static function files()
	{
		if ( !libError::isLoggingEnabled() )
			return array();

		static $files;
		if ( isset($files) ) return $files;

		$dir = self::directory();

		if ( ! ( $handle = @opendir($dir) ) )
		{
			libHTML::error("Could not open error log directory");
		}

		$files = array();
		while ( false !== ( $file = readdir($handle) ) )
		{
			if( preg_match(self::LOG_FILE_PATTERN, $file, $match) )
				$files[$file] = (int)$match[1];
		}
		closedir($handle);

		arsort($files, SORT_NUMERIC);

		return $files;
	}

	/**
	 * Occurrence counts from the de-duplication index
	 *
	 * @return array log file basename => array(count, first, last, file)
	 */
	public static function counts()
	{
		$counts = array();

		if ( !libError::isLoggingEnabled() )
			return $counts;

		foreach( glob(self::directory().'/.dedup/*.json') ?: array() as $indexFile )
		{
			if( !is_file($indexFile) ) continue;
			$entry = json_decode((string)file_get_contents($indexFile), true);
			if( is_array($entry) && isset($entry['file'], $entry['count']) )
				$counts[$entry['file']] = $entry;
		}

		return $counts;
	}

	public static function directory()
	{
		static $dir;

		if ( isset($dir) ) return $dir;

		if ( !libError::isLoggingEnabled() )
			return false;

		$dir = Config::errorlogDirectory();

		if ( ! is_dir($dir) )
		{
			mkdir($dir);
		}

		if ( ! is_file($dir.'/index.html') )
		{
			touch($dir.'/index.html');
		}

		if ( ! is_writable($dir) )
		{
			libHTML::error("Error log directory not ready; does not exist, or no protective index file");
		}

		return $dir;
	}

	public static function stats()
	{
		global $Misc;

		$errorTimes = self::errorTimes();
		$count=count($errorTimes);

		$Misc->ErrorLogs = $count;

		return $count.' error log files'.($count>0?', last error log at '.libTime::text($errorTimes[0]):'');
	}

	/**
	 * The times of the error log files, newest first. Also updates the cached count in $Misc->ErrorLogs.
	 *
	 * @return int[]
	 */
	public static function errorTimes()
	{
		global $Misc;

		if ( !libError::isLoggingEnabled() )
			return array();

		$errorTimes = array_values(self::files());

		$Misc->ErrorLogs = count($errorTimes);

		return $errorTimes;
	}

	public static function clear()
	{
		global $Misc;

		if ( !libError::isLoggingEnabled() )
			return false;

		$dir = self::directory();

		foreach( array_keys(self::files()) as $file )
			unlink($dir.'/'.$file);

		foreach( glob($dir.'/.dedup/*.json') ?: array() as $indexFile )
			unlink($indexFile);

		$Misc->ErrorLogs = 0;
	}
}

?>
