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
 */
class RedisInterface
{
    private $redis;

    public function __construct($host = '127.0.0.1', $port = 6379)
    {
        $this->redis = new Redis();
        $this->redis->connect($host, $port);
    }

    public function set($key, $value, $expirySeconds = null)
    {
        // Memcached serializes values automatically, so do the same thing for Redis manually to get the same behavior:
        // If this isn't done serialized e.g. territory structures will be converted to string as "Array"
        $data = serialize($value);
        if ($expirySeconds) {
            return $this->redis->set($key, $data, $expirySeconds);
        } else {
            return $this->redis->set($key, $data);
        }
    }

    public function replace($key, $value, $expirySeconds = null)
    {
        if (!$this->redis->exists($key)) {
            return false;
        }
        return $this->set($key, $value, $expirySeconds);
        /*
        $options = ['xx']; // only set if key exists

        if ($expirySeconds) {
            $options['ex'] = $expirySeconds;
        }

        return $this->redis->set($key, $value, $options);
        */
    }

    public function get($key)
    {
        $data = $this->redis->get($key);
        if ($data !== false) {
            // Unserialize but catch any WARNING or error and ignore, so that if
            // an object definition has changed it will be handled
            try {
                set_error_handler(function($errno, $errstr) {
                    throw new \ErrorException($errstr, 0, $errno);
                });
                $result = unserialize($data);
                restore_error_handler();
                return $result;
            } catch (\ErrorException $e) {
                restore_error_handler();
                // Handle the unserialize warning here (e.g., log and return null)
                // error_log("Unserialize failed: " . $e->getMessage());
                return false;
            }
        } else {
            return false; // Key does not exist
        }
    }

    public function append($key, $value)
    {
        return $this->redis->append($key, $value);
    }

    public function delete($key)
    {
        return $this->redis->del($key);
    }

    public function publish($channel, $message)
    {
        return $this->redis->publish($channel, $message);
    }
}