<?php
/*
 * Used by the beta build (the "build" script in ../package.json): prints the Google Analytics measurement ID
 * that config.php's custom header or footer puts on the PHP pages, so the beta board reports to the same GA
 * property as the rest of the site it's served from. public/index.html gets it as %REACT_APP_GA_ID_MAIN% and
 * %REACT_APP_GA_ID_PLAY%.
 *
 * php ga-measurement-id.php main|play
 *
 * "play" asks config.php as if the request were on Config::$playNowDomain, in case it picks the tag by domain.
 * Prints nothing if there's no config.php or no ID in it (e.g. in development), and the beta then loads no GA.
 */

if( php_sapi_name() != 'cli' ) die();

// Anything else printed would be built into the page as the ID
ini_set('display_errors', 'stderr');

chdir(__DIR__.'/../..');
if( !is_file('config.php') )
{
	fwrite(STDERR, "No config.php, so the beta won't load Google Analytics\n");
	exit;
}

define('IN_CODE', 1);
require_once('config.php');

if( isset($argv[1]) && $argv[1] == 'play' && !empty(Config::$playNowDomain) )
	$_SERVER['HTTP_HOST'] = Config::$playNowDomain;

if( preg_match('/\bG-[A-Z0-9]{6,}\b/', Config::customHeader().Config::customFooter(), $match) )
	print $match[0];
else
	fwrite(STDERR, "No Google Analytics measurement ID in config.php's customHeader() or customFooter(), so the beta won't load Google Analytics\n");
