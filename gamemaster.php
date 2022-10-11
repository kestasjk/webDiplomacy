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
 * @package GameMaster
 */

require_once('header.php');

require_once(l_r('gamemaster/game.php'));
require_once(l_r('gamemaster/misc.php'));

if ( $Misc->Panic )
{
	libHTML::notice(l_t('Game processing disabled'),
		l_t("Game processing has been temporarily disabled while we take care of an ".
		"unexpected problem. Please try again later, sorry for the inconvenience."));
}

if( php_sapi_name() == "cli" )
{
	// If requestion from the CLI allow the gamemaster secret to be passed via an environment variable (it's not that bad if it leaks it's just to limit resources)
	$_REQUEST['gameMasterSecret'] = $_SERVER['gameMasterSecret'];
}

if ( !( $User->type['Moderator']
	or ( isset($_REQUEST['gameMasterSecret']) and $_REQUEST['gameMasterSecret'] == Config::$gameMasterSecret )
	or ( isset($_REQUEST['gameMasterToken']) and libAuth::gamemasterToken_Valid($_REQUEST['gameMasterToken']) )
	) )
{
	libHTML::notice(l_t('Denied'), l_t('Only the cron script and moderators can run the gamemaster script.'));
}

if ( isset($_REQUEST['gameMasterSecret']) && $User->type['User'] && !$User->type['Moderator'] && $Misc->LastProcessTime == 0 )
{
	// The server has just been installed; make this user the admin now.
	$DB->sql_put("UPDATE wD_Users SET type = CONCAT(type,',Moderator,Admin') WHERE id = ".$User->id);
	$User->type['Moderator']=$User->type['Admin']=true;
	$Misc->LastProcessTime = time();
	$Misc->write();
	libHTML::notice(l_t('Admin'),l_t("You have been made admin. Please continue with the install instructions in README.txt."));
}

libHTML::starthtml(l_t('GameMaster'));

if( isset(Config::$customForumURL) )
{
	if( $MC->get('CustomForumFixTimer') === false )
	{
		// If there is a phpBB link ensure users are added to the registered users group, as there is some issue that affected 129 out of 100k users that
		// prevented those users from having any forum permissions.
		// TODO: Find out how this happens
		
		$DB->sql_put("INSERT INTO phpbb_user_group (user_id, group_id, user_pending) ".
			"SELECT u.user_id, 2 group_id, 0 user_pending ".
			"FROM phpbb_users u ".
			"WHERE NOT u.user_id IN ( SELECT user_id FROM phpbb_user_group WHERE group_id = 2 )");

		$MC->set('CustomForumFixTimer', time(), 24*60*60);
	}
}

print '<div class="content">';
$DB->sql_put("COMMIT"); // Unlock our user row, to prevent deadlocks below
// This means our $User object should only be used for reading from

$DB->get_lock('gamemaster',1);

ini_set('memory_limit',"40M");
ini_set('max_execution_time','40');


/*
 * - Update session table
 * - Update misc values (if running as admin/mod)
 * - Check last process time, pause processing/save current process time
 * - Check queue and games table for games to process, votes to enact, and system functions to perform
 */
print l_t('Updating session table').'<br />';
libGameMaster::updateSessionTable();

$statsDir=libCache::dirName('stats');
$onlineFile=$statsDir.'/onlineUsers.json';
$tabl=$DB->sql_tabl("SELECT userID FROM wD_Sessions");
$onlineUsers=array();
while(list($userID)=$DB->tabl_row($tabl))
	$onlineUsers[]=$userID;

file_put_contents($onlineFile, 'onlineUsers=$A(['.implode(',',$onlineUsers).']);');

//- Update misc values (if running as admin/mod)
if( $Misc->LastStatsUpdate < (time() - 37*60) )
{
	print l_t('Updating Misc values').'<br />';
	miscUpdate::errorLog();
	miscUpdate::forum();
	miscUpdate::game();
	miscUpdate::user();
	$Misc->LastStatsUpdate = time();
}

//- Check last process time, pause processing/save current process time
if ( ( time() - $Misc->LastProcessTime ) > Config::$downtimeTriggerMinutes*60 )
{
	libHTML::notice(l_t('Games not processing'),libHTML::admincp('resetLastProcessTime',null,l_t('Continue processing now')));
}

if( (time() - $Misc->LastGroupUpdate) > 10*60 )
{
	// Update the user group calculations
	require_once('lib/group.php');
	libGroup::generateGameRelationCache($Misc->LastGroupUpdate);	
	$Misc->LastGroupUpdate = time();
}

// Disable transactions while updating reliability ratings:
$DB->disableTransactions();

// Update the reliability ratings:
print l_t('Updating user phase/year counts and reliability ratings').'<br />';
libGameMaster::updateReliabilityRatings();

if( Config::$playNowDomain != null )
{
	// If there is a play-now domain set up ensure that games that have been left for over 24 hours don't linger and waste resources:
	// If a diplonow_ member hasn't logged onto a game for 24 hours set the member to vote for cancellation of the game.
	$DB->sql_put(
		"UPDATE wD_Members 
		SET votes='Cancel' 
		WHERE userID IN (
			SELECT id 
			FROM wD_Users 
			WHERE username LIKE 'diplonow%'
		) 
		AND timeLoggedIn < UNIX_TIMESTAMP()-24*60*60 
		AND status='Playing';"
	);
}

$DB->enableTransactions();
$DB->sql_put("BEGIN");

// Now apply any votes that need to be applied, and get any votes to process now:
print l_t('Finding and applying votes');
libGameMaster::findAndApplyGameVotes();

print l_t('Finding games where all players are ready');
// When users set their orders to ready it should set a memcached hint to process the game, but memcached isn't guaranteed and on dev systems
// it's not good to rely on it, so this acts as a backup to ensure when all players have set their orders to ready it will process
$readyGames = libGameMaster::findGameReadyVotes();

// Get the current processing time. It is important to save this at this point so that next process the next 
// LastProcessTime will exactly match this process' $currentProcessTime (this ensures all turns that pass over 1 year
// old get processed properly .. unless the last process time gets reset, in which case the turn counts need to be
// recalculated)
$currentProcessTime = time();

$Misc->LastProcessTime = $currentProcessTime;
$Misc->write();

# Take member / bot submitted game ID hints for games that may need early processing,
# and add them to the list of games to be checked
$gameIDHints = $MC->get('processHint');
// Set to 1 to ensure there is always a value, so that on startup this key will be reliably created
if( !$gameIDHints ) $MC->set('processHint','1'); // If memcached is restarted processHint will be unset
else $MC->replace('processHint','1');
if( $gameIDHints )
{
	$gameIDHints = explode(',',trim(''.$gameIDHints));
	$ids = array();
	foreach($gameIDHints as $id)
	{
		if ( $id && strlen($id) > 0 )
		{
			$ids[] = (int)$id;
		}
	}
	$ids = array_unique($ids, SORT_NUMERIC);
	$gameIDHints = "";
	if ( count($ids) > 0 ) {
		$gameIDHints = " OR id IN ( ".implode(',',$ids)." ) ";
	}
}
else
{
	$gameIDHints = "";
}


$startTime = $currentProcessTime; // Only do ~30 sec of processing per cycle
$tabl = $DB->sql_tabl("SELECT * FROM wD_Games
	WHERE processStatus='Not-processing' AND ( processTime <= ".time()." ".
	$gameIDHints." ". // Game IDs triggered from memcached
	( count($readyGames) > 0 ? " OR id IN ( ".implode(',',$readyGames)." ) " : "" ). // Game IDs triggered from ready votes
	" ) AND gameOver='No'"); // Using gameOver means one index can be used making the query much quicker
	//" ) AND NOT phase='Finished'");

$dirtyApiKeys = array(); // Keep track of any api keys with cached data that needs cleansing
while( (time() - $startTime)<30 && $gameRow=$DB->tabl_hash($tabl) )
{
	$Variant=libVariant::loadFromVariantID($gameRow['variantID']);
	$Game=$Variant->Game($gameRow);

	print '<a href="board.php?gameID='.$Game->id.'">gameID='.$Game->id.': '.$Game->name.'</a>: ';

	try
	{
		if( $Game->processStatus!='Crashed' && $Game->attempts > count($Game->Members->ByID)*2 )
		{
			$Game = $Variant->processGame($Game->id);
			$Game->crashed();
			$DB->sql_put("COMMIT");
			print 'Crashed.';
		}
		elseif( $Game->needsProcess() )
		{
			$DB->sql_put("UPDATE wD_Games SET attempts=attempts+1 WHERE id=".$Game->id);
			$DB->sql_put("COMMIT");
			print 'Rechecking.. ';

			$Game = $Variant->processGame($Game->id);
			if( $Game->needsProcess() )
			{
				print l_t('Processing..').' ';
				$Game->process();
				$DB->sql_put("UPDATE wD_Games SET attempts=0 WHERE id=".$Game->id);
				$DB->sql_put("COMMIT");
				print l_t('Processed.');
				
				// Flush any memcached data about this game which is may be dirty
				$stabl = $DB->sql_tabl("SELECT apiKey FROM wD_Members m INNER JOIN wD_ApiKeys a ON m.userID = a.userID WHERE m.gameID = ".$Game->id);
				while(list($apiKey) = $DB->tabl_row($stabl))
					$dirtyApiKeys[] = $apiKey;
			}
		}

		require_once('lib/pusher.php');
		libPusher::trigger("private-game" . $Game->id, 'overview', 'processed');
	}
	catch(Exception $e)
	{
		if( $e->getMessage() == "Abandoned" || $e->getMessage() == "Cancelled" )
		{
			$DB->sql_put("COMMIT");
			print l_t('Abandoned.');
		}
		else
		{
			$DB->sql_put("ROLLBACK");
			print l_t('Crashed: "%s".',$e->getMessage());
		}
	}

	print '<br />';
}

$dirtyApiKeys = array_unique($dirtyApiKeys);
foreach($dirtyApiKeys as $key)
	$MC->delete(str_replace(' ','_','api'.$key.'players/missing_orders'));

// Find any turns which have just passed more than one year old, and 
// If it took over 30 secs there may still be games to process
if( (time() - $startTime)>=30 )
{
	/*
	 * For when you're developing and just reloaded the DB from a backup,
	 * you usually have to refresh a few times before it runs out of games
	 * to process
	 */
	header('refresh: 4; url=gamemaster.php');
	print '<p class="notice">'.l_t('Timed-out; re-running').'</p>';
}
else
{
	// Finished all remaining games with time to spare; update the civil disorder and NMR counts
	//libGameMaster::updateCDNMRCounts();
}

print '</div>';
libHTML::footer();

?>
	
