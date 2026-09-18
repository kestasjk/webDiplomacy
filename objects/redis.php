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

/**
 * This is an interface to Redis. Memcached was being used, and working fine, for storing 
 * cached values for retrieval without having to constantly fetch from the DB. However pusher
 * / cloudfare durable objects/workers were being used to notify clients of updates, and this
 * was causing large costs due to it being a third party service that was being used wastefully,
 * with capability to persist messages, run code between events, complex authentication, etc,
 * which isn't needed to simply notify clients efficiently of updates.
 * 
 * Redis can do everything Memcached can do, but Memcached can't trigger PUB/SUB events like 
 * Redis; so using Memcached for this purpose would mean polling memcached on a loop for each
 * client.
 * Since Redis is also used by the bots it makes more sense to replace Memcached with Redis.
 * 
 * It is used as a key-value store for caching data in the server to reduce DB hits, and to
 * receive events from the server like new messages / votes / game updates, which can be 
 * subscribed to by a node.js process which serves SSE (Server-Sent Events) to clients.
 * 
 * TODO: This isn't necessary anymore, would be easier to use the Redis class directly where needed.
 */
class RedisInterface
{
    private $redis;

    public function __construct($host = '127.0.0.1', $port = 6379)
    {
        if (!class_exists('Redis')) {
            throw new Exception('Redis PHP extension is not installed');
        }
        $this->redis = new Redis();
        // Persistent connection, reused by each PHP-FPM worker across requests instead of opening a new
        // TCP connection for every page load / API call. This is only safe because nothing here changes
        // connection state (SELECT, AUTH, MULTI, SUBSCRIBE, etc) that would carry over into the next request.
        $this->redis->pconnect($host, $port);
    }

    public function set($key, $value, $expirySeconds = null): mixed
    {
        if ($expirySeconds) {
            return $this->redis->set($key, $value, $expirySeconds);
        } else {
            return $this->redis->set($key, $value);
        }
    }

    public function get($key): mixed
    {
        return $this->redis->get($key);
    }

    public function append($key, $value): mixed
    {
        return $this->redis->append($key, $value);
    }

    public function delete($key): mixed
    {
        return $this->redis->del($key);
    }

    public function publish($channel, $message): mixed
    {
        return $this->redis->publish($channel, $message);
    }
    
    public function trigger($channel, $event, $message): mixed
    {
        return $this->publish(channel: $channel, message: json_encode(['event' => $event, 'data' => $message]));
    }

    public function flushDB(): mixed
    {
        return $this->redis->flushDB();
    }
}