<?php
/*
    Copyright (C) 2004-2025 Kestas J. Kuliukas

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

 require_once('objects/redis.php');
 
function newMC()
{
	global $MC;
	if( isset(Config::$redisHost) && isset(Config::$redisPort) )
	{
		// Use Redis as a Memcached replacement (temporary workaround to test like-for-like functionality)
		$MC = new RedisInterface(Config::$redisHost, Config::$redisPort);
		return $MC;
	}
	else
	{
		$MC = new Memcached();
		$MC->addServer(
			isset(Config::$memcachedHost) ? Config::$memcachedHost : '127.0.0.1', 
			isset(Config::$memcachedPort) ? Config::$memcachedPort : 11211 
		);
		// OPT_COMPRESSION needs to be off in order for append to work ..
		$MC->setOption(Memcached::OPT_COMPRESSION, false);
		return $MC;
	}
}

$MC = newMC();
