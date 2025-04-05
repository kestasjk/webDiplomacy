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
 * @package Base
 * @subpackage Forms
 */

// Check whether we're in play-now mode:
define('IN_CODE',true);
require_once('config.php');
if( Config::isOnPlayNowDomain() ) define('PLAYNOW',true);

require_once('header.php');

global $User, $Misc, $DB;

if ( $Misc->Panic )
{
	libHTML::notice(l_t('Game creation disabled'), 
	l_t("Game creation has been temporarily disabled while we take care of an unexpected problem. Please try again later, sorry for the inconvenience."));
}

if( defined('PLAYNOW') )
{
	libAuth::configurePlayNowUser();
	$_REQUEST['newGame'] = array('variantID'=>1, 'name'=>$User->username, 'countryID'=>0);
}

/*
A class to get the number of active bot games, the bot game limit, the number of people in the queue
who can join, and tell if the current user is in the queue and/or can join.
*/
class BotGameQueue
{
	public static $botGamesAllowed = 10;
	public static $botGamesQueued;
	public static $usersNotifiedAndWaiting;
	public static $botGamesStarted;

	public static $openSpacesReadyToNotify;

	public static function loadQueueStats()
	{
		global $DB;

		// Find how many games are currently in the queue and how many are waiting:
		list(self::$botGamesQueued, self::$usersNotifiedAndWaiting, self::$botGamesStarted) = $DB->sql_row("SELECT 
				COUNT(*) queued,
				SUM(IF(notifiedTime IS NOT NULL,1,0))-SUM(IF(startedTime IS NOT NULL,1,0)) notifiedAndWaiting,
				SUM(IF(startedTime IS NOT NULL,1,0)) started
			FROM wD_BotGameQueue 
			WHERE finishedTime IS NULL");

		self::$openSpacesReadyToNotify = (self::$botGamesAllowed - self::$botGamesStarted) - self::$usersNotifiedAndWaiting;
	}

	public static function updateQueue()
	{
		global $DB;

		// Set finished/cancelled games to finished:
		$DB->sql_put("UPDATE wD_BotGameQueue q LEFT JOIN wD_Games g ON g.id = q.gameID SET q.finishedTime = UNIX_TIMESTAMP() WHERE q.startedTime IS NOT NULL AND q.gameID IS NOT NULL AND q.finishedTime IS NULL AND (g.id IS NULL OR g.gameOver <> 'No')");
		
		// Remove queued users who have been notified for over 24 hours and haven't started:
		$DB->sql_put("DELETE FROM wD_BotGameQueue WHERE notifiedTime IS NOT NULL AND startedTime IS NULL AND UNIX_TIMESTAMP()-notifiedTime > 24*60*60");
		
		self::loadQueueStats();

		// Notify queued users who haven't been notified:
		$tabl = $DB->sql_tabl("SELECT id, userID FROM wD_BotGameQueue WHERE notifiedTime IS NULL ORDER BY queuedTime LIMIT ".self::$openSpacesReadyToNotify);
		while (list($id, $userID) = $DB->tabl_row($tabl))
		{
			// Notify the user:
			// Mark the user as notified:
			$DB->sql_put("UPDATE wD_BotGameQueue SET notifiedTime = UNIX_TIMESTAMP() WHERE id = ".$id);
			self::$usersNotifiedAndWaiting++;
		}
	}

	public static function canUserJoinQueue()
	{
		global $DB, $User;

		// Check if the user is already in the queue for a non-finished game:
		list($inQueue) = $DB->sql_row("SELECT COUNT(*) FROM wD_BotGameQueue WHERE userID = ".$User->id." AND finishedTime IS NULL");

		return ($inQueue == 0); // If they're not in the queue they can join:
	}

	public static function addToQueue()
	{
		global $DB, $User;

		// If there is currently a free slot to send someone a notification they can start this user can start immidiately without 
		// being notified:
		$userCanStartNow = self::$openSpacesReadyToNotify > 0;

		$DB->sql_put("INSERT INTO wD_BotGameQueue (userID, queuedTime, notifiedTime) VALUES (".$User->id.", UNIX_TIMESTAMP(), " . ($userCanStartNow ? "UNIX_TIMESTAMP()" : "NULL") . ")");
	}

	public static function canUserCreateGame()
	{
		global $DB, $User;

		// Check if the user is in the queue for a non-started game they've been notified for:
		list($isNotified) = $DB->sql_row("SELECT COUNT(*) FROM wD_BotGameQueue WHERE userID = ".$User->id." AND finishedTime IS NULL AND startedTime IS NULL AND notifiedTime IS NOT NULL");

		return ($isNotified > 0);
	}

	public static function gameQueueTable($includeFinished = false)
	{
		global $DB, $User;

		if( isset($_REQUEST['joinQueue']) )
		{
			if( self::canUserJoinQueue() )
			{
				self::addToQueue();
			}
			else
			{
				libHTML::notice(l_t('Already in queue'), l_t('You are already in the bot game queue; please finish your current game or wait for your turn.'));
			}
		}

		$tabl = $DB->sql_tabl(
			"SELECT u.username, q.queuedTime, q.notifiedTime, q.startedTime, q.finishedTime, g.id, g.name, g.turn, stats.readyOrders, stats.messages
			FROM wD_BotGameQueue q
			LEFT JOIN wD_Games g ON g.id = q.gameID
			LEFT JOIN wD_Members m ON m.gameID = g.id AND m.userID = q.userID
			LEFT JOIN (
				SELECT gameID, SUM(IF(orderStatus LIKE '%Completed%' OR orderStatus LIKE '%None%',1,0)) readyOrders, SUM(gameMessagesSent) messages
				FROM wD_Members
				GROUP BY gameID
			) stats ON stats.gameID = g.id
			LEFT JOIN wD_Users u ON u.id = q.userID
			".($includeFinished ? "" : "WHERE q.finishedTime IS NULL ")."
			ORDER BY queuedTime DESC"
		);

		$buf = '<strong>Full-press bot game queue</strong> ('.self::$botGamesQueued.' queued, '.self::$usersNotifiedAndWaiting.' notified and waiting, '.self::$botGamesStarted.' started)<br />';
		if( self::$openSpacesReadyToNotify > 0 )
		{
			$buf .= '<em>There are currently '.self::$openSpacesReadyToNotify.' open slots. Click <a href="botgamecreate.php?joinQueue=1">here</a> to start a full-press game.</em><br />';
		}
		else if( self::canUserJoinQueue() )
		{
			$buf .= '<em>There are currently no open slots. Click <a href="botgamecreate.php?joinQueue=1">here</a> to join the queue.</em><br />';
		}

		$buf .= '<table class="botGameQueue">';
		$buf .= '<tr><th>Username</th><th>Queued</th><th>Notified</th><th>Started</th><th>Finished</th>';
		$buf .= '<th>Game</th><th>Turn</th><th>Ready Orders</th><th>Messages</th></tr>';
		while (list($username, $queuedTime, $notifiedTime, $startedTime, $finishedTime, $gameID, $gameName, $turn, $readyOrders, $messages) = $DB->tabl_row($tabl))
		{
			$buf .= '<tr>';
			$buf .= '<td>' . $username . '</td>';
			$buf .= '<td>' . libTime::text($queuedTime) . '</td>';
			$buf .= '<td>' . ( $notifiedTime == null ? "" : libTime::text($notifiedTime)) . '</td>';
			$buf .= '<td>' . ( $startedTime == null ? "" : libTime::text($startedTime)) . '</td>';
			$buf .= '<td>' . ( $finishedTime == null ? "" : libTime::text($finishedTime)) . '</td>';
			if ($gameID)
			{
				$buf .= '<td><a href="board.php?gameID='.$gameID.'">'.$gameName.'</a></td>';
				$buf .= '<td>'.$turn.'</td>';
				$buf .= '<td>'.$readyOrders.'</td>';
				$buf .= '<td>'.$messages.'</td>';
			}
			else
			{
				$buf .= '<td colspan="4">No game started</td>';
			}
			$buf .= '</tr>';
		}
		
		$buf .= '</table>';
		return $buf;
	}
}

if( !$User->type['User'] )
{
	libHTML::notice(l_t('Not logged on'),l_t("Only a logged on user can create games. Please <a href='logon.php' class='light'>log on</a> to create your own games."));
}

// Limit users to 3 bot games at a time unless they are a moderator,
// and 1 game at a time if playing anonymously.
$userBotGameCount = $User->getBotGameCount();
if ($userBotGameCount > 2 || (defined('PLAYNOW') && $userBotGameCount > 0))
{
    if (!$User->type['Moderator'])
    {
        libHTML::notice(l_t('3 bot games at a time.'),l_t('Sorry, only 3 bot games at a time, please finish one of your current ones to start another!'));
    }
}

// Limit the number of simultaneous play now / anonymous bot games to 60
if( defined('PLAYNOW') )
{
	list($nopressBotGameCount) = $DB->sql_row("SELECT COUNT(DISTINCT g.id) FROM wD_Games g INNER JOIN wD_Members m ON m.gameID = g.id INNER JOIN wD_Users u ON u.id = m.userID WHERE NOT u.type LIKE '%Bot%' AND g.gameOver = 'No' AND g.playerTypes = 'MemberVsBots' AND u.username LIKE 'diplonow_%' AND NOT g.name LIKE 'SB_%'");
	if( $nopressBotGameCount > 199 )
	{
		libHTML::notice(l_t('Anonymous bot game limit reached'), l_t('Anonymous bot game limit '.$nopressBotGameCount.'/100 reached: Apologies, the anonymous bot game limit has been reached. To conserve server resources we have to limit the number of anonymous games. Please try again later, or create an account on the community page.'));
	}
}
else
{
	list($nopressBotGameCount) = $DB->sql_row("SELECT COUNT(DISTINCT g.id) FROM wD_Games g INNER JOIN wD_Members m ON m.gameID = g.id INNER JOIN wD_Users u ON u.id = m.userID WHERE NOT u.type LIKE '%Bot%' AND g.gameOver = 'No' AND g.playerTypes = 'MemberVsBots' AND NOT u.username LIKE 'diplonow_%' AND NOT g.name LIKE 'SB_%'");
	if( $nopressBotGameCount > 599 )
	{
		libHTML::notice(l_t('No-press bot game limit reached'), l_t('No-press bot game limit '.$nopressBotGameCount.'/600 reached: Apologies, the no-press bot game limit has been reached. To conserve server resources we have to limit the number of anonymous games. Please try again later.'));
	}

	list($fullPressBotGames, $userFullPressBotGames) = $DB->sql_row(
		"SELECT COUNT(*) totalGames, SUM(IF(u.id = ".$User->id.",1,0)) userGames
		FROM wD_ApiKeys a 
		INNER JOIN wD_Members m ON m.userID = a.userID
		INNER JOIN wD_Games g ON g.id = m.gameID 
		INNER JOIN wD_Members hm ON hm.gameID = g.id 
		INNER JOIN wD_Users u ON u.id = hm.userID 
		LEFT JOIN wD_ApiKeys ha ON ha.userID = u.id 
		WHERE a.username = 'dipgpt3' AND g.phase <> 'Finished' AND ha.userID IS NULL");
}

libHTML::starthtml();

if( isset($_REQUEST['newGame']) and is_array($_REQUEST['newGame']) )
{
	try
	{
		$form = $_REQUEST['newGame']; // This makes $form look harmless when it is unsanitized; the parameters must all be sanitized

		$input = array();
		$required = array('variantID', 'name', 'countryID');

		if ( !isset($form['missingPlayerPolicy']) ) {$form['missingPlayerPolicy'] = 'Normal'; }
		
		foreach($required as $requiredName)
		{
			if ( isset($form[$requiredName]) ) { $input[$requiredName] = $form[$requiredName]; }
			else{ throw new Exception(l_t('The variable "%s" is needed to create a game, but was not entered.',$requiredName)); }
        }
        
		$input['fullPress'] = isset($form['fullPress']) ? (int)$form['fullPress'] : 0;

		unset($required, $form);

		$input['variantID']=(int)$input['variantID'];
		$input['countryID']=(int)$input['countryID'];
		if( !in_array($input['variantID'],Config::$apiConfig['variantIDs']) ) { throw new Exception(l_t("Variant ID given (%s) doesn't represent a real variant.",$input['variantID'])); }

		$input['fullPress'] = ( $input['variantID'] == 1 && $input['fullPress'] == 1 ) ? 1 : 0;

		if( $input['fullPress'] == 1 )
		{
			if( defined('PLAYNOW') )
			{
				throw new Exception(l_t('Full-press games are not available in play-now mode.'));
			}
			if( $fullPressBotGames >= 10 && !isset($_REQUEST['enableBotOption']) )
			{
				throw new Exception(l_t('Full-press game limit reached, please try again later.'));
			}
			if( !is_null($userFullPressBotGames) && $userFullPressBotGames > 0 )
			{
				throw new Exception(l_t('One full-press game at a time, please complete / cancel your current full-press game before starting another.'));
			}
		}
		// If the name isn't unique or is too long the database will stop it
		$input['name'] = $DB->escape($input['name']);
		if ( !$input['name'] ) { throw new Exception(l_t("No name entered.")); }

		list($countryCount) = $DB->sql_row("SELECT countryCount FROM wD_VariantInfo WHERE variantID=".$input['variantID']);
		
		if ($input['countryID'] < 0 or $input['countryID'] > $countryCount)
		{
			throw new Exception(l_t("%s is an invalid country ID.",(string)$input['countryID']));
		}

		// Prevent temp banned players from making new games.
		if ($User->userIsTempBanned())
		{
			libHTML::notice('Temporary block', 'You are blocked from creating new games. Please visit the <a href="modforum.php">mod forum</a> to speak to a moderator.');
		}

		// Create Game record & object
		require_once(l_r('gamemaster/game.php'));
		$phaseMinutes = defined('PLAYNOW') ? 24*60 : 3*24*60;
		$Game = processGame::create($input['variantID'],$input['name'],'',5,'Unranked', $phaseMinutes, -1, $phaseMinutes, -1, 60,'No','Regular','Normal','draw-votes-public',0,4,'MemberVsBots');

		// Create first Member record & object
		processMember::create($User->id, 5, $input['countryID']);

		//Add Bots
		$botNum = $countryCount - 1;
		// $tabl = $DB->sql_tabl("SELECT id FROM wD_Users WHERE type LIKE '%bot%' LIMIT ".$botNum);
		// Use a specific bot for a specific variant for now. A new Config:: function is needed to be able to flexibly map variants to certain specialized bots
		$tabl = $DB->sql_tabl("SELECT id FROM wD_Users WHERE type LIKE '%bot%' ".
			($input['variantID']==15 ? " AND username='FairBot' ":
				($input['fullPress']==1 ? " AND username LIKE 'dipgpt%' ": " AND NOT username LIKE 'dipgpt%' ")
			)
			." LIMIT ".$botNum);
        
		$currCountry = 1;

		while (list($botID) = $DB->tabl_row($tabl))
		{
			if($currCountry == $input['countryID'])
			{
				$currCountry += 1;
			}
			if ($input['countryID'] == 0)
			{
				processMember::create($botID, 5, 0);
			}
			else
			{
				processMember::create($botID, 5, $currCountry);
			}
			$currCountry += 1;
		}
		// Get the game started straight away
		$DB->sql_put('UPDATE wD_Games SET processTime = ' . time() . ' WHERE id = ' . $Game->id);
		$MC->append('processHint',','.$Game->id);
		$Game->Members->joinedRedirect();
	}
	catch(Exception $e)
	{
		print '<div class="content">';
		print '<p class="notice">'.$e->getMessage().'</p>';
		print '</div>';
	}
}

if( defined('PLAYNOW') )
{
	libHTML::notice('Access denied', "Cannot customize bot-games from an anonymous account; please create an account to create a customized bot game.");
}

if ( $User->points >= 5 ) { $defaultPoints = 5; }
else
{
	print l_t("You cannot create a new game because you have less than 5%s, you only have %s%s. ".
		"You will always have at least 100 points, including the points that you have bet into active games, so if you want ".
		"to start a new game just wait until your other games have finished (<a href='points.php#minpoints' class='light'>read more</a>).",libHTML::points(),$User->points,libHTML::points());

	print '</div>';
	libHTML::footer();
}

if( isset($input) && isset($input['points']) ) { $formPoints = $input['points']; }
else { $formPoints = $defaultPoints; }

print '<div class="content-bare content-board-header content-title-header">
	<div class="pageTitle barAlt1">Create a new game against bots</div>
	<div class="pageDescription">Start a new game of Diplomacy against bots.</div>
</div>
<div class="content content-follow-on">
	<p><a href="gamecreate.php">Play a game against humans</a></p>
	<div class = "gameCreateShow">
	<p>All games against bots are unranked, with 3 day phases and 4 excused missed turns. However, anytime you ready up your orders, the game will immediately move to the next phase.</p>
		<form method="post">
			<p>
				<strong>Game Name:</strong></br>
				<input class = "gameCreate" type="text" name="newGame[name]" value="" size="30">
			</p>
			
			<strong>Variant type (map choices):</strong>
			<select id="variantID" class = "gameCreate" name="newGame[variantID]" onChange="setExtOptions(this.value)">';
			
?>			
<script type="text/javascript">
	function setExtOptions(i){
		document.getElementById('countryID').options.length=0;
		switch(i)
		{
			<?php
			$checkboxes=array();
			$first='';
			foreach(Config::$variants as $variantID=>$variantName)
			{
				if (in_array($variantID, Config::$apiConfig['variantIDs']))
				{
					$Variant = libVariant::loadFromVariantName($variantName);
					$checkboxes[$Variant->fullName] = '<option value="'.$variantID.'"'.(($first=='')?' selected':'').'>'.$Variant->fullName.'</option>';
					if($first=='')
					{
						$first='"'.$variantID.'"';
						$defaultName=$Variant->fullName;
					}
					print "case \"".$variantID."\":\n";		
					print "document.getElementById('countryID').options[0]=new Option ('Random','0');";
					for ($i=1; $i<=count($Variant->countries); $i++)
						print "document.getElementById('countryID').options[".$i."]=new Option ('".$Variant->countries[($i -1)]."', '".$i."');";
					print "break;\n";		
				}	
				ksort($checkboxes);	
			}
			?>	
		}
	}
</script>
<?php		
			print implode($checkboxes);
			print '</select>';
			print '</br></br>

			<strong>Country: </strong>
			<select id="countryID" class="gameCreate" name="newGame[countryID]">
			</select>
			</br></br>

			';
			if( $User->id == 10 )
			{
				BotGameQueue::loadQueueStats();
				BotGameQueue::updateQueue();
				print BotGameQueue::gameQueueTable();
			}
			print '
			<strong>Full-press setting: (Classic only): '.(9-$fullPressBotGames).'/10 game slots available</strong><br/>
			'.($fullPressBotGames >= 10 && !isset($_REQUEST['enableBotOption']) ? '<hidden id="fullPress" name="newGame[fullPress]" value="0"> <em>Please try again later when a full-press slot becomes available.</em>':'
			<em>This is currently a beta feature; full-press bots will take longer to respond than gunboat/no-press bots, 
			and their behavior / performance is still being determined / improved.</em>
			<select id="fullPress" class="gameCreate" name="newGame[fullPress]">
				<option value="0" selected>No - Gunboat / No-press</option>
				<option value="1">Yes - Full-press</option>
			</select>'.(isset($_REQUEST['enableBotOption']) ? '
   			<input type="hidden" name="enableBotOption" value="1" />':'').'
			').'</br></br>

			<p class="notice">
				<input class = "green-Submit" type="submit"  value="Create">
			</p>';
			?>
<script type="text/javascript">
	setExtOptions(<?php print $first;?>);
</script>
			<?php
			print '</form>
	</div>';

print '</div>';
libHTML::footer();
?>
