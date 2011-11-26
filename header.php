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
/**
 * The header file; sanitize, initialize, get everything set up quickly
 *
 * @package Base
 */

//$_SERVER['HTTP_CACHE_CONTROL'] = 'max-age=10000';

/*
function setExpires($expires) {
 header('Expires: '.gmdate('D, d M Y H:i:s', time()+$expires).'GMT');
 header('Last-Modified: '.gmdate('D, d M Y H:i:s', time()-$expires).'GMT');
}

setExpires(600);
echo ( 'This page will self destruct in 10 seconds<br />' );
echo ( 'The GMT is now '.gmdate('H:i:s').'<br />' );
echo ( 'The GMT is now '.gmdate('H:i:s',time()).'<br />' );
echo ( '<a href="'.$_SERVER['PHP_SELF'].'">View Again</a><br />' );

die();*/

if( strpos($_SERVER['PHP_SELF'], 'header.php') )
{
	die("You can't view this document by itself.");
}

if( !defined('IN_CODE') )
	define('IN_CODE', 1); // A flag to tell scripts they aren't being executed by themselves

require_once('config.php');

require_once('global/definitions.php');

if( strlen(Config::$serverMessages['ServerOffline']) )
	die('<html><head><title>Server offline</title></head>'.
		'<body>'.Config::$serverMessages['ServerOffline'].'</body></html>');


/*
 * If register_globals in enabled remove globals.
 */
if (ini_get('register_globals') or get_magic_quotes_gpc())
{
	function stripslashes_deep(&$value)
	{
		if ( is_array($value) )
			return array_map('stripslashes_deep', $value);
		else
			return stripslashes($value);
	}

	$defined_vars = get_defined_vars();
	while( list($var_name, $var_value) = each($defined_vars) )
	{
		switch( $var_name )
		{
			case "_POST":
			case "_GET":
			case "_REQUEST":
			case "_COOKIE":
				if (get_magic_quotes_gpc())
				{
					// Strip slashes if magic quotes added slashes
					${$var_name} = stripslashes_deep(${$var_name});
				}
				break;
			case "_SERVER":
				break; // Don't strip slashes on _SERVER variables, slashes aren't added to these
			default:
				unset( ${$var_name} ); // Remove register_globals variables
				break;
		}
	}

	unset($defined_vars);
}

// Support the legacy request variables
if ( isset($_REQUEST['gid']) ) $_REQUEST['gameID'] = $_REQUEST['gid'];
if ( isset($_REQUEST['uid']) ) $_REQUEST['userID'] = $_REQUEST['uid'];

// Reset globals
// FIXME: Resetting this means $GLOBALS['asdf'] is no longer kept in sync with global $asdf. This causes problems during construction
$GLOBALS = array();
$GLOBALS['scriptStartTime'] = microtime(true);

ini_set('memory_limit',"8M"); // 8M is the default
ini_set('max_execution_time','8');
//ini_set('session.cache_limiter','public');
ignore_user_abort(TRUE); // Carry on if the user exits before the script gets printed.
	// This shouldn't be necessary for data integrity, but either way it may save reprocess time

ob_start(); // Buffer output. libHTML::footer() flushes.

// All the standard includes.
require_once('lib/cache.php');
require_once('lib/time.php');
require_once('lib/html.php');

require_once('objects/silence.php');
require_once('objects/user.php');
require_once('objects/game.php');

require_once('global/error.php');
// Set up the error handler

date_default_timezone_set('UTC');

// Create database object
require_once('objects/database.php');
$DB = new Database();

// Set up the misc values object
require_once('objects/misc.php');
global $Misc;
$Misc = new Misc();

if ( $Misc->Version != VERSION )
{
	require_once('install/install.php');
}

// Taken from the php manual to disable cacheing.
header("Last-Modified: Mon, 26 Jul 1997 05:00:00 GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);

if( defined('FACEBOOKSCRIPT') ) {
	require_once('facebook/facebook-platform/php/facebook.php');
	$facebook=new Facebook(Config::$facebookAPIKey,Config::$facebookSecret);
	$facebook->require_frame();

	$fb_user=$facebook->get_loggedin_user();

	if( !$fb_user ) {
		if( !isset($_REQUEST['wD_FB_AuthNow'])) {
			libHTML::notice('Not authorized','To play in webDiplomacy games you need to authorize this application, so that
				it can send you notifications informing you when a game you\'re playing in needs your attention.
				Please <a href="index.php?wD_FB_AuthNow=on">authorize this application</a> to continue.');
		} else {
			$fb_user=$facebook->require_login();
		}
	}
}

require_once('lib/auth.php');

if( !defined('AJAX') )
{
	if( isset($_REQUEST['logoff']) )
	{
		$success=libAuth::keyWipe();
		$User = new User(GUESTID); // Give him a guest $User
		header('refresh: 4; url=logon.php?noRefresh=on');
		libHTML::notice("Logged out","You have been logged out, and are being redirected to the logon page.");
	}

	global $User;
	$User = libAuth::auth();

	if ( $User->type['Admin'] )
	{
		Config::$debug=true;

		if ( isset($_REQUEST['auid']) || isset($_SESSION['auid']) )
			$User = libAuth::adminUserSwitch($User);
		else
			define('AdminUserSwitch',$User->id);
	}
	elseif ( $Misc->Maintenance )
	{
		unset($DB); // This lets libHTML know there's a problem
		libHTML::error(Config::$serverMessages['Maintenance']);

	}
}

// This gets called by libHTML::footer
function close()
{
	global $DB, $Misc;

	// This isn't put into the database destructor in case of dieing due to an error

	if ( is_object($DB) )
	{
		$Misc->write();

		if( !defined('ERROR'))
			$DB->sql_put("COMMIT");

		unset($DB);
	}

	ob_end_flush();

	die();
}

?>