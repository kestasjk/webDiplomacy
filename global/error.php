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
assert_options (ASSERT_CALLBACK, 'assert_handler');
assert_options (ASSERT_WARNING, 0);
error_reporting(E_STRICT | E_ALL | E_NOTICE);

function assert_handler ($file, $line, $expr)
{
	trigger_error("An assertion, ".$expr.", was not met as required.");
}

function exception_handler(Throwable $exception)
{
	$file = $exception->getFile();
	$trace = $exception->getTraceAsString();
	$line = $exception->getLine();

	trigger_error('A software exception was not caught: "'.$exception->getMessage().'"');
}

function error_handler($errno, $errstr, $errfile=false, $errline=false, $errcontext=false)
{
	global $DB, $User, $Game;

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

	if ( isset($User) and $User instanceof User )
	{
		$error .= 'userID = '.$User->id;

		if ( isset($Game) and $Game instanceof Game )
			$error .= ', gameID = '.$Game->id;
	}

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
	$User = null;
	if ( is_object($DB) )
	{
		$DB->sql_put("ROLLBACK");
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

	$errorlogFile = $errorlogDirectory.'/'.time().'.txt';
	if ( @file_put_contents($errorlogFile, $error) )
	{
		$message .= 'The details of this error have been successfully logged and will be attended to by a developer.';
	}
	else
	{
		$message .= 'This error could not be logged! Please contact the administrator about this error.';
	}
	$message .= '</p>';

	libHTML::error($message);

}

class libError
{
	public static function isLoggingEnabled()
	{
		return !( false === Config::errorlogDirectory() );
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

	public static function errorTimes()
	{
		global $Misc;

		if ( !libError::isLoggingEnabled() )
			return array();

		static $errorTimes;
		if ( isset($errorTimes) ) return $errorTimes;

		$dir = self::directory();

		if ( ! ( $handle = @opendir($dir) ) )
		{
			libHTML::error("Could not open error log directory");
		}

		$errorTimes = array();
		while ( false !== ( $file = readdir($handle) ) )
		{
			list($timestamp) = explode('.', $file);

			if ( intval($timestamp) < 1000 ) continue;
			else $errorTimes[] = intval($timestamp);

		}
		closedir($handle);

		sort($errorTimes, SORT_NUMERIC);
		$errorTimes = array_reverse($errorTimes);

		$Misc->ErrorLogs = count($errorTimes);

		return $errorTimes;
	}

	public static function clear()
	{
		global $Misc;

		if ( !libError::isLoggingEnabled() )
			return false;

		$dir = self::directory();

		$times = self::errorTimes();

		foreach($times as $time)
			unlink($dir.'/'.$time.'.txt');

		$Misc->ErrorLogs = 0;
	}
}

?>