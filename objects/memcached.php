<?php

function newMC()
{
	global $MC;
	$MC = new Memcached();
	$MC->addServer(
		isset(Config::$memcachedHost) ? Config::$memcachedHost : '127.0.0.1', 
		isset(Config::$memcachedPort) ? Config::$memcachedPort : 11211 
	);
	// OPT_COMPRESSION needs to be off in order for append to work ..
	$MC->setOption(Memcached::OPT_COMPRESSION, false);
	return $MC;
}

$MC = newMC();
