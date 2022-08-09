<?php

function newMC()
{
	global $MC;
	$MC = new Memcached();
	$MC->addServer(Config::$memcachedHost, Config::$memcachedPort);
	// OPT_COMPRESSION needs to be off in order for append to work ..
	$MC->setOption(Memcached::OPT_COMPRESSION, false);
	return $MC;
}
$MC = newMC();

