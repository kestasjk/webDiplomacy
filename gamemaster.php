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
	define('RUNNINGFROMCLI', true);

	ob_end_flush();
	
	Config::$debug = true; // Always debug / show SQL etc when running from the CLI
	// If requestion from the CLI allow the gamemaster secret to be passed via an environment variable (it's not that bad if it leaks it's just to limit resources)
	if( !isset($_SERVER['gameMasterSecret']))
	{
		die("gameMasterSecret environment variable not set");
	}
	$_REQUEST['gameMasterSecret'] = $_SERVER['gameMasterSecret'];
	$_SERVER['QUERY_STRING'] = ''; // Fix for libHTML expecting this
}


if ( !( $User->type['Moderator']
	or ( isset($_REQUEST['gameMasterSecret']) and $_REQUEST['gameMasterSecret'] == Config::$gameMasterSecret )
	or ( isset($_REQUEST['gameMasterToken']) and libAuth::gamemasterToken_Valid($_REQUEST['gameMasterToken']) )
	) )
{
	libHTML::notice(l_t('Denied'), l_t('Only the cron script and moderators can run the gamemaster script.'));
}

ini_set('memory_limit',"100M");
ini_set('max_execution_time','60');

if( defined('RUNNINGFROMCLI') && isset($argv) )
{
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

	// Restore with RESTOREGAMES RESTOREGAMEIDS=1234,1235,1236. This will output SQL which can be restored to the backup directory
	if( in_array("BACKUPGAMES", $argv) )
	{
		print "Backing up games\n";

		if( !isset(Config::$gameBackupDirectory) || !is_dir(Config::$gameBackupDirectory) )
			throw new Exception(Config::$gameBackupDirectory." is not set or is not a directory");

		$backupTime = time();

		$tabl = $DB->sql_tabl("SELECT DISTINCT gameID FROM wD_Backup_Log WHERE timestamp >= ".$Misc->LastBackupUpdate);
		while(list($gameID) = $DB->tabl_row($tabl))
		{
			print 'Backing up '.$gameID;
			$data = processGame::getBackupData($gameID);
			$jsonData = json_encode($data);
			file_put_contents(Config::$gameBackupDirectory.'/'.$gameID.'.json', $jsonData);
		}


		// ALso backup critical user data, as without this a restore of games couldn't be associated to new users
		print 'Backing up new users';
		$tabl = $DB->sql_tabl("SELECT id, username, email, password FROM wD_Users WHERE timeJoined >= ".$Misc->LastBackupUpdate);
		$newUserRows = array();
		while($row = $DB->tabl_row($tabl))
		{
			$newUserRows[] = $row;
		}
		$DB->sql_put("COMMIT");
		if( count($newUserRows) > 0 )
		{
			print 'Backing up '.count($newUserRows).' new users';
			$jsonData = json_encode($newUserRows);
			file_put_contents(Config::$gameBackupDirectory.'/newUsers_'.$backupTime.'.json', $jsonData);
		}

		print 'Backups complete';
		$Misc->LastBackupUpdate = $backupTime;
		$Misc->write();
	}

	if( in_array("NMRWARNING", $argv) )
	{
		print "Generating NMR warnings\n";
		
		$nmrWarningUpdateTime = time();

		$tabl = $DB->sql_tabl("SELECT u.username, u.email, m.userID, g.id gameID, g.name gameName, 
				g.phaseMinutes, g.processTime, m.countryID, g.phase, g.phaseMinutesRB 
			FROM wD_Members m 
			INNER JOIN wD_Games g ON g.id = m.gameID 
			INNER JOIN wD_Users u ON u.id = m.userID 
			WHERE m.status = 'Playing' AND orderStatus = 'None' 
				AND g.gameOver='No' AND g.processStatus = 'Not-processing' 
				AND g.phaseMinutes > 60 
				AND (
					(
						(COALESCE(phaseMinutesRB,0) <= 0 OR phase='Diplomacy') 
						AND 100*(processTime - ".$nmrWarningUpdateTime.")/(60*g.phaseMinutes) < 20
						AND 100*(processTime - ".$Misc->LastNMRWarningUpdate.")/(60*g.phaseMinutes) >= 20
					) 
					OR (
						COALESCE(phaseMinutesRB,0) > 0 
						AND phase <> 'Diplomacy' 
						AND 100*((processTime - ".$nmrWarningUpdateTime.")/(60*g.phaseMinutesRB)) < 20
						AND 100*(processTime - ".$Misc->LastNMRWarningUpdate.")/(60*g.phaseMinutes) >= 20
					)
				) 
				AND g.playerTypes <> 'MemberVsBots' 
				AND g.phase <> 'Retreats' AND g.phase <> 'Builds' /* Until these phases are behaving correctly */
				AND g.sandboxCreatedByUserID IS NULL 
				AND g.processTime > ".$nmrWarningUpdateTime."");
		
		// Aggregate warnings by user email address, so we only send one email per user
		$nmrWarningMessagesByUserEmail = array();
		while($row = $DB->tabl_hash($tabl))
		{
			$nmrWarningMessagesByUserEmail[$row['email']][] = $row;
		}
		$DB->sql_put("COMMIT");

		require_once(l_r('objects/mailer.php'));
		$Mailer = new Mailer();
		foreach($nmrWarningMessagesByUserEmail as $email => $warnings)
		{
			$username = $warnings[0]['username'];
			$links = array();
			foreach($warnings as $warning)
			{
				$links[] = '<a href="https://webdiplomacy.net/board.php?gameID='.$warning['gameID'].'">'.
					htmlentities($warning['gameName']).
					'</a> - '.
					l_t($warning['phase']).' - '.
					'<strong>'.libTime::remainingText($warning['processTime']).' remaining</strong>';
			}
			print 'E-mailing '.$email.' about '.count($links).' games'."\n";
			$Mailer->Send(
				array($email=>$username), 
				l_t('NMR Warning: No orders submitted!'),
				l_t("You haven't submitted orders for the following game(s), which will be processed soon (less than 20% of phase left)!<br><br>").
				l_t("Not submitting orders will affect your reliability rating, and makes the game less enjoyable for others.<br><br>Please use the link(s) below to submit orders for these games asap!<br><br>").
				"<ul><li>".implode('</li><li>',$links)."</li></ul>"
			);
		}

		$Misc->LastNMRWarningUpdate = $nmrWarningUpdateTime;
		$Misc->write();
		
	}

	if( in_array("NOTIFICATIONS", $argv) )
	{
		print "Running notification updates\n";
		
	}

	if( in_array("GROUPUPDATE", $argv) )
	{
		$groupUpdateTime = time();
		print "Running group relationship updates\n";
	
		// Update the user group calculations
		require_once('lib/group.php');
		libGroup::generateGameRelationCache($Misc->LastGroupUpdate);
		
		$Misc->LastGroupUpdate = $groupUpdateTime;
		$Misc->write();
	}

	if( in_array("CONNECTIONUPDATE", $argv) )
	{
		$connectionUpdateTime = time();

		print "Running user connection updates\n";

		// Update the user connections
		require_once('gamemaster/userconnections.php');
		
		print l_t('Updating user connection stats').'<br />';
		libUserConnections::updateUserConnections($Misc->LastConnectionUpdate);
	
		$Misc->LastConnectionUpdate = $connectionUpdateTime;
		$Misc->write();
	}

	if( in_array("TIDYWATCHED", $argv) )
	{
		print l_t('Clearing old watched game records').'<br />';
		$DB->sql_put("DELETE wg FROM wD_WatchedGames wg LEFT JOIN wD_Games g ON g.id = wg.gameID WHERE g.id IS NULL OR g.phase = 'Finished' OR g.gameOver <> 'No'");
	}

	if( in_array("RELIABILITYRATINGS", $argv) )
	{
		$DB->get_lock('gamemaster',1);
		// Update the reliability ratings:
		print l_t('Updating user phase/year counts and reliability ratings').'<br />';
		libGameMaster::updateReliabilityRatings();
	}

	$DB->sql_put("COMMIT");

	if( !in_array("PROCESSGAMES", $argv) )
	{
		die('PROCESSGAMES not specified, ending now');
	}
}

$DB->get_lock('gamemaster',1);

if ( isset($_REQUEST['gameMasterSecret']) && $_REQUEST['gameMasterSecret'] == Config::$gameMasterSecret && 
	$User->type['User'] && !$User->type['Moderator'] && $Misc->LastProcessTime == 0 )
{
	// The server has just been installed; make this user the admin now.
	$DB->sql_put("UPDATE wD_Users SET type = CONCAT(type,',Moderator,Admin') WHERE id = ".$User->id);
	$User->type['Moderator']=$User->type['Admin']=true;
	$Misc->LastProcessTime = time();
	$Misc->write();
	libHTML::notice(l_t('Admin'),l_t("You have been made admin. Please continue with the install instructions in README.txt."));
}

// If running from the CLI don't display the HTML header, but do run it as it may do something relevent to this script(?), but I don't think it does
if( defined('RUNNINGFROMCLI') ) ob_start();
libHTML::starthtml(l_t('GameMaster'));
if( defined('RUNNINGFROMCLI') ) ob_end_clean();

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
if( $Misc->LastStatsUpdate < (time() - 60) )
{
	print l_t('Updating Misc values').'<br />';
	miscUpdate::errorLog();
	miscUpdate::forum();
	miscUpdate::game();
	miscUpdate::user();
	
	// Keep sandbox games from clogging things up using a hack for now, and ensure this doesn't cause paused games to error:
	$DB->sql_put("UPDATE wD_Games SET processTime = 2000000000, pauseTimeRemaining = NULL WHERE name LIKE 'SB_%' AND processStatus <> 'Paused'");

	// Cancel bot games that haven't been used for an hour if they are anonymous:
	$DB->sql_put("UPDATE wD_Games g INNER JOIN wD_Members m ON m.gameID = g.id INNER JOIN wD_Users u ON u.id = m.userID LEFT JOIN wD_Sessions s ON s.userID = u.id SET g.gameOver='Draw', g.phase= 'Finished' WHERE NOT u.type LIKE '%Bot%' AND g.gameOver = 'No' AND g.playerTypes = 'MemberVsBots' AND (u.timeLastSessionEnded < UNIX_TIMESTAMP() - 2*60*60 AND u.timeJoined < UNIX_TIMESTAMP() - 2*60*60 AND s.userID IS NULL) AND u.username LIKE 'diplonow_%' AND NOT g.name LIKE 'SB_%';");

	// Cancel bot games that haven't been used for two days if they are not anonymous:
	$DB->sql_put("UPDATE wD_Games g INNER JOIN wD_Members m ON m.gameID = g.id INNER JOIN wD_Users u ON u.id = m.userID LEFT JOIN wD_Sessions s ON s.userID = u.id SET g.gameOver='Draw', g.phase= 'Finished' WHERE NOT u.type LIKE '%Bot%' AND g.gameOver = 'No' AND g.playerTypes = 'MemberVsBots' AND (u.timeLastSessionEnded < UNIX_TIMESTAMP() - 2*24*60*60 AND u.timeJoined < UNIX_TIMESTAMP() - 2*60*60 AND s.userID IS NULL) AND NOT u.username LIKE 'diplonow_%' AND NOT g.name LIKE 'SB_%';");

	// Update like counts for the forum every day:
	if( floor($Misc->LastStatsUpdate / (24*60*60)) < floor(time() / (24*60*60)) )
	{
		$DB->sql_put("UPDATE phpbb_users u
			SET webdip_like_count = 0
			UPDATE phpbb_users u
			INNER JOIN (
				SELECT p.poster_id, COUNT(*) AS likes
				FROM phpbb_posts p
				INNER JOIN phpbb_posts_likes l ON l.post_id = p.post_id
				GROUP BY p.poster_id
			) x ON x.poster_id = u.user_id
			SET u.webdip_like_count = x.likes;");
	}

	$Misc->LastStatsUpdate = time();
	// This is also only needed infrequently
	/*
	This should be unnecessary after changing the way the play-now games are created, but leaving it in for now just in case.
	if( Config::$playNowDomain != null )
	{
		// If there is a play-now domain set up ensure that games that have been left for over 24 hours don't linger and waste resources:
		// If a diplonow_ member hasn't logged onto a game for 24 hours set the member to vote for cancellation of the game.
		$DB->sql_put(
			"UPDATE wD_Members SET votes='Cancel' WHERE userID IN (SELECT id FROM wD_Users WHERE username LIKE 'diplonow%') AND timeLoggedIn < UNIX_TIMESTAMP()-24*60*60 AND status='Playing';"
		);
	}*/
}

//- Check last process time, pause processing/save current process time
if ( ( time() - $Misc->LastProcessTime ) > Config::$downtimeTriggerMinutes*60 )
{
	libHTML::notice(l_t('Games not processing'),libHTML::admincp('resetLastProcessTime',null,l_t('Continue processing now')));
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

// Set all games where all users have joined to process now:
$DB->sql_put("UPDATE wD_Games g
	INNER JOIN wD_VariantInfo v ON v.variantID = g.variantID
	INNER JOIN (
		SELECT gameID, COUNT(*) membersJoined
		FROM wD_Members m
		GROUP BY gameID
	) m ON m.gameID = g.id
	SET g.processTime = UNIX_TIMESTAMP()
	WHERE g.phase = 'Pre-game' AND m.membersJoined = v.countryCount");

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
	$MC->set('processing'.$gameRow['id'], time(), 60); // Set a hint that nothing should be saved/cached for this game as it's being processed

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

		if( $Game->phaseMinutes > 3*60 && $Game->playerTypes != 'MembersVsBots' )
		{
			// Take a backup of non-bot games with a phase length 3 hours to a table that can be written out without transactions
			processGame::backupGame($Game->id, false);
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

	// Wipe the whole cache; regenerating game maps used to be a big drain on performance but these days locking is more of a concern,
	// and disabling locking on map generation might mean that a user loads half the units up
	Game::wipeCache($Game->id);

	$MC->delete('processing'.$Game->id);
	
	print '<br />';
}

$dirtyApiKeys = array_unique($dirtyApiKeys);
foreach($dirtyApiKeys as $key)
	$MC->delete(str_replace(' ','_','api'.$key.'players/missing_orders'));


if( defined('RUNNINGFROMCLI') ) 
{
	$DB->sql_put("COMMIT");
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
