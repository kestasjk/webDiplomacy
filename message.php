<?php

require_once('header.php');

if ( ! isset($_REQUEST['gameID']) )
{
    header('HTTP/1.1 400 Bad Request');
    die("No Game Specified");
}

$gameID = (int)$_REQUEST['gameID'];

require_once(l_r('objects/game.php'));
require_once(l_r('board/chatbox.php'));
require_once(l_r('gamepanel/gameboard.php'));

$Variant=libVariant::loadFromGameID($gameID);
libVariant::setGlobals($Variant);
$Game = $Variant->panelGameBoard($gameID);

if ( $Game->Members->isJoined() )
{
    // We are a member, load the extra code that we might need
    require_once(l_r('gamemaster/gamemaster.php'));
    require_once(l_r('board/member.php'));
    require_once(l_r('board/orders/orderinterface.php'));

    global $Member;
    $Game->Members->makeUserMember($User->id);
    $Member = $Game->Members->ByUserID[$User->id];
}

$CB = $Game->Variant->Chatbox();

// Now that we have retrieved the latest messages we can update the time we last viewed the messages
// Post messages we sent, and get the user we're speaking to
$msgCountryID = $CB->findTab();

$CB->postMessage($msgCountryID);

$DB->sql_put("COMMIT");

//RE-generate chat box

$messages = $CB->getMessages($msgCountryID);

if ( $messages == "" )
{
    $messages .= '<TR class="barAlt1"><td class="notice">
					'.l_t('No messages yet posted.').
        '</td></TR>';
}

echo '<TABLE class="chatbox">'.$messages.'</TABLE>';

?>