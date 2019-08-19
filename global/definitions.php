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
 * @package Base
 */

defined('IN_CODE') or die('This script can not be run by itself.');

define("VERSION", 159);


// Some integer values which are named for clarity.

// System user IDs
define("GUESTID",1);

// InnoDB lock modes
define("NOLOCK", '');
define("SHARE", ' LOCK IN SHARE MODE');
define("UPDATE", ' FOR UPDATE');

// The dynamic and static server links
define("DYNAMICSRV", Config::$facebookServerURL);
define("STATICSRV", Config::$facebookStaticURL);

// Allow easy renaming of the javascript and css directories, which prevents all sorts of cacheing
// problems (people complaining about bugs in old code)
define("JSDIR", 'javascript');
define("CSSDIR", 'css');

//Increment these versions whenever you update any js or css files for cachebusting
define("JSVERSION",1.4);
define("CSSVERSION",1.32);

if( !defined('FACEBOOK') )
	define('FACEBOOK',false);
?>
