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

    /**
     * Set a key only if it doesn't already have a value.
     *
     * @return mixed False if the key already had a value
     */
    public function setIfMissing($key, $value, $expirySeconds): mixed
    {
        return $this->redis->set($key, $value, array('nx', 'ex' => (int)$expirySeconds));
    }

    public function get($key): mixed
    {
        return $this->redis->get($key);
    }

    public function append($key, $value): mixed
    {
        return $this->redis->append($key, $value);
    }

    /**
     * Add to several integer counters in one round trip. INCRBY is atomic, so unlike a GET followed by a SET
     * concurrent requests can't lose each other's increments. A pipeline only batches the commands on the
     * client side, so it leaves no state on the persistent connection.
     *
     * @param array $increments Key => amount to add
     */
    public function incrementMany(array $increments): mixed
    {
        $pipeline = $this->redis->pipeline();
        foreach ($increments as $key => $amount)
            $pipeline->incrBy($key, (int)$amount);
        return $pipeline->exec();
    }

    public function delete($key): mixed
    {
        return $this->redis->del($key);
    }

    /**
     * Append to a list, then trim it to its newest $maxLength entries so a list nothing is reading from can't grow
     * without limit. The pipeline only batches the commands on the client side.
     */
    public function listPush($key, $value, $maxLength): mixed
    {
        $pipeline = $this->redis->pipeline();
        $pipeline->rPush($key, $value);
        $pipeline->lTrim($key, -$maxLength, -1);
        return $pipeline->exec();
    }

    /**
     * Remove and return up to $count entries from the front of a list. It's a script so the read and removal are
     * atomic without a MULTI, which would leave state on the persistent connection if it failed part way.
     *
     * @return array
     */
    public function listPopMany($key, $count): array
    {
        $items = $this->redis->eval("local items = redis.call('LRANGE', KEYS[1], 0, ARGV[1] - 1)
            redis.call('LTRIM', KEYS[1], ARGV[1], -1)
            return items", array($key, (int)$count), 1);
        return is_array($items) ? $items : array();
    }

    public function listLength($key): int
    {
        return (int)$this->redis->lLen($key);
    }

    /**
     * Take a lock which expires after $seconds if it isn't released first.
     *
     * @return string|false A token to pass to releaseLock(), or false if the lock is already held
     */
    public function acquireLock($key, $seconds): string|false
    {
        $token = bin2hex(random_bytes(8));
        return $this->redis->set($key, $token, array('nx', 'ex' => (int)$seconds)) ? $token : false;
    }

    /**
     * Release a lock, but only if it's still held with this token; if it expired it may now belong to someone else.
     */
    public function releaseLock($key, $token): mixed
    {
        return $this->redis->eval("if redis.call('GET', KEYS[1]) == ARGV[1] then return redis.call('DEL', KEYS[1]) end
            return 0", array($key, $token), 1);
    }

    public function setAddMany($key, array $members): mixed
    {
        return $this->redis->sAdd($key, ...array_values($members));
    }

    /**
     * Remove and return up to $count members of a set, in no particular order.
     *
     * @return array
     */
    public function setPopMany($key, $count): array
    {
        $members = $this->redis->sPop($key, (int)$count);
        return is_array($members) ? $members : array();
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