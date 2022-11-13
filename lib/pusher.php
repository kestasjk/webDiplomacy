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

require_once('vendor/autoload.php');

use Pusher\Pusher;

/**
 * An class that handles Pusher config and methods
 *
 * @package Base
 */
class libPusher
{

  private static function buildPusher()
  {
    return new Pusher(Config::$pusherAppKey, Config::$pusherAppSecret, Config::$pusherAppId, [
      'host' => Config::$pusherHost,
      'port' => Config::$pusherPort,
      'scheme' => isset(Config::$pusherScheme) ? Config::$pusherScheme : 'http',
      'encrypted' => isset(Config::$pusherForceTLS) ? Config::$pusherForceTLS : true,
      'useTLS' => isset(Config::$pusherForceTLS) ? Config::$pusherForceTLS : false,
    ]);
  }

  public static function trigger($channel, $event, $message)
  {
    $pusher = self::buildPusher();
    $result = $pusher->trigger($channel, $event, $message);
    return $result;
  }
}
