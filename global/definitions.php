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

define("VERSION", 183);

// The most no-press bot games botgamecreate.php lets run at once: play-now (anonymous) games, and
// registered users' games. status.php reports against the same numbers. A server can set either in
// config.php; a config.php from before these settings existed gets these defaults.
define("BOT_GAME_LIMIT_NOPRESS_ANONYMOUS", property_exists('Config', 'botGameLimitNoPressAnonymous') ? (int)Config::$botGameLimitNoPressAnonymous : 200);
define("BOT_GAME_LIMIT_NOPRESS", property_exists('Config', 'botGameLimitNoPress') ? (int)Config::$botGameLimitNoPress : 800);


// Some integer values which are named for clarity.

// System user IDs
define("GUESTID",1);

// InnoDB lock modes
define("NOLOCK", '');
define("SHARE", ' LOCK IN SHARE MODE');
define("UPDATE", ' FOR UPDATE');

// Allow easy renaming of the javascript and css directories, which prevents all sorts of cacheing
// problems (people complaining about bugs in old code)
define("JSDIR", 'javascript');
define("CSSDIR", 'css');

//Increment these versions whenever you update any js or css files for cachebusting
define("JSVERSION",1.95);
define("CSSVERSION",1.38);
