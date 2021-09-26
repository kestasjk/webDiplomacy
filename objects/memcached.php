<?php

function newMC()
{
	global $MC;
	$MC = new Memcached();
	$MC->addServer("127.0.0.1", 11211);
	// OPT_COMPRESSION needs to be off in order for append to work ..
	$MC->setOption(Memcached::OPT_COMPRESSION, false);
	return $MC;
}
$MC = newMC();

