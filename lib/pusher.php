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

//require_once('vendor/autoload.php');

//use Pusher\Pusher;

require_once('objects/redis.php');

/**
 * An class that handles Pusher config and methods; just redirects to Redis. libPushed will be phased out.
 *
 * @package Base
 */
class libPusher
{
  // If Redis is available use that instead, and the messages will be forwarded to a node.js SSE server
  private static ?RedisInterface $redis = null;
  private static function initialize()
  {
    global $Redis;
    
    if(self::$redis === null)
    {
      if( isset($Redis) )
      {
        // If Redis is already initialized, use that
        self::$redis = $Redis;
      }
      else if( isset(Config::$redisHost) && isset(Config::$redisPort) )
      {
        if( self::$redis === null )
        {
          // Use Redis as a Memcached replacement (temporary workaround to test like-for-like functionality)
          self::$redis = new RedisInterface(Config::$redisHost, Config::$redisPort);
          $Redis = self::$redis; // Set the global Redis variable for other uses
        }
      }
    }
  }

  public static function trigger($channel, $event, $message)
  {
    // Channel=private-game[gameID] or private-game[gameID]-country[toCountryID]
    // Event = overview / message
    // Message = vote-sent|processed for Event=overview, or messagesent for Event=message

    self::initialize();

    // For now sent to both for easier testing / migration:
    if( self::$redis !== null )
    {
      $result = self::$redis->publish($channel, json_encode(['event' => $event, 'data' => $message]));
    }
  }
}
