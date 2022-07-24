<?php


function newPusher()
{
    global $Pusher;
    
    require_once('vendor/autoload.php');
    
    if( ! isset(Config::$pusherConfig) || strlen(Config::$pusherConfig['secret']) == 0 )
    {
        throw new Exception("Trying to initialize Pusher with no configuration.");
    }
    
    $Pusher = new Pusher\Pusher(
        Config::$pusherConfig['key'], 
        Config::$pusherConfig['secret'], 
        Config::$pusherConfig['id'], 
        ['host' => Config::$pusherConfig['server'], 
        'port' => Config::$pusherConfig['port'], 
        'useTLS' => Config::$pusherConfig['useTLS']]
    );

    return $Pusher;
}

//print print_r($Pusher->getChannels(),true);

//$Pusher->trigger('chat-room', 'message', array('sender'=>'foo','content'=>'bar'));
