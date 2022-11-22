<?php
/*
    Copyright (C) 2004-2010 Kestas J. Kuliukas

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
 * @package Board
 */

require_once('header.php');

if ( ! isset($_REQUEST['gameID']) )
{
	libHTML::error(l_t("You haven't specified a game to view, please go back to the game listings and choose one."));
}

$gameID = (int)$_REQUEST['gameID'];

// If we are trying to join the game lock it for update, so it won't get changed while we are joining it.
if ( $User->type['User'] && ( isset($_REQUEST['join']) || isset($_REQUEST['joinBeta']) || isset($_REQUEST['leave']) ) && libHTML::checkTicket() )
{
	try
	{
		require_once(l_r('gamemaster/game.php'));

		$Variant=libVariant::loadFromGameID($gameID);
		libVariant::setGlobals($Variant);
		$Game = $Variant->processGame($gameID);

		// If viewing an archive page make that the title, otherwise us the name of the game
		libHTML::starthtml(isset($_REQUEST['viewArchive'])?$_REQUEST['viewArchive']:$Game->titleBarName());

		if ( isset($_REQUEST['join']) || isset($_REQUEST['joinBeta']))
		{
			// They will be stopped here if they're not allowed.
			$Game->Members->join(
				( $_REQUEST['gamepass'] ?? null ),
				( $_REQUEST['countryID'] ?? null ),
				( $_REQUEST['joinBeta'] ?? null ) );
		}
		elseif ( isset($_REQUEST['leave']) )
		{
			$reason=$Game->Members->cantLeaveReason();

			if($reason)
				throw new Exception(l_t("Can't leave game; %s.",$reason));
			else
				$Game->Members->ByUserID[$User->id]->leave();
		}
	}
	catch(Exception $e)
	{
		// Couldn't leave/join game
		libHTML::error($e->getMessage());
	}
	die(); // This point in the code isn't reached, all code paths above will have terminated by here (this means no need to get a different Game object)
}

try
{
	require_once(l_r('objects/game.php'));
	require_once(l_r('board/chatbox.php'));
	require_once(l_r('gamepanel/gameboard.php'));
	$Variant=libVariant::loadFromGameID($gameID);
	libVariant::setGlobals($Variant);
	$Game = $Variant->panelGameBoard($gameID);
	
	// If viewing an archive page make that the title, otherwise us the name of the game
	libHTML::starthtml(isset($_REQUEST['viewArchive'])?$_REQUEST['viewArchive']:$Game->titleBarName());

	if ( $Game->Members->isJoined() && !$Game->Members->isTempBanned() )
	{
		// We are a member, load the extra code that we might need
		require_once(l_r('gamemaster/gamemaster.php'));
		require_once(l_r('board/member.php'));
		require_once(l_r('board/orders/orderinterface.php'));
		global $Member;
		$Game->Members->makeUserMember($User->id);
		$Member = $Game->Members->ByUserID[$User->id];

		// As a member check for any vote submissions, order submissions, and if the game needs to be processed

		// Before HTML pre-generate everything and check input, so game summary header will be accurate
		if( $Member->status == 'Playing' && $Game->phase != 'Finished' )
		{
			if( $Game->phase != 'Pre-game' )
			{
				if(isset($_REQUEST['Unpause'])) $_REQUEST['Pause']='on'; // Hack because Unpause = toggle Pause

				foreach(Members::$votes as $possibleVoteType) {
					if( isset($_REQUEST[$possibleVoteType]) && isset($Member) && libHTML::checkTicket() )
					{
						$Member->toggleVote($possibleVoteType);
					}
				}

				if ( $Game->phase !='Finished' )
				{
					$OI = OrderInterface::newBoard();
					$OI->load();

					$Orders = '<div id="orderDiv'.$Member->id.'">'.$OI->html().'</div>';
					unset($OI);
				}
			}

			if( $Game->needsProcess() )
			{
				$MC->append('processHint',','.$Game->id);
			}
		}
	}

	// Process the chatbox / game messages
	if( $Game->phase != 'Pre-game' && ( (isset($Member) && is_a($Member, 'userMember') ) || $User->type['Moderator'] || $Game->isDirector($User->id) ) )
	{
		$CB = $Game->Variant->Chatbox();
		// Now that we have retrieved the latest messages we can update the time we last viewed the messages
		// Post messages we sent, and get the user we're speaking to
		$msgCountryID = $CB->findTab();
		$CB->postMessage($msgCountryID);
		$forum = $CB->output($msgCountryID);
		unset($CB);
		libHTML::$footerScript[] = 'makeFormsSafe();';
	}
}
catch(Exception $e)
{
	// Couldn't load game
	libHTML::error(l_t("Couldn't load specified game; this probably means this game was cancelled or abandoned.")." ".
		($User->type['User'] ? l_t("Check your <a href='index.php' class='light'>notices</a> for messages regarding this game."):''));
}

if ( isset($_REQUEST['viewArchive']) )
{
	// Start HTML with board gamepanel header
	print '</div>';
	print '<div class="content-bare content-board-header">';
	print '<div class="boardHeader">'.$Game->contentHeader().'</div>';
	print '</div>';
	print '<div class="content content-follow-on">';

	print '<p><a href="board.php?gameID='.$Game->id.'" class="light">'.l_t('&lt; Return').'</a></p>';

	switch($_REQUEST['viewArchive'])
	{
		case 'Orders': require_once(l_r('board/info/orders.php')); break;
		case 'Messages': require_once(l_r('board/info/messages.php')); break;
		case 'Graph': require_once(l_r('board/info/graph.php')); break;
		case 'Maps': require_once(l_r('board/info/maps.php')); break;
		case 'Gif': require_once(l_r('board/info/gif.php')); break;
		case 'Reports':
			require_once(l_r('lib/modnotes.php'));
			libModNotes::checkDeleteNote();
			libModNotes::checkInsertNote();
			print libModNotes::reportBoxHTML('Game',$Game->id);
			print libModNotes::reportsDisplay('Game', $Game->id);
			break;
		default: libHTML::error(l_t("Invalid info parameter given."));
	}

	print '</div>';
	libHTML::footer();
}

// The error where this isn't a panelGameBoard but is probably a processGame appears to be related to someone rejoining a game they were previously
// in civil disorder for. Need to trace how $Game gets set up when rejoining from a CD TODO
$map = $Game->mapHTML();

/*
 * Now there is $orders, $form, and $map. That's all the HTML cached, now begin printing
 */

if ( $Game->watched() && isset($_REQUEST['unwatch'])) {
	print '<div class="content-notice gameTimeRemaining">'
		.'<form method="post" action="redirect.php">'
		.libAuth::formTokenHTML()
		.'Are you sure you wish to remove this game from your spectated games list? '
		.'<input type="hidden" name="gameID" value="'.$Game->id.'">'
		.'<input type="submit" class="form-submit" name="unwatch" value="Confirm">
		</form></div>';
}


print '</div>';
print '<div class="content-bare content-board-header">';
print '<div class="boardHeader">'.$Game->contentHeader().'</div>';
print '</div>';
print '<div class="content content-follow-on variant'.$Game->Variant->name.'">';

// Now print the forum, map, orders, and summary
if ( isset($forum) )
{
	print $forum.'<div class="hr"></div>';
}

print $map.'<div class="hr"></div>';

if (isset($Orders))
{
	print $Orders.'<div class="hr"></div>';
}

print $Game->summary(true);


if($User->type['Moderator'])
{
	$modActions=array();

	if($Game->gameOver=='No')
	{
		$modActions[] = libHTML::admincpType('Game',$Game->id);

		$modActions[] = libHTML::admincp('resetMinimumBet',array('gameID'=>$Game->id), l_t('Reset Min Bet'));
		$modActions[] = libHTML::admincp('togglePause',array('gameID'=>$Game->id), l_t('Toggle pause'));
		if($Game->processStatus=='Not-processing')
		{
			$modActions[] = libHTML::admincp('setProcessTimeToNow',array('gameID'=>$Game->id), l_t('Process now'));
			$modActions[] = libHTML::admincp('setProcessTimeToPhase',array('gameID'=>$Game->id), l_t('Reset Phase'));
		}

		if($User->type['Admin'])
		{
			if($Game->processStatus == 'Crashed')
				$modActions[] = libHTML::admincp('unCrashGames',array('excludeGameIDs'=>''), l_t('Un-crash all crashed games'));
		}

		if( $Game->phase!='Pre-game' && !$Game->isMemberInfoHidden() )
		{
			$userIDs=implode('%2C',array_keys($Game->Members->ByUserID));
			$modActions[] = '<br /></br>'.l_t('Multi-check:');
			foreach($Game->Members->ByCountryID as $countryID=>$Member)
			{
				$modActions[] = '<a href="admincp.php?tab=Multi-accounts&aUserID='.$Member->userID.'" class="light">'.
					$Member->memberCountryName().'('.$Member->username.')</a>';
			}
		}
	}

	if($modActions)
	{
		print '<div class="hr"></div>';
		print '<p class="notice">';
		print implode(' - ', $modActions);
		print '</p>';
		print '<div class="hr"></div>';
	}
}

if( $Game->isDirector($User->id) )
{
	define("INBOARD", true);

	require_once(l_r("admin/adminActionsForms.php"));
}

print '</div>';

libHTML::footer();

?>
