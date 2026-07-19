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

// This script can be run from a webpage or from the command line:
// - Running from a webpage is good if processing games, as it can be triggered from another external system 
// so that if the server becomes inaccessible but is still running
// games will not continue to be processed while users can't access the site. 
// - Running from the CLI is better for longer running processes though, and more convenient for local 
// development via Docker.
if( php_sapi_name() == "cli" )
{
	define('RUNNINGFROMCLI', true);

	ob_end_flush();
	
	Config::$debug = true; // Always debug / show SQL etc when running from the CLI

	// If requestion from the CLI allow the gamemaster secret to be passed via an environment variable
	if( !isset($_SERVER['gameMasterSecret']))
	{
		die("gameMasterSecret environment variable not set");
	}
	$_REQUEST['gameMasterSecret'] = $_SERVER['gameMasterSecret'];
	$_SERVER['QUERY_STRING'] = ''; // Fix for libHTML expecting this (should no longer be needed, libHTML not being called)
}

// Permission check
if ( !( $User->type['Moderator']
	or ( isset($_REQUEST['gameMasterSecret']) and $_REQUEST['gameMasterSecret'] == Config::$gameMasterSecret )
	or ( isset($_REQUEST['gameMasterToken']) and libAuth::gamemasterToken_Valid($_REQUEST['gameMasterToken']) )
	) )
{
	libHTML::notice(l_t('Denied'), l_t('Only the cron script and moderators can run the gamemaster script.'));
}
elseif ( isset($_REQUEST['gameMasterSecret']) && $_REQUEST['gameMasterSecret'] == Config::$gameMasterSecret && 
	$User->type['User'] && !$User->type['Moderator'] && $Misc->LastProcessTime == 0 )
{
	// The server has just been installed; make this user the admin now.
	$DB->sql_put("UPDATE wD_Users SET type = CONCAT(type,',Moderator,Admin') WHERE id = ".$User->id);
	$User->type['Moderator']=$User->type['Admin']=true;
	$Misc->LastProcessTime = time();
	$Misc->write();
	libHTML::notice(l_t('Admin'),l_t("You have been made admin. Please continue with the install instructions in README.txt."));
}

ini_set('memory_limit',"200M");
ini_set('max_execution_time','300');

if( defined('RUNNINGFROMCLI') && isset($argv) )
{
	ini_set('memory_limit',"1024M");
	ini_set('max_execution_time','600');

	print "Running from CLI\n";

	// Disable transactions while doing batch updates:
	$DB->disableTransactions();

	if( in_array("RESTOREGAMES", $argv) )
	{
		$restoreGameIDs = array();
		foreach($argv as $arg)
		{
			if( substr($arg, 0, strlen("RESTOREGAMEIDS=")) === "RESTOREGAMEIDS=" )
			{
				$restoreGameIDs = explode(",", substr($arg, strlen("RESTOREGAMEIDS=")));
				break;
			}
		}
		foreach($restoreGameIDs as $restoreGameID)
		{
			$jsonData = file_get_contents(Config::$gameBackupDirectory.'/'.$restoreGameID.'.json');
			$data = json_decode($jsonData, true); // true means return as array instead of stdClass
			$sqlData = processGame::restoreBackupData($restoreGameID, $data);
			file_put_contents(Config::$gameBackupDirectory.'/'.$restoreGameID.'.sql', $sqlData);
		}
	}

	$DB->sql_put("COMMIT");

	if( !in_array("PROCESSGAMES", $argv) )
	{
		die('PROCESSGAMES not specified, ending now');
	}
}

if( !defined('RUNNINGFROMCLI') )
{
	libHTML::starthtml(l_t('GameMaster'));
	print '<div class="content">';
}

$DB->sql_put("COMMIT"); // Unlock our user row, to prevent deadlocks below

//- Check last process time, pause processing/save current process time
if ( ( time() - $Misc->LastProcessTime ) > Config::$downtimeTriggerMinutes*60 )
{
	libHTML::notice(l_t('Games not processing'),libHTML::admincp('resetLastProcessTime',null,l_t('Continue processing now')));
}

// A global lock for game processing:
$DB->get_lock('gamemaster',1);

$DB->enableTransactions();
$DB->sql_put("BEGIN");

/*
We have permissions and everything is locked, now look for games that need processing
*/

// Now apply any votes that need to be applied, and get any votes to process now:
print l_t('Finding and applying votes');
libGameMaster::findAndApplyGameVotes();

print l_t('Finding games where all players are ready');
$gameIDsToProcess = libGameMaster::findGameOrdersReady();

print l_t('Finding games where players have recently joined');
$gameIDsToProcess = array_merge($gameIDsToProcess, libGameMaster::findGamesWithRecentlyJoinedPlayers());

# Take member / bot submitted game ID hints for games that may need early processing,
# and add them to the list of games to be checked
$gameIDHints = $Redis->get('processHint');
if( $gameIDHints )
{
	$gameIDHints = explode(',',trim(''.$gameIDHints));
	foreach($gameIDHints as $id)
	{
		if ( $id && strlen($id) > 0 )
		{
			$gameIDsToProcess[] = (int)$id;
		}
	}
	$Redis->delete('processHint');
}

// Remove duplicates
$gameIDsToProcess = array_unique($gameIDsToProcess, SORT_NUMERIC);

// Get the current processing time. It is important to save this at this point so that next process the next 
// LastProcessTime will exactly match this process' $currentProcessTime (this ensures all turns that pass over 1 year
// old get processed properly .. unless the last process time gets reset, in which case the turn counts need to be
// recalculated)
$currentProcessTime = time();

$Misc->LastProcessTime = $currentProcessTime;
$Misc->write();

$startTime = $currentProcessTime; // Only do ~30 sec of processing per cycle
$tabl = $DB->sql_tabl("SELECT * FROM wD_Games
	WHERE processStatus='Not-processing' AND (
		processTime <= ".time()." ".
		( count($gameIDsToProcess) > 0 ? " OR id IN ( ".implode(',',$gameIDsToProcess)." ) " : "" ). // Game IDs triggered from ready votes
	" ) AND gameOver='No'
	AND NOT ( missingPlayerPolicy='Wait' AND EXISTS (
		SELECT 1 FROM wD_Members m
		WHERE m.gameID = wD_Games.id AND m.status='Playing'
			AND NOT FIND_IN_SET('Completed',m.orderStatus)
			AND NOT FIND_IN_SET('None',m.orderStatus)
	) )"); // Using gameOver means one index can be used making the query much quicker.
	// Wait-policy games with members yet to complete their orders can never pass needsProcess(),
	// so they are left out here rather than being reselected and skipped every cycle until their orders arrive.

$dirtyApiKeys = array(); // Keep track of any api keys with cached data that needs cleansing
while( (time() - $startTime)<30 && $gameRow=$DB->tabl_hash($tabl) )
{
	// This is used by drawMap.php / map.php
	$Redis->set('processing'.$gameRow['id'], time(), expirySeconds: 10); // Set a hint that nothing should be saved/cached for this game as it's being processed

	$Variant=libVariant::loadFromVariantID($gameRow['variantID']);
	$Game=$Variant->Game($gameRow);
	print '<a href="board.php?gameID='.$Game->id.'">gameID='.$Game->id.': '.$Game->name.'</a>: ';

	$gameUpdated = false; // Only backup / publish / wipe caches for games whose state actually changed this cycle
	try
	{
		// If we have already tried and failed to process this game twice, or it has a turn over 1000 (likely indicating a bug where processing is in a loop)
		// then flag the game as crashed:
		if( $Game->processStatus!='Crashed' && ( $Game->attempts > count($Game->Members->ByID)*2 || $Game->turn > 1000 ) )
		{
			$Game = $Variant->processGame($Game->id);
			$Game->crashed();
			$DB->sql_put("COMMIT");
			$gameUpdated = true;
			print 'Crashed.';
		}
		elseif( $Game->needsProcess() )
		{
			$DB->sql_put("UPDATE wD_Games SET attempts=attempts+1 WHERE id=".$Game->id);
			$DB->sql_put("COMMIT");
			print 'Rechecking.. ';
			// It does seem wasteful to get a Game, check if it needs processing, then get it again,
			// but we need to increment the attempts counter before processing to avoid infinite loops,
			// so when we commit we need to refetch the game to lock it after the commit.
			// Perhaps an attempt counter could be in Redis instead, but best not to change.
			// (This also ensures we have no other locks on other records before we fetch this game for processing)

			$Game = $Variant->processGame($Game->id);
			if( $Game->needsProcess() )
			{
				print l_t('Processing..').' ';
				$Game->process();
				$gameUpdated = true;
				$DB->sql_put("UPDATE wD_Games SET attempts=0 WHERE id=".$Game->id);
				$DB->sql_put("COMMIT");
				print l_t('Processed.');
			}
		}

		if( $gameUpdated )
		{
			if( $Game->phaseMinutes > 3*60 && $Game->playerTypes != 'MemberVsBots' )
			{
				// Take a backup of non-bot games with a phase length >3 hours to a table that can be written out without transactions
				processGame::backupGame($Game->id, false);
			}

			$Redis->trigger("private-game" . $Game->id, 'overview', 'processed');
		}
	}
	catch(Exception $e)
	{
		$gameUpdated = true; // The game may have been part-changed before the exception; wipe the cache below to be safe

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

	if( $gameUpdated )
	{
		// Wipe the whole cache; regenerating game maps used to be a big drain on performance but these days locking is more of a concern,
		// and disabling locking on map generation might mean that a user loads half the units up
		Game::wipeCache($Game->id);
	}

	$Redis->delete('processing'.$Game->id);
	
	print '<br />';
}

require_once(l_r('gamemaster/backgroundTasks.php'));
libBackgroundTasks::run();

if( defined('RUNNINGFROMCLI') ) 
{
	$DB->sql_put("COMMIT"); // Usually done in libHTML::footer()
	print "Gamemaster script ended successfully.\n";
}
else
{
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
	print '</div>';

	libHTML::footer();
}
