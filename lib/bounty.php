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

defined('IN_CODE') or die('This script can not be run by itself.');

/**
 * Bounty events: live games at fixed times with bonus points, so that players have a time to show up.
 *
 * - A template (wD_BountyTemplates) is a named game configuration with a bonus, the whole pot of every game made
 *   from it (bounty games are free to enter), and optional consolation points for players left out.
 * - An event (wD_BountyEvents) schedules a template at a UTC time, once or weekly.
 * - Each time an event comes round is an occurrence, with its own pool (wD_BountyPools). The gamemaster opens the
 *   pool POOL_OPEN_SECONDS before the start, copying the template's settings into it, and players enter or leave
 *   it (wD_BountyPoolMembers) until the start.
 * - At the start the gamemaster shuffles the pool and makes as many full games as it fills. Anyone left over is
 *   paid the consolation points.
 *
 * Opening and shuffling happen once per occurrence however many gamemaster runs overlap: a pool is opened by an
 * INSERT IGNORE on its unique (eventID, startTime) key, and shuffled inside a transaction which holds the pool row
 * FOR UPDATE and only goes ahead if the pool is still Open; a run claims a pool before that (see shufflePool()), so
 * that overlapping runs don't each try and count an attempt. Entering and leaving lock the same row, so they wait for
 * a shuffle in progress and then find the pool closed.
 *
 * @package Base
 */
class libBounty
{
	const POOL_OPEN_SECONDS = 300;
	const WEEK_SECONDS = 604800;
	// Shuffling a pool that fails this many times is given up on
	const MAX_ATTEMPTS = 3;
	// How long a run's claim on a pool keeps other runs from trying to shuffle it
	const CLAIM_SECONDS = 60;

	public static $potTypes = array('Winner-takes-all'=>'DSS (Equal split for draws)', 'Sum-of-squares'=>'SoS (Weighted split on draw)');
	public static $pressTypes = array('Regular'=>'All', 'PublicPressOnly'=>'Global only', 'NoPress'=>'None (No messaging)', 'RulebookPress'=>'Per rulebook');
	public static $drawTypes = array('draw-votes-public'=>'Show draw votes', 'draw-votes-hidden'=>'Hide draw votes');
	public static $missingPlayerPolicies = array('Normal'=>'Normal', 'Wait'=>'Wait for all orders');

	// The template columns which are copied into a pool's settings when it opens
	public static $settingNames = array('variantID', 'potType', 'phaseMinutes', 'phaseMinutesRB', 'nextPhaseMinutes',
		'phaseSwitchPeriod', 'anon', 'pressType', 'drawType', 'missingPlayerPolicy', 'minimumReliabilityRating',
		'excusedMissedTurns', 'bonus', 'consolationPoints');

	/**
	 * Check and normalize template settings from a form, the way gamecreate.php checks a new game's.
	 *
	 * @param array $form Unsanitized input
	 * @return array The template's column values, escaped for SQL
	 */
	public static function validateTemplate(array $form)
	{
		global $DB;

		$form = array_filter($form, 'is_scalar');
		$t = array();

		$t['name'] = isset($form['name']) ? $DB->escape(trim($form['name'])) : '';
		if ( $t['name'] == '' ) throw new Exception(l_t("No name entered."));
		if ( strlen($t['name']) > 50 ) throw new Exception(l_t("The name can't be over 50 characters."));

		$t['variantID'] = isset($form['variantID']) ? (int)$form['variantID'] : 0;
		if ( !isset(Config::$variants[$t['variantID']]) )
			throw new Exception(l_t("Variant ID given (%s) doesn't represent a real variant.", $t['variantID']));

		foreach( array('potType'=>self::$potTypes, 'pressType'=>self::$pressTypes, 'drawType'=>self::$drawTypes,
			'missingPlayerPolicy'=>self::$missingPlayerPolicies) as $name=>$allowed )
		{
			if ( !isset($form[$name]) || !isset($allowed[$form[$name]]) )
				throw new Exception(l_t('Invalid %s given.', $name));
			$t[$name] = $form[$name];
		}

		// As gamecreate.php forces it, to prevent cheating via out of game messaging
		$t['anon'] = ( $t['pressType'] == 'NoPress' || ( isset($form['anon']) && $form['anon'] == 'Yes' ) ) ? 'Yes' : 'No';

		$t['phaseMinutes'] = isset($form['phaseMinutes']) ? (int)$form['phaseMinutes'] : 0;
		if ( $t['phaseMinutes'] < 5 or $t['phaseMinutes'] > 1440*10 )
			throw new Exception(l_t("The phase value is too large or small; it must be between 5 minutes and 10 days."));

		$t['phaseMinutesRB'] = isset($form['phaseMinutesRB']) && $form['phaseMinutesRB'] !== '' ? (int)$form['phaseMinutesRB'] : -1;
		if ( $t['phaseMinutesRB'] != -1 && ( $t['phaseMinutesRB'] > $t['phaseMinutes'] || $t['phaseMinutesRB'] < $t['phaseMinutes'] / 10 ) )
			throw new Exception(l_t("The phase length for retreats and builds must be within 10% and 100% of the regular phase length, or -1 for the same."));

		$t['phaseSwitchPeriod'] = isset($form['phaseSwitchPeriod']) && $form['phaseSwitchPeriod'] !== '' ? (int)$form['phaseSwitchPeriod'] : -1;
		$t['nextPhaseMinutes'] = isset($form['nextPhaseMinutes']) && $form['nextPhaseMinutes'] !== '' ? (int)$form['nextPhaseMinutes'] : $t['phaseMinutes'];
		if ( $t['phaseMinutes'] > 60 || $t['phaseSwitchPeriod'] == -1 )
		{
			$t['nextPhaseMinutes'] = $t['phaseMinutes'];
			$t['phaseSwitchPeriod'] = -1;
		}
		else
		{
			if ( $t['nextPhaseMinutes'] < 1440 or $t['nextPhaseMinutes'] > 1440*10 )
				throw new Exception(l_t("The phase length after the switch must be between 1 and 10 days."));
			if ( $t['phaseSwitchPeriod'] > 360 or $t['phaseSwitchPeriod'] < $t['phaseMinutes'] )
				throw new Exception(l_t("The phase switch period must be between the phase length and 6 hours."));
		}

		$t['minimumReliabilityRating'] = isset($form['minimumReliabilityRating']) ? (int)$form['minimumReliabilityRating'] : 0;
		if ( $t['minimumReliabilityRating'] < 0 or $t['minimumReliabilityRating'] > 100 )
			throw new Exception(l_t("The reliability rating threshold must range from 0-100"));

		$t['excusedMissedTurns'] = isset($form['excusedMissedTurns']) ? (int)$form['excusedMissedTurns'] : 0;
		if ( $t['excusedMissedTurns'] < 0 || $t['excusedMissedTurns'] > 4 )
			throw new Exception(l_t("The excused missed turn number is too large or small; it must be between 0 and 4."));

		$t['bonus'] = isset($form['bonus']) ? (int)$form['bonus'] : 0;
		if ( $t['bonus'] < 1 || $t['bonus'] > 100000 )
			throw new Exception(l_t("The bonus must be between 1 and 100000 points; bounty games are free to enter, so it is the whole pot."));

		$t['consolationPoints'] = isset($form['consolationPoints']) ? (int)$form['consolationPoints'] : 0;
		if ( $t['consolationPoints'] < 0 || $t['consolationPoints'] > 10000 )
			throw new Exception(l_t("The consolation points must be between 0 (none) and 10000."));

		return $t;
	}

	/**
	 * Save a template, inserting it if $templateID is 0
	 *
	 * @return int The template's ID
	 */
	public static function saveTemplate($templateID, array $t)
	{
		global $DB;

		$set = array();
		foreach($t as $name=>$value)
			$set[] = "`".$name."` = '".$value."'";
		$set[] = "timeModified = ".time();

		if ( $templateID )
		{
			$DB->sql_put("UPDATE wD_BountyTemplates SET ".implode(', ', $set)." WHERE id = ".(int)$templateID);
			return (int)$templateID;
		}

		$DB->sql_put("INSERT INTO wD_BountyTemplates SET ".implode(', ', $set).", timeCreated = ".time());
		return (int)$DB->last_inserted();
	}

	public static function getTemplate($templateID)
	{
		global $DB;

		$row = $DB->sql_hash("SELECT * FROM wD_BountyTemplates WHERE id = ".(int)$templateID);
		if ( !$row ) throw new Exception(l_t("Template %s not found.", (int)$templateID));
		return $row;
	}

	/**
	 * Copy a template, as "Copy of <name>"
	 *
	 * @return int The new template's ID
	 */
	public static function copyTemplate($templateID)
	{
		global $DB;

		$row = self::getTemplate($templateID);

		$t = array('name' => $DB->escape(substr('Copy of '.$row['name'], 0, 50), true));
		foreach(self::$settingNames as $name)
			$t[$name] = $DB->escape($row[$name], true);

		return self::saveTemplate(0, $t);
	}

	/**
	 * Delete a template and its events. The pools it has run keep their own copy of its name and settings, but one
	 * which is open would lose its event, so it can't be deleted until the pool has been shuffled.
	 */
	public static function deleteTemplate($templateID)
	{
		global $DB;

		$templateID = (int)$templateID;
		self::getTemplate($templateID);

		list($openPools) = $DB->sql_row("SELECT COUNT(*) FROM wD_BountyPools WHERE templateID = ".$templateID." AND status = 'Open'");
		if ( $openPools > 0 )
			throw new Exception(l_t("This template has a pool open; it can be deleted once the pool has been shuffled into games."));

		$DB->sql_put("DELETE FROM wD_BountyEvents WHERE templateID = ".$templateID);
		$DB->sql_put("DELETE FROM wD_BountyTemplates WHERE id = ".$templateID);
	}

	/**
	 * Parse a list of UTC start times, one per line or comma separated, as YYYY-MM-DD HH:MM
	 *
	 * @return int[] Unix timestamps
	 */
	public static function parseStartTimes($text)
	{
		$times = array();
		foreach( preg_split('/[\r\n,]+/', (string)$text) as $line )
		{
			$line = trim($line);
			if ( $line == '' ) continue;

			$date = DateTimeImmutable::createFromFormat('!Y-m-d H:i', $line, new DateTimeZone('UTC'));
			$errors = DateTimeImmutable::getLastErrors();
			if ( !$date || ( $errors && ( $errors['warning_count'] || $errors['error_count'] ) ) )
				throw new Exception(l_t("Couldn't read the start time '%s'; use YYYY-MM-DD HH:MM, in UTC.", htmlentities($line, ENT_QUOTES, 'UTF-8')));

			$times[] = $date->getTimestamp();
		}

		if ( count($times) == 0 ) throw new Exception(l_t("No start times entered."));
		return array_unique($times);
	}

	/**
	 * Schedule a template at each of the given times
	 *
	 * @param int[] $startTimes
	 * @param string $repeats 'Once' or 'Weekly'
	 * @return int[] The new events' IDs
	 */
	public static function scheduleEvents($templateID, array $startTimes, $repeats, $enabled)
	{
		global $DB;

		$templateID = (int)$templateID;
		self::getTemplate($templateID);

		if ( $repeats != 'Once' && $repeats != 'Weekly' ) throw new Exception(l_t("Invalid repeat given."));

		$eventIDs = array();
		foreach($startTimes as $startTime)
		{
			// A weekly event's first time may be in the past, as only its weekday and time matter; a single one can't be
			if ( $repeats == 'Once' && $startTime <= time() )
				throw new Exception(l_t("%s UTC has already passed.", gmdate('Y-m-d H:i', $startTime)));

			$DB->sql_put("INSERT INTO wD_BountyEvents ( templateID, startTime, repeats, enabled, timeCreated )
				VALUES ( ".$templateID.", ".(int)$startTime.", '".$repeats."', ".($enabled ? 1 : 0).", ".time()." )");
			$eventIDs[] = (int)$DB->last_inserted();
		}
		return $eventIDs;
	}

	/**
	 * The start of an event's next occurrence which hasn't started yet, or null if it won't come round again
	 *
	 * @param array $event A wD_BountyEvents row
	 * @param int $now
	 * @return int|null
	 */
	public static function nextStartTime(array $event, $now)
	{
		$startTime = (int)$event['startTime'];

		if ( $startTime >= $now ) return $startTime;
		if ( $event['repeats'] != 'Weekly' ) return null;

		return $startTime + (int)ceil(($now - $startTime) / self::WEEK_SECONDS) * self::WEEK_SECONDS;
	}

	/**
	 * Run by the gamemaster on every cycle, before it looks for games to process so that the games made here start
	 * in the same run: open the pools of occurrences about to start, and shuffle the pools whose start has come.
	 */
	public static function processDue()
	{
		global $DB;

		$now = time();

		// Open pools. The unique key on (eventID, startTime) makes this happen once per occurrence; an occurrence which
		// is past its start without a pool (the gamemaster wasn't running in its last five minutes) is skipped.
		$tabl = $DB->sql_tabl("SELECT e.*, t.name FROM wD_BountyEvents e
			INNER JOIN wD_BountyTemplates t ON t.id = e.templateID
			WHERE e.enabled = 1");
		$toOpen = array();
		while( $event = $DB->tabl_hash($tabl) )
		{
			$startTime = self::nextStartTime($event, $now);
			if ( $startTime !== null && $startTime - self::POOL_OPEN_SECONDS <= $now )
				$toOpen[] = array($event, $startTime);
		}
		foreach($toOpen as $open)
		{
			list($event, $startTime) = $open;

			// Checked first because an ignored INSERT still uses up an auto-increment ID, and this runs every cycle
			list($opened) = $DB->sql_row("SELECT COUNT(*) FROM wD_BountyPools WHERE eventID = ".$event['id']." AND startTime = ".$startTime);
			if ( $opened ) continue;

			$template = self::getTemplate($event['templateID']);
			$settings = array();
			foreach(self::$settingNames as $name)
				$settings[$name] = $template[$name];

			$DB->sql_put("INSERT IGNORE INTO wD_BountyPools ( eventID, templateID, name, startTime, settings, timeOpened )
				VALUES ( ".$event['id'].", ".$event['templateID'].", '".$DB->escape($template['name'], true)."', ".$startTime.",
					'".$DB->escape(json_encode($settings), true)."', ".$now." )");
			if ( $DB->last_affected() > 0 )
				print l_t('Opened the bounty pool for %s at %s', $template['name'], gmdate('Y-m-d H:i', $startTime)).'<br />';
		}
		$DB->sql_put("COMMIT");

		$poolIDs = array();
		$tabl = $DB->sql_tabl("SELECT id FROM wD_BountyPools WHERE status = 'Open' AND startTime <= ".$now." ORDER BY startTime");
		while( list($poolID) = $DB->tabl_row($tabl) )
			$poolIDs[] = (int)$poolID;

		foreach($poolIDs as $poolID)
			self::shufflePool($poolID);
	}

	/**
	 * Shuffle a pool into games, once. Everything is done in one transaction holding the pool row FOR UPDATE, so if
	 * another gamemaster run is shuffling it this one waits and then finds it done, a player entering or leaving
	 * meanwhile waits and then finds it closed, and if anything fails it is all rolled back and tried again.
	 */
	public static function shufflePool($poolID)
	{
		global $DB, $Game;

		// Claim the pool, counting the attempt, and commit that before shuffling: a database error ends the whole
		// request rather than throwing, so a count made after a failure would never be written, and a pool which always
		// failed would stop the gamemaster every run. One run gets the claim and the rest skip the pool, so overlapping
		// runs don't each count an attempt; if the run which claimed it dies, another can claim it CLAIM_SECONDS later.
		$DB->sql_put("UPDATE wD_BountyPools SET attempts = attempts + 1, timeClaimed = ".time()."
			WHERE id = ".$poolID." AND status = 'Open' AND ( timeClaimed IS NULL OR timeClaimed < ".(time() - self::CLAIM_SECONDS)." )");
		$claimed = ( $DB->last_affected() > 0 );
		$DB->sql_put("COMMIT");
		if ( !$claimed ) return;

		$previousGame = isset($Game) ? $Game : null;
		$gameIDs = array();
		$placedUserIDs = array();
		$leftOverUserIDs = array();
		try
		{
			$DB->sql_put("BEGIN");
			$pool = $DB->sql_hash("SELECT * FROM wD_BountyPools WHERE id = ".$poolID." FOR UPDATE");
			if ( !$pool || $pool['status'] != 'Open' || $pool['startTime'] > time() )
			{
				$DB->sql_put("COMMIT");
				return;
			}
			// This attempt is included in the count
			if ( $pool['attempts'] > self::MAX_ATTEMPTS )
			{
				$DB->sql_put("UPDATE wD_BountyPools SET status = 'Failed', timeSharded = ".time()." WHERE id = ".$poolID);
				$DB->sql_put("COMMIT");
				// Logged rather than raised, as the error handler would end the gamemaster run before it processes games
				$failedMessage = l_t("Bounty pool %s failed to be shuffled into games %s times, and has been given up on.", $poolID, self::MAX_ATTEMPTS);
				error_log($failedMessage);
				print $failedMessage.'<br />';
				return;
			}

			$settings = json_decode($pool['settings'], true);

			// The eligibility checked on entry, checked again in case it has changed since
			$eligible = array();
			$tabl = $DB->sql_tabl("SELECT m.userID,
					( u.type LIKE '%Banned%' OR u.type LIKE '%Bot%' OR u.tempBan > ".time()."
						OR u.reliabilityRating < ".(int)$settings['minimumReliabilityRating']." ) AS ineligible
				FROM wD_BountyPoolMembers m
				INNER JOIN wD_Users u ON u.id = m.userID
				WHERE m.poolID = ".$poolID." AND m.outcome = 'Waiting'");
			while( list($userID, $ineligible) = $DB->tabl_row($tabl) )
			{
				if ( $ineligible )
					$DB->sql_put("UPDATE wD_BountyPoolMembers SET outcome = 'Ineligible' WHERE poolID = ".$poolID." AND userID = ".$userID);
				else
					$eligible[] = (int)$userID;
			}

			$Variant = libVariant::loadFromVariantID($settings['variantID']);
			$countryCount = count($Variant->countries);

			shuffle($eligible);
			$gameCount = (int)floor(count($eligible) / $countryCount);

			require_once(l_r('gamemaster/game.php'));
			for($g = 0; $g < $gameCount; $g++)
			{
				$Game = processGame::create(
					$settings['variantID'],
					$DB->escape(substr($pool['name'], 0, 40).' '.gmdate('M j', $pool['startTime']).($gameCount > 1 ? ' #'.($g+1) : ''), true),
					'',
					0, // Free to enter; the bonus below is the pot
					$settings['potType'],
					$settings['phaseMinutes'],
					$settings['phaseMinutesRB'],
					$settings['nextPhaseMinutes'],
					$settings['phaseSwitchPeriod'],
					5,
					$settings['anon'],
					$settings['pressType'],
					$settings['missingPlayerPolicy'],
					$settings['drawType'],
					$settings['minimumReliabilityRating'],
					$settings['excusedMissedTurns'],
					'Members');

				$countryIDs = range(1, $countryCount);
				shuffle($countryIDs);
				foreach( array_slice($eligible, $g*$countryCount, $countryCount) as $i=>$userID )
				{
					processMember::create($userID, 0, $countryIDs[$i]);
					$DB->sql_put("UPDATE wD_BountyPoolMembers SET outcome = 'Placed', gameID = ".$Game->id."
						WHERE poolID = ".$poolID." AND userID = ".$userID);
					$placedUserIDs[$userID] = $Game->id;
				}

				// Start straight away: the gamemaster run which called this processes it next
				$DB->sql_put("UPDATE wD_Games SET pot = pot + ".(int)$settings['bonus'].", processTime = ".time()." WHERE id = ".$Game->id);

				$Game->Members->send('No', l_t('This is a bounty game from %s: you were placed in it from the pool, and it is starting now. '.
					'The pot is a bonus of %s; good luck!', $pool['name'], $settings['bonus'].libHTML::points()));

				$gameIDs[] = $Game->id;
			}

			// Everyone else is removed from the pool, with the consolation points if the template has them
			$consolation = (int)$settings['consolationPoints'];
			foreach( array_slice($eligible, $gameCount*$countryCount) as $userID )
			{
				$DB->sql_put("UPDATE wD_BountyPoolMembers SET outcome = 'LeftOver', consolationPoints = ".$consolation."
					WHERE poolID = ".$poolID." AND userID = ".$userID);
				if ( $consolation > 0 )
					User::pointsTransfer($userID, 'Bounty', $consolation);

				notice::send($userID, 1, 'User', 'No', 'No',
					( $gameCount == 0
						? l_t('Not enough players entered the pool for %s to make a game.', $pool['name'])
						: l_t('The pool for %s was shuffled into %s game(s), and you were one of the players left over.', $pool['name'], $gameCount) ).
					( $consolation > 0 ? ' '.l_t('You have been given %s for entering.', $consolation.libHTML::points()) : '' ),
					'GameMaster');
				$leftOverUserIDs[] = $userID;
			}

			$DB->sql_put("UPDATE wD_BountyPools SET status = 'Done', timeSharded = ".time().", gamesCreated = ".$gameCount.",
					playersPlaced = ".count($placedUserIDs).", playersLeftOver = ".count($leftOverUserIDs)."
				WHERE id = ".$poolID);
			$DB->sql_put("COMMIT");
		}
		catch(Throwable $e)
		{
			$DB->sql_put("ROLLBACK");
			$Game = $previousGame;
			print l_t('Failed to shuffle bounty pool %s: "%s"', $poolID, $e->getMessage()).'<br />';
			return;
		}
		$Game = $previousGame;

		print l_t('Shuffled bounty pool %s into %s game(s); %s player(s) left over', $poolID, count($gameIDs), count($leftOverUserIDs)).'<br />';

		// Now that it has all committed: publish the new games, and push-notify the players
		foreach($gameIDs as $gameID)
			libGameFiles::refresh($gameID);

		foreach( array_count_values($placedUserIDs) as $gameID=>$count )
			libPush::queue(array_keys($placedUserIDs, $gameID), $pool['name'],
				l_t('Your bounty game is starting now'), '/board.php?gameID='.$gameID, 'game-'.$gameID);
		if ( count($leftOverUserIDs) > 0 )
			libPush::queue($leftOverUserIDs, $pool['name'],
				$consolation > 0 ? l_t('You were left over from the pool, and given %s points', $consolation) : l_t('You were left over from the pool'),
				'/index.php', 'bounty-'.$poolID);
	}

	/**
	 * Why the current user can't enter this pool now, or false if they can
	 *
	 * @param array $pool A wD_BountyPools row
	 * @return string|false
	 */
	public static function cantEnterReason(array $pool)
	{
		global $User, $DB;

		if ( !$User->type['User'] ) return l_t('You must be logged on to enter.');
		if ( $User->type['Bot'] || $User->type['Banned'] ) return l_t('This account can\'t enter.');
		if ( $pool['status'] != 'Open' || $pool['startTime'] <= time() ) return l_t('This pool has closed.');
		if ( $User->userIsTempBanned() ) return l_t('Due to a temporary ban you cannot join games.');

		$settings = json_decode($pool['settings'], true);
		if ( $User->reliabilityRating < $settings['minimumReliabilityRating'] )
			return l_t('You are not reliable enough to join this game; it needs a reliability rating of %s%%.', $settings['minimumReliabilityRating']);

		// A player can be in one open pool at a time, as two would start two live games at once
		list($otherPool) = $DB->sql_row("SELECT p.name FROM wD_BountyPoolMembers m
			INNER JOIN wD_BountyPools p ON p.id = m.poolID
			WHERE m.userID = ".$User->id." AND m.outcome = 'Waiting' AND p.status = 'Open' AND p.id <> ".(int)$pool['id']);
		if ( $otherPool ) return l_t('You are already in the pool for %s; leave it to enter this one.', $otherPool);

		return false;
	}

	/**
	 * Enter the current user into a pool, or take them out of it
	 */
	public static function enterPool($poolID, $enter = true)
	{
		global $User, $DB;

		$poolID = (int)$poolID;

		// The pool's row is locked so that a shuffle in progress finishes first, and then the user's so that entering two
		// pools at once can't get round the one pool limit. Pool then user is the order the shuffle locks them in too.
		$DB->sql_put("COMMIT");
		$pool = $DB->sql_hash("SELECT * FROM wD_BountyPools WHERE id = ".$poolID." FOR UPDATE");
		if ( !$pool ) throw new Exception(l_t('Bounty pool not found.'));
		$DB->sql_row("SELECT id FROM wD_Users WHERE id = ".$User->id." FOR UPDATE");

		list($inPool) = $DB->sql_row("SELECT COUNT(*) FROM wD_BountyPoolMembers WHERE poolID = ".$poolID." AND userID = ".$User->id." AND outcome = 'Waiting'");

		if ( $enter )
		{
			if ( $inPool ) return;
			$reason = self::cantEnterReason($pool);
			if ( $reason ) throw new Exception($reason);

			$DB->sql_put("INSERT INTO wD_BountyPoolMembers ( poolID, userID, timeEntered ) VALUES ( ".$poolID.", ".$User->id.", ".time()." )");
		}
		else
		{
			if ( !$inPool ) return;
			if ( $pool['status'] != 'Open' || $pool['startTime'] <= time() ) throw new Exception(l_t('This pool has closed.'));

			$DB->sql_put("DELETE FROM wD_BountyPoolMembers WHERE poolID = ".$poolID." AND userID = ".$User->id." AND outcome = 'Waiting'");
		}
		$DB->sql_put("COMMIT");
	}

	/**
	 * A text summary of a template's or pool's game settings
	 */
	public static function settingsText(array $s)
	{
		$Variant = libVariant::loadFromVariantID($s['variantID']);

		$buf = array();
		$buf[] = $Variant->fullName;
		$buf[] = l_t('%s /phase', libTime::timeLengthText($s['phaseMinutes']*60)).
			( $s['phaseMinutesRB'] != -1 ? ' '.l_t('(%s retreats and builds)', libTime::timeLengthText($s['phaseMinutesRB']*60)) : '' );
		if ( $s['phaseSwitchPeriod'] != -1 )
			$buf[] = l_t('then %s /phase after %s', libTime::timeLengthText($s['nextPhaseMinutes']*60), libTime::timeLengthText($s['phaseSwitchPeriod']*60));
		$buf[] = l_t(self::$potTypes[$s['potType']]);
		$buf[] = l_t('Chat: %s', l_t(self::$pressTypes[$s['pressType']]));
		if ( $s['anon'] == 'Yes' ) $buf[] = l_t('Anon');
		if ( $s['drawType'] == 'draw-votes-hidden' ) $buf[] = l_t('Hidden draw votes');
		if ( $s['missingPlayerPolicy'] == 'Wait' ) $buf[] = l_t('Waits for all orders');
		if ( $s['minimumReliabilityRating'] > 0 ) $buf[] = l_t('RR%s%%', $s['minimumReliabilityRating']);
		$buf[] = l_t('%s excused missed turn(s)', $s['excusedMissedTurns']);

		return implode(', ', $buf);
	}

	/**
	 * The upcoming occurrences: each open pool, and the next occurrence of each enabled event which doesn't have one
	 *
	 * @return array[] Each with startTime, name, settings, and the pool row if it is open
	 */
	public static function upcomingOccurrences()
	{
		global $DB;

		$now = time();
		$occurrences = array();

		$openEventStarts = array();
		$tabl = $DB->sql_tabl("SELECT * FROM wD_BountyPools WHERE status = 'Open' ORDER BY startTime");
		while( $pool = $DB->tabl_hash($tabl) )
		{
			$openEventStarts[$pool['eventID'].'-'.$pool['startTime']] = true;
			$occurrences[] = array('startTime'=>(int)$pool['startTime'], 'name'=>$pool['name'], 'eventID'=>(int)$pool['eventID'],
				'settings'=>json_decode($pool['settings'], true), 'pool'=>$pool);
		}

		$tabl = $DB->sql_tabl("SELECT e.*, t.* , e.id eventID FROM wD_BountyEvents e
			INNER JOIN wD_BountyTemplates t ON t.id = e.templateID
			WHERE e.enabled = 1");
		while( $row = $DB->tabl_hash($tabl) )
		{
			$startTime = self::nextStartTime($row, $now);
			if ( $startTime === null || isset($openEventStarts[$row['eventID'].'-'.$startTime]) ) continue;

			$settings = array();
			foreach(self::$settingNames as $name)
				$settings[$name] = $row[$name];
			$occurrences[] = array('startTime'=>$startTime, 'name'=>$row['name'], 'eventID'=>(int)$row['eventID'],
				'settings'=>$settings, 'pool'=>null);
		}

		usort($occurrences, function($a, $b) { return $a['startTime'] - $b['startTime']; });
		return $occurrences;
	}

	/**
	 * The home page panels for the upcoming occurrences, with the buttons to enter or leave an open pool
	 */
	public static function homePanels($limit = 5)
	{
		global $User, $DB;

		$buf = '';
		foreach( array_slice(self::upcomingOccurrences(), 0, $limit) as $o )
		{
			$s = $o['settings'];

			$buf .= '<div class="hr"></div>';
			$buf .= '<div class="gamePanelHome bountyEvent">';
			$buf .= '<div class="titleBarRightSide"><span class="gameTimeRemaining">'.libTime::remainingText($o['startTime']).'</span></div>';
			$buf .= '<div class="titleBarLeftSide"><div class="titleBarHomeGameName"><span class="gameName">'.$o['name'].'</span></div></div>';
			$buf .= '<div style="clear:both"></div>';
			$buf .= '<div class="bar homeGameLinks barAlt'.libHTML::alternate().'">';
			$buf .= l_t('Starts %s', libTime::detailedText($o['startTime'])).'<br />';
			$buf .= l_t('Bonus: <strong>%s</strong>, free to enter', $s['bonus'].libHTML::points());
			if ( $s['consolationPoints'] > 0 )
				$buf .= '; '.l_t('%s for players left out', $s['consolationPoints'].libHTML::points());
			$buf .= '<br /><span style="font-size:90%">'.self::settingsText($s).'</span><br />';

			if ( $o['pool'] )
			{
				$pool = $o['pool'];
				list($entered) = $DB->sql_row("SELECT COUNT(*) FROM wD_BountyPoolMembers WHERE poolID = ".$pool['id']." AND outcome = 'Waiting'");
				list($inPool) = $DB->sql_row("SELECT COUNT(*) FROM wD_BountyPoolMembers WHERE poolID = ".$pool['id']." AND userID = ".$User->id." AND outcome = 'Waiting'");
				$buf .= l_t('The pool is open: <strong>%s</strong> player(s) in it.', $entered).' ';

				if ( $inPool )
				{
					$buf .= l_t("You're in the pool.");
					$buf .= '<form method="post" action="index.php">'.libAuth::formTokenHTML().
						'<input type="hidden" name="bountyPoolID" value="'.$pool['id'].'" />'.
						'<input type="submit" class="form-submit" name="bountyLeave" value="'.l_t('Leave pool').'" /></form>';
				}
				elseif ( $reason = self::cantEnterReason($pool) )
				{
					$buf .= '<span style="font-size:80%">('.$reason.')</span>';
				}
				else
				{
					$buf .= '<form method="post" action="index.php">'.libAuth::formTokenHTML().
						'<input type="hidden" name="bountyPoolID" value="'.$pool['id'].'" />'.
						'<input type="submit" class="form-submit" name="bountyEnter" value="'.l_t('Enter pool').'" /></form>';
				}
			}
			else
			{
				$buf .= l_t('Players can join from %s.', libTime::detailedText($o['startTime'] - self::POOL_OPEN_SECONDS));
			}

			$buf .= '</div><div class="bar lastBar"> </div></div>';
		}
		return $buf;
	}
}
