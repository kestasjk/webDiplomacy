<?php

require_once('vendor/autoload.php');

global $Pusher;
$Pusher = new Pusher\Pusher(
	Config::$pusherConfig['key'], 
	Config::$pusherConfig['secret'], 
	Config::$pusherConfig['id'], 
	['host' => Config::$pusherConfig['server'], 
	'port' => Config::$pusherConfig['port'], 
	'useTLS' => Config::$pusherConfig['useTLS']]
);

//print print_r($Pusher->getChannels(),true);

//$Pusher->trigger('chat-room', 'message', array('sender'=>'foo','content'=>'bar'));
