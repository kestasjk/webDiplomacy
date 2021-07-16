<?php

function newMC()
{
	global $MC;
	$MC = new Memcached();
	$MC->setOption(Memcached::OPT_COMPRESSION, false);
	$MC->addServer("127.0.0.1", 11211);
	return $MC;
}
$MC = newMC();

