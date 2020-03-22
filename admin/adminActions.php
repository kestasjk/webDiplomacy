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
 * This class gives the static information for moderator tasks, and the code
 * which runs each task. The adminActionsForms class can use the static data
 * to present forms for each task, and correctly pass the submitted form data
 * to the right task code here.
 *
 * @package Admin
 */
class adminActions extends adminActionsForms
{
	public static $actions = array(
			'drawGame' => array(
				'name' => 'Draw game',
				'description' => 'Splits points among all the surviving players in a game according to its scoring system, and ends the game.',
				'params' => array('gameID'=>'Game ID'),
			),
			'cancelGame' => array(
				'name' => 'Cancel game',
				'description' => 'Refunds points each player has bet unless the game is finished. Finished games need manual point adjustments. Then deletes the game. Does not work on games that have not started, instead force all users into CD.',
				'params' => array('gameID'=>'Game ID'),
			),
			'togglePause' => array(
				'name' => 'Toggle-pause game',
				'description' => 'Flips a game\'s paused status; if it\'s paused it\'s unpaused, otherwise it\'s paused.<br />
					If you are using this tool on a phone navigate away from this page after using or an auto refresh will cause accidental toggles.',
				'params' => array('gameID'=>'Game ID'),
			),
			'makePublic' => array(
				'name' => 'Make a private game public',
				'description' => 'Removes a private game\'s password. This allows anyone to join.',
				'params' => array('gameID'=>'Game ID'),
			),
			'makePrivate' => array(
				'name' => 'Make a public game private',
				'description' => 'Add a password to a private game. Only people with this password can join.',
				'params' => array('gameID'=>'Game ID','password'=>'Password'),
			),
			'cdUser' => array(
				'name' => 'Force a user into CD',
				'description' => 'Force a user into CD in all their games, or in one game if non-zero gameID given.<br />
					Forced CDs do not count against the player\'s RR.<br />
					If the game has not started yet the user will be removed from the game entirely, and if they were the only user in the game, the game will be cancelled.',
				'params' => array('userID'=>'User ID','gameID'=>'Game ID'),
			),
			'replaceCoutries' => array(
				'name' => 'Replace country-player.',
				'description' => 'Replace one player in a given game with another one. This does not impact points. If the replacing player does not meet the RR requirements for the game or is already in the game, that game replacement will not occur.',
				'params' => array('userID'=>'UserID to be replaced','replaceID'=>'UserID replacing','gameIDs'=>'GameID (all active if empty)', )
			),
			'tempBan' => array(
				'name' => 'Temporary ban a player',
				'description' => 'Stops a player from joining or creating new games for that many days. To remove a temp ban, enter 0 days. Include a reason for the temp
				ban. <strong>The user will see the reason provided</strong>',
				'params' => array('userID'=>'User ID', 'ban'=>'Days','reason'=>'Reason')
			),
			'recalculateUserRR' => array(
				'name' => 'Recalculate RR for a User',
				'description' => 'Reruns the RR calculation for the user provided.',
				'params' => array('userID'=>'User ID')
			),
			'modExcuseDelay' => array(
				'name' => 'Mod Excuse Missed Turn',
				'description' => 'Enter the user to excuse and the ID of the missed turn to excuse (found on RR breakdown page)',
				'params' => array('userID'=>'User ID', 'excuseID'=>'Excuse ID','reason'=>'Reason')
			),
			'banUser' => array(
				'name' => 'Ban a user',
				'description' => 'Bans a user, setting his games to civil disorder, and removing his points.',
				'params' => array('userID'=>'User ID','reason'=>'Reason'),
			),
			'unbanUser' => array(
				'name' => 'Unban a user',
				'description' => 'Unbans a user; does not return the player from civil disorder, remove the ban comment from their profile, or return the points taken.',
				'params' => array('userID'=>'Banned User ID'),
			),
			'givePoints' => array(
				'name' => 'Give or take points',
				'description' => 'Enter a positive number of points to give, or a negative number of points to take.',
				'params' => array('userID'=>'User ID', 'points'=>'Points')
			),
			'resetPass' => array(
				'name' => 'Reset password',
				'description' => 'Resets a users password',
				'params' => array('userID'=>'User ID'),
			),
			'updateEmergencyDate' => array(
				'name' => 'Adjust Emergency Date',
				'description' => 'Enter 0 to grant the user another emergency pause, enter 1 to stop this user from having emergency pauses.',
				'params' => array('userID'=>'User ID','setting'=>'Setting'),
			),
			'setProcessTimeToPhase' => array(
				'name' => 'Reset process time',
				'description' => 'Set a game process time to now + the phase length, resetting the turn length',
				'params' => array('gameID'=>'Game ID'),
			),
			'setProcessTimeToNow' => array(
				'name' => 'Process game now',
				'description' => 'Set a game process time to now, resulting in it being processed now.<br />
					<em>Be careful:</em> this will cause any players without submitted moves to NMR.',
				'params' => array('gameID'=>'Game ID'),
			),
			'toggleWaitForOrders' => array(
				'name' => 'Toggle Wait for orders mode',
				'description' => 'Will toggle this game between normal NMR rules and wait-for-orders mode.<br />
					<em>Be careful:</em> flipping from on to off while the game is in wait mode will cause any players without submitted moves to NMR.',
				'params' => array('gameID'=>'Game ID'),
			),
			'resetMinimumBet' => array(
				'name' => 'Reset the minimum bet',
				'description' => 'If there is no join button on a game and the minimum bet hasn\'t been set correctly you can use this to reset it.',
				'params' => array('gameID'=>'Game ID'),
			),
			'panic' => array(
				'name' => 'Toggle panic button',
				'description' => 'Toggle the panic button; turning it on prevents games from being processed, users joining games,
						users registering. It is intended to limit the damage a problem can do.
						<em>This effectively shuts down the site, so be sure before pressing this.</em>',
				'params' => array(),
			),
			'changePhaseLength' => array(
				'name' => 'Change phase length',
				'description' => 'Change the maximum number of minutes that a phase lasts.
					The time must be given in minutes (5 minutes to 10 days = 5-14400).<br />
					Also the next process time is reset to the new phase length.',
				'params' => array('gameID'=>'Game ID','phaseMinutes'=>'Minutes per phase'),
			),
			'countryReallocate' => array(
				'name' => 'Reallocate countries',
				'description' => 'Alter which player has which country. Enter a list like so:
					"<em>R,T,A,G,I,F,E</em>".<br />
					The result will be that England will be set to Russia, France to Turkey, etc.<br /><br />
					If you aren\'t sure about the order of each country just enter the gameID without anything else and the list of
					countries in the order will be output.<br /><br />
					To prevent people sharing invalid info before the countries have been reallocated only no-message games
					can have their countries reallocated; messages should be enabled only after the countries have been reallocated.<br /><br />
					(The substitution string to reverse the reallocation will be generated, in case you need to reverse the reallocation.)<br />
					(If changing the countries of a variant for which the first letter of the countries are not distinct countryID numbers must be used instead.)<br />
					(Alternatively you can enter [userID1]=[countryLetter1],[userID2]=[countryLetter2],etc)',
				'params' => array(
					'gameID'=>'Game ID',
					'reallocations'=>'Reallocations list (e.g "<em>R,T,A,G,I,F,E</em>")'
					)
			),
	        'drawType' => array(
				'name' => 'Change the draw visibility',
				'description' => 'Change a game\'s draw visibility (public or hidden).',
				'params' => array(
					'gameID'=>'Game ID',
					'newSetting'=>'Enter a number for the desired setting: 1=Public, 2=Hidden'
				),
			),
			'alterMessaging' => array(
				'name' => 'Alter game messaging',
				'description' => 'Change a game\'s messaging settings, e.g. to convert from gunboat to public-only or all messages allowed.',
				'params' => array(
					'gameID'=>'Game ID',
					'newSetting'=>'Enter a number for the desired setting: 1=Regular, 2=PublicPressOnly, 3=NoPress, 4=RuleBookPress'
					),
			),
			'setDirector' => array(
				'name' => 'Set a user as a game director',
				'description' => 'Sets the given user ID to be the director of the given game ID (set to 0 to remove someone as game director).
					This will give them mod capabilities for this game.',
				'params' => array('gameID'=>'Game ID','userID'=>'User ID'),
			),
			'excusedMissedTurnsIncreaseAll' => array(
				'name' => 'Excused Missed Turns - Add for All',
				'description' => 'Adds 1 excused missed turn to all members in the game.',
				'params' => array('gameID'=>'Game ID'),
			),
			'excusedMissedTurnsDecreaseAll' => array(
				'name' => 'Excused Missed Turns - Remove for All',
				'description' => 'Removes 1 excused missed turn for all members in the game. If the user(s) do not have excused turns left nothing will happen.',
				'params' => array('gameID'=>'Game ID'),
			),
			'excusedMissedTurnsIncrease' => array(
				'name' => 'Excused Missed Turns - Add',
				'description' => 'Adds 1 excused missed turn to a specific user in a game.',
				'params' => array('gameID'=>'Game ID','userID'=>'User ID'),
			),
			'excusedMissedTurnsDecrease' => array(
				'name' => 'Excused Missed Turns - Remove',
				'description' => 'Removes 1 excused missed turn for a specific user in a game. If the user(s) do not have excused turns left nothing will happen.',
				'params' => array('gameID'=>'Game ID','userID'=>'User ID'),
			)
		);

	public function __construct()
	{
		global $Misc;
	}
	
	public function resetMinimumBet(array $params)
	{
		require_once(l_r('gamemaster/game.php'));
		$Variant=libVariant::loadFromGameID($params['gameID']);
		$Game = $Variant->processGame($params['gameID']);
		$Game->resetMinimumBet();
		return l_t("The minimum bet has been reset.");
	}

	public function countryReallocate(array $params)
	{
		global $DB;

		$gameID=(int)$params['gameID'];

		$Variant=libVariant::loadFromGameID($gameID);
		$Game = $Variant->Game($gameID);

		if( strlen($params['reallocations'])==0 )
		{
			$c=array();
			foreach($Variant->countries as $index=>$country)
			{
				$index++;
				$countryLetter=strtoupper(substr($country,0,1));
				$c[$countryLetter] = '#'.$index.": ".$country;
			}
			$ids=array_keys($c);

			return implode('<br />',$c)."<br />".l_t("e.g. \"%s\"\" would change nothing",implode(',',$ids));
		}

		$reallocations=explode(',',$params['reallocations']);

		if ( $Game->pressType != 'NoPress' )
			throw new Exception(l_t("Only games with no messages allowed can have their countries reordered, ".
				"otherwise information may already have been communicated while believing countries were already allocated."));

		if ( $Game->phase == 'Pre-game' )
			throw new Exception(l_t("This game hasn't yet started; countries can only be reallocated after they have been allocated already."));

		if ( $Game->phase == 'Finished' )
			throw new Exception(l_t("This game has finished, countries can't be reallocated."));

		if( count($reallocations) != count($Variant->countries) )
			throw new Exception(l_t("The number of inputted reallocations (%s) aren't equal to the number of countries (%s).",count($reallocations),count($Variant->countries)));

		if( !is_numeric(implode('', $reallocations)) )
		{
			$countryIDsByLetter=array();
			foreach($Variant->countries as $countryID=>$countryName)
			{
				$countryID++;
				$countryLetter=strtoupper(substr($countryName,0,1));
				if( isset($countryIDsByLetter[$countryLetter]) )
					throw new Exception(l_t("For the given variant two countries have the same start letter: '%s (one is '%s'), you must give countryIDs instead of letters.",$countryLetter,$countryName));

				$countryIDsByLetter[$countryLetter]=$countryID;
			}

			if( count(explode('=',$reallocations[0]))==2 )
			{
				$newCountryIDsByOldCountryID=array();
				foreach($reallocations as $r)
				{
					list($userID,$countryLetter)=explode('=', $r);
					$countryID=$countryIDsByLetter[$countryLetter];

					$oldCountryID=false;
					list($oldCountryID)=$DB->sql_row("SELECT countryID FROM wD_Members WHERE userID=".$userID." AND gameID = ".$Game->id);
					if( !$oldCountryID )
						throw new Exception(l_t("User %s not found in this game.",$userID));

					$newCountryIDsByOldCountryID[$oldCountryID]=$countryID;
				}
			}
			else
			{
				$newCountryIDsByOldCountryID=array();
				for($oldCountryID=1; $oldCountryID<=count($reallocations); $oldCountryID++)
				{
					$countryLetter=$reallocations[$oldCountryID-1];

					if( !isset($countryIDsByLetter[$countryLetter]) )
						throw new Exception(l_t("No country name starts with letter '%s'",$countryLetter));

					$newCountryIDsByOldCountryID[$oldCountryID]=$countryIDsByLetter[$countryLetter];
				}
			}
		}
		else
		{
			$newCountryIDsByOldCountryID=array();
			for($oldCountryID=1; $oldCountryID<=count($reallocations); $oldCountryID++)
			{
				$newCountryID=$reallocations[$oldCountryID-1];
				$newCountryIDsByOldCountryID[$oldCountryID]=(int)$newCountryID;
			}
		}

		$changes=array();
		$newUserIDByNewCountryID=array();
		$changeBack=array();

		foreach($newCountryIDsByOldCountryID as $oldCountryID=>$newCountryID)
		{
			list($userID)=$DB->sql_row("SELECT userID FROM wD_Members WHERE gameID=".$Game->id." AND countryID=".$oldCountryID." FOR UPDATE");
			$newUserIDByNewCountryID[$newCountryID]=$userID;

			$changes[] = l_t("Changed %s (#%s) to %s (#%s).",$Variant->countries[$oldCountryID-1],$oldCountryID,$Variant->countries[$newCountryID-1],$newCountryID);
			$changeBack[$newCountryID]=$oldCountryID;
		}

		$changeBackStr=array();

		for($i=1; $i<=count($Variant->countries); $i++)
			$changeBackStr[] = $changeBack[$i];

		$changeBackStr=implode(',', $changeBackStr);

		// Foreach member set the new owners' userID
		// The member isn't given a new countryID, instead the user in control of the countryID is moved into the other countryID:
		// userID is what gets changed, not countryID (if it's not done this way all sorts of problems e.g. supplyCenterNo crop up)
		$DB->sql_put("BEGIN");

		foreach($newUserIDByNewCountryID as $newCountryID=>$userID)
			$DB->sql_put("UPDATE wD_Members SET userID=".$userID." WHERE gameID=".$Game->id." AND countryID=".$newCountryID);

		$DB->sql_put("COMMIT");

		return l_t('In this game these countries were successfully swapped:').'<br />'.implode(',<br />', $changes).'.<br />
			'.l_t('These changes can be reversed with "%s"',$changeBackStr);
	}

	public function drawType(array $params)
	{
		global $DB;

		$gameID=(int)$params['gameID'];
		$newSetting=(int)$params['newSetting'];

		switch($newSetting)
		{
			case 1: $newSettingName='draw-votes-public'; break;
			case 2: $newSettingName='draw-votes-hidden'; break;
			default: throw new Exception(l_t("Invalid draw vote setting - enter 1 (public) or 2 (hidden)"));
		}

		$DB->sql_put("UPDATE wD_Games SET drawType = '".$newSettingName."' WHERE id = ".$gameID);

		return l_t('Game changed to drawType=%s.',$newSettingName);
	}

	public function alterMessaging(array $params)
	{
		global $DB;

		$gameID=(int)$params['gameID'];
		$newSetting=(int)$params['newSetting'];

		switch($newSetting)
		{
			case 1: $newSettingName='Regular'; break;
			case 2: $newSettingName='PublicPressOnly'; break;
			case 3: $newSettingName='NoPress'; break;
			case 4: $newSettingName='RuleBookPress'; break;
			default: throw new Exception(l_t("Invalid messaging setting; enter 1, 2, 3, or 4."));
		}

		$DB->sql_put("UPDATE wD_Games SET pressType = '".$newSettingName."' WHERE id = ".$gameID);

		return l_t('Game changed to pressType=%s.',$newSettingName);
	}

	public function changePhaseLength(array $params)
	{
		global $DB;

		require_once(l_r('objects/game.php'));

		$newPhaseMinutes = (int)$params['phaseMinutes'];

		if ( $newPhaseMinutes < 5 || $newPhaseMinutes > 10*24*60 )
			throw new Exception(l_t("Given phase minutes out of bounds (5 minutes to 10 days)"));

		$Variant=libVariant::loadFromGameID($params['gameID']);
		$Game = $Variant->Game($params['gameID']);

		if( $Game->processStatus != 'Not-processing' || $Game->phase == 'Finished' )
			throw new Exception(l_t("Game is either crashed/paused/finished/processing, and so the next-process time cannot be altered."));

		$oldPhaseMinutes = $Game->phaseMinutes;

		$Game->phaseMinutes = $newPhaseMinutes;
		$Game->processTime = time()+($newPhaseMinutes*60);

		$DB->sql_put("UPDATE wD_Games SET phaseMinutes = ".$Game->phaseMinutes.", processTime = ".$Game->processTime." WHERE id = ".$Game->id);

		return l_t('Process time changed from %s to %s. Next process time is %s.',
			libTime::timeLengthText($oldPhaseMinutes*60),libTime::timeLengthText($Game->phaseMinutes*60),libTime::text($Game->processTime));
	}

	public function toggleWaitForOrders(array $params)
	{
		global $DB;

		require_once(l_r('objects/game.php'));

		$Variant=libVariant::loadFromGameID($params['gameID']);
		$Game = $Variant->Game($params['gameID']);

		if( $Game->missingPlayerPolicy == 'Wait' )
		{
			$msg = "Set game to normal mode.";
			$setting = 'Normal';
		}
		else
		{
			$msg = "Set game to wait-for-orders mode.";
			$setting = 'Wait';
		}
		$DB->sql_put("UPDATE wD_Games SET missingPlayerPolicy = '".$setting."' WHERE id = ".$Game->id);
		return l_t($msg);
	}

	public function setProcessTimeToPhaseConfirm(array $params)
	{
		global $DB;

		require_once(l_r('objects/game.php'));

		$gameID = intval($params['gameID']);

		$Variant=libVariant::loadFromGameID($gameID);
		$Game = $Variant->Game($gameID);

		return l_t('Are you sure you want to reset the phase process time of this game to process in %s hours?',($Game->phaseMinutes/60));
	}

	public function setProcessTimeToPhase(array $params)
	{
		global $DB;

		$gameID = intval($params['gameID']);

		$Variant=libVariant::loadFromGameID($gameID);
		$Game = $Variant->Game($gameID);

		if( $Game->processStatus != 'Not-processing' || $Game->phase == 'Finished' )
			return l_t('This game is paused/crashed/finished.');

		$DB->sql_put("UPDATE wD_Games SET processTime = ".time()." + phaseMinutes * 60 WHERE id = ".$Game->id );

		return l_t('Process time reset successfully');
	}

	public function setProcessTimeToNowConfirm(array $params)
	{
		global $DB;

		require_once(l_r('objects/game.php'));

		$Variant=libVariant::loadFromGameID($params['gameID']);
		$Game = $Variant->Game($params['gameID']);

		return l_t('Are you sure you want to start processing this game now?');
	}

	public function makePublic(array $params)
	{
		global $DB;

		$gameID = intval($params['gameID']);

		$DB->sql_put("UPDATE wD_Games SET password = NULL WHERE id = ".$gameID );

		return l_t('Password removed');
	}

	public function makePrivate(array $params)
	{
		global $DB;

		$gameID = intval($params['gameID']);
		$password=$params['password'];

		$DB->sql_put( "UPDATE wD_Games SET password = UNHEX('".md5($password)."') WHERE id = ".$gameID);

		return l_t('Password set to "%s"',$password);
	}

	public function setProcessTimeToNow(array $params)
	{
		global $DB;

		$gameID = intval($params['gameID']);

		$Variant=libVariant::loadFromGameID($gameID);
		$Game = $Variant->Game($gameID);

		if( $Game->processStatus != 'Not-processing' || $Game->phase == 'Finished' )
			return l_t('This game is paused/crashed/finished.');

		$DB->sql_put("UPDATE wD_Games SET processTime = ".time()." WHERE id = ".$Game->id);

		return 'Process time set to now successfully';
	}

	public function panic(array $params)
	{
		global $Misc;

		$Misc->Panic = 1-$Misc->Panic;
		$Misc->write();

		return l_t('Panic button '.($Misc->Panic?'turned on':'turned off'));
	}

	public function resetPassConfirm(array $params)
	{
		global $DB;

		$User= new User($params['userID']);

		return l_t('Are you sure you want to reset the password of this user?');
	}

	public function resetPass(array $params)
	{
		global $DB,$User;

		$ChangeUser= new User($params['userID']);
		if( $ChangeUser->type['Admin'] || ( $ChangeUser->type['Moderator'] && !$User->type['Admin'] ) )
		{
			throw new Exception(l_t("Cannot reset an admin/moderator's password if you aren't admin."));
		}
		if ( $ChangeUser->type['Bot'] && !$User->type['Admin'] )
		{
			throw new Exception(l_t("Cannot reset a bot's password if you aren't admin."));
		}

		$password = base64_encode(rand(1000000,2000000));

		$DB->sql_put( "UPDATE wD_Users SET password = UNHEX('".libAuth::pass_Hash($password)."') WHERE id = ".$ChangeUser->id );

		return l_t('Users password reset to %s',$password);
	}

	public function drawGameConfirm(array $params)
	{
		global $DB;

		require_once(l_r('objects/game.php'));

		$Variant=libVariant::loadFromGameID($params['gameID']);
		$Game = $Variant->Game($params['gameID']);

		return l_t('Are you sure you want to draw this game? This is really hard to undo, so be sure this is correct!');
	}

	public function drawGame(array $params)
	{
		global $DB, $Game;

		$gameID = (int)$params['gameID'];

		$DB->sql_put("BEGIN");

		require_once(l_r('gamemaster/game.php'));
		$Variant=libVariant::loadFromGameID($gameID);
		$Game = $Variant->processGame($gameID);

		if( $Game->phase != 'Diplomacy' and $Game->phase != 'Retreats' and $Game->phase != 'Builds' )
		{
			throw new Exception(l_t('This game is in phase %s, so it can\'t be drawn.',$Game->phase), 987);
		}

		$Game->setDrawn();

		$DB->sql_put("COMMIT");

		return l_t('The game was drawn.');
	}

	public function cancelGameConfirm(array $params)
	{
		global $DB;

		require_once('objects/game.php');

		$Variant=libVariant::loadFromGameID($params['gameID']);
		$Game = $Variant->Game($params['gameID']);

		return l_t('Are you sure you want to cancel this game? This is really hard to undo, so be sure this is correct!');
	}

	public function cancelGame(array $params)
	{
		global $DB, $Game, $User;

		$gameID = (int)$params['gameID'];

		$DB->sql_put("BEGIN");

		require_once(l_r('gamemaster/game.php'));
		$Variant=libVariant::loadFromGameID($gameID);
		$Game = $Variant->processGame($gameID);

		if( $Game->phase == 'Diplomacy' or $Game->phase == 'Retreats' or $Game->phase == 'Builds' )
		{
			$name = addslashes($Game->name);
			$pot = $Game->pot;
			$potType = $Game->potType;
			$varID = $Game->variantID;

			$logInfo = 'Game ID: '.$name.' was cancelled. Name: '.$name.', Pot: '.$pot.', Pot Type: '.$potType.', VariantID: '.$varID;

			$tabl = $DB->sql_tabl("SELECT countryID, userID, bet, status FROM wD_Members WHERE gameID=".$gameID);
			while(list($curCountryID,$curUserID,$curBet,$curStatus) = $DB->tabl_row($tabl))
			{
				$logInfo = $logInfo . ', {CountryID: '.$curCountryID.', UserID: '.$curUserID.', Bet: '.$curBet.', Status: '.$curStatus.'}';
			}
			$DB->sql_put("INSERT INTO wD_AdminLog ( name, userID, time, details, params )
									VALUES ( 'Game Cancelled', ".$User->id.", ".time().", '".$logInfo."', '' )");
			$Game->setCancelled(); 
			// This throws an exception, since it expects to be run from within the main gamemaster loop, and wants to stop the loop from continuing to use this game after
			// it has been cancelled. But it also contains its own commit, so the exception does not prevent the game from being cancelled (it is messy though).

			// This point after $Game->setCancelled(); shouldn't actually be reached.
		}
		elseif( $Game->phase == 'Finished' )
		{
			/*
			 * Some special action is needed; this game has already finished.
			 *
			 * We need to get back all winnings that have been distributed first, then we need to
			 * return all starting bets.
			 *
			 * Note: with the introduction of new scoring systems and with the introduction of free takeovers this logic no longer works right.
			 * As such it is being commented out. It is more important for moderators to be able to cancel an old game if absolutely necessary
			 * and to have to make manual point adjustments then to have this key functionality broken.
			 */

			/*$transactions = array();
			$sumPoints = 0; // Used to ensure the total points transactions add up roughly to 0
			$tabl = $DB->sql_tabl("SELECT type, points, userID, memberID FROM wD_PointsTransactions WHERE gameID = ".$Game->id
				." FOR UPDATE"); // Lock it for update, so other transactions can't interfere with these ones
			while(list($type, $points, $userID, $memberID) = $DB->tabl_row($tabl))
			{
				if( !isset($transactions[$userID])) $transactions[$userID] = array();
				if( !isset($transactions[$userID][$type])) $transactions[$userID][$type] = 0;

				if( $type != 'Bet' ) $points = $points * -1; // Bets are to be credited back, everything else is to be debited

				if( $type != 'Supplement') $sumPoints += $points;

				$transactions[$userID][$type] += $points;
			}

			// Check that the total points transactions within this game make sense (i.e. they add up to roughly 0 accounting for rounding errors)
			if( $sumPoints < (count($transactions)*-1) or count($transactions) < $sumPoints )
				throw new Exception(l_t("The total points transactions (in a finished game) add up to %s, but there are %s members; ".
					"cannot cancel game with an unusual points transaction log.", $sumPoints, count($transactions)), 274);

			// The points transactions make sense; we can now try and reverse them.

			// Get the current points each user has
			$tabl = $DB->sql_tabl("SELECT u.id, u.points FROM wD_Users u INNER JOIN wD_PointsTransactions pt ON pt.userID = u.id WHERE pt.gameID = ".$Game->id
			." GROUP BY u.id, u.points "
			." FOR UPDATE"); // Lock it for update, so other transactions can't interfere with these ones
			$pointsInAccount = array();
			$pointsInPlay = array();
			while(list($userID, $points) = $DB->tabl_row($tabl))
			{
				$sumPoints = 0;
				foreach($transactions[$userID] as $type=>$typePoints)
					$sumPoints += $typePoints;


				if( ( $points + $sumPoints) < 0 )
				{
					// If the user doesn't have enough points on hand to pay back the points transactions for this game we will need to supplement him the points to do it:
					$supplementPoints = -($points + $sumPoints);
					$points += $supplementPoints;
					$DB->sql_put("INSERT INTO wD_PointsTransactions ( type, points, userID, gameID ) VALUES ( 'Supplement', ".$supplementPoints.", ".$userID.", ".$Game->id.")");
					$DB->sql_put("UPDATE wD_Users SET points = ".$points." WHERE id = ".$userID);
				}

				// Now we have given the user enough points so their points transactions for this game can definitely be undone:
				$DB->sql_put("INSERT INTO wD_PointsTransactions ( type, points, userID, gameID ) VALUES ( 'Correction', ".$sumPoints.", ".$userID.", ".$Game->id.")");
				$points += $sumPoints;
				$DB->sql_put("UPDATE wD_Users SET points = ".$points." WHERE id = ".$userID);

				// Now check that they don't need a supplement to bring their total points in play back up to 100:
				$pointsInPlay = User::pointsInPlay($userID);
				if( ($points + $pointsInPlay) < 100 )
				{
					$supplementPoints = 100 - ($points + $pointsInPlay);
					$points += $supplementPoints;
					$DB->sql_put("INSERT INTO wD_PointsTransactions ( type, points, userID, gameID ) VALUES ( 'Supplement', ".$supplementPoints.", ".$userID.", ".$Game->id.")");
					$DB->sql_put("UPDATE wD_Users SET points = ".$points." WHERE id = ".$userID);
				}

				notice::send(
					$userID, $Game->id, 'Game',
					'No', 'No',
					l_t("This game has been cancelled after having finished (usually to undo the effects of cheating). ".
						"%s points had to be added/taken from your account to undo the effects of the game. ".
					"Please contact the mod team with any queries.", $sumPoints, $points),
					$Game->name, $Game->id);
			}*/

			// Now backup and erase the game from existence, then commit:
			$name = addslashes($Game->name);
			$pot = $Game->pot;
			$potType = $Game->potType;
			$varID = $Game->variantID;

			$logInfo = 'Game ID: '.$name.' was cancelled. Name: '.$name.', Pot: '.$pot.', Pot Type: '.$potType.', VariantID: '.$varID;

			$tabl = $DB->sql_tabl("SELECT countryID, userID, bet, status FROM wD_Members WHERE gameID=".$gameID);

			while(list($curCountryID,$curUserID,$curBet,$curStatus) = $DB->tabl_row($tabl))
			{
				$logInfo = $logInfo . ', {CountryID: '.$curCountryID.', UserID: '.$curUserID.', Bet: '.$curBet.', Status: '.$curStatus.'}';
			}
			$DB->sql_put("INSERT INTO wD_AdminLog ( name, userID, time, details, params ) VALUES ( 'Game Cancelled', ".$User->id.", ".time().", '".$logInfo."', '' )");
			processGame::eraseGame($Game->id);
		}
		else
		{
			throw new Exception(l_t('This game is in phase %s, so it can\'t be cancelled',$Game->phase), 987);
		}

		return l_t('This game was cancelled.');
	}

	public function togglePause(array $params)
	{
		global $DB, $Game;

		$gameID = (int)$params['gameID'];

		$DB->sql_put("BEGIN");

		require_once(l_r('gamemaster/game.php'));
		$Variant=libVariant::loadFromGameID($gameID);
		$Game = $Variant->processGame($gameID);

		$Game->togglePause();

		$DB->sql_put("COMMIT");

		return l_t('This game is now '.( $Game->processStatus == 'Paused' ? 'paused':'unpaused').'.');
	}

	public function cdUserConfirm(array $params)
	{
		global $DB;

		require_once('objects/game.php');

		$User = new User($params['userID']);
		if ( isset($params['gameID']) && $params['gameID'] )
		{
			$Variant=libVariant::loadFromGameID($params['gameID']);
			$Game = $Variant->Game($params['gameID']);
		}

		return l_t('Are you sure you want to put this user into civil-disorder'.(isset($Game)?', in this game':', in all his games').'?');
	}

	public function cdUser(array $params)
	{
		global $DB, $Game;

		require_once(l_r('gamemaster/game.php'));

		$User = new User($params['userID']);
		if ( isset($params['gameID']) && $params['gameID'] )
		{
			$Variant=libVariant::loadFromGameID($params['gameID']);
			$Game = $Variant->processGame($params['gameID']);

			// If the game is finished do not CD and throw an error.
			if( $Game->phase == 'Finished' )
			{
				throw new Exception(l_t("Invalid phase to set CD"));
			}

			// If the game hasn't started check if there's just 1 person in it. If there is then delete the game, otherwise remove that 1 user.
			else if( $Game->phase == 'Pre-game' )
			{
				if(count($Game->Members->ByID)==1) 
				{
					processGame::eraseGame($Game->id);
				}
				else
				{
					$DB->sql_put("DELETE FROM wD_Members WHERE gameID = ".$Game->id." AND userID = ".$params['userID']);

					// If there are still people in the game reset the min bet in case the game was full to readd the join button.
					$Game->resetMinimumBet();
				}
			}
			else
			{
				$Game->Members->ByUserID[$User->id]->setLeft(1);
				$Game->resetMinimumBet();
			}
		}
		// This does not work, should be fixed eventually. 
		else
		{
			$tabl = $DB->sql_tabl("SELECT gameID, status FROM wD_Members WHERE userID = ".$userID);
			while( list($gameID, $status) = $DB->tabl_row($tabl) )
			{
				if ( $status != 'Playing' ) continue;

				$Variant=libVariant::loadFromGameID($gameID);
				$Game = $Variant->processGame($gameID);
				$Game->Members->ByUserID[$User->id]->setLeft(1);
				$Game->resetMinimumBet();
			}
		}

		return l_t('This user was put into civil-disorder'.((isset($params['gameID']) && $params['gameID'])?', in this game':', in all his games'));
	}

	public function replaceCoutries(array $params)
	{
		global $DB;

		$gameIDs = (int)$params['gameIDs'];
		$userID = (int)$params['userID'];
		$replaceID = (int)$params['replaceID'];
		$games = array();
		$tabl = $DB->sql_tabl( 'SELECT gameID FROM wD_Members WHERE status = "Playing" AND userID = "'.$userID.'"'.($gameIDs != 0 ? ' AND gameID = "'.$gameIDs.'"':'') );

		while(list($gameID) = $DB->tabl_row($tabl))
		{
			$games[] = $gameID;
		}

		// Load the two users as Userobjects.
		try
		{
			$SendToUser = new User($replaceID);
		}
		catch (Exception $e)
		{
			$error = l_t("Invalid user ID given.");
		}

		try
		{
			$SendFromUser = new User($userID);
		}
		catch (Exception $e)
		{
			$error = l_t("Invalid user ID given.");
		}
		$ret = '';

		foreach ($games AS $gameID)
		{
			$Variant=libVariant::loadFromGameID($gameID);
			$Game = $Variant->Game($gameID);

			list($blocked) = $DB->sql_row("SELECT count(*) FROM wD_Members AS m WHERE m.gameID = ".$Game->id);

			// Check for additional requirements:
			if ( $Game->minimumReliabilityRating > $SendToUser->reliabilityRating)
			{
				$ret .= '<b>Error:</b> The reliability of '.$SendToUser->username.' is not high enough to join the game <a href="board.php?gameID='.$Game->id.'">'.$Game->name.'</a>.<br>';
			}

			elseif ( array_key_exists ( $SendToUser->id , $Game->Members->ByUserID))
			{
				$ret .= '<b>Error:</b> '.$SendToUser->username.' is already a member of the game <a href="board.php?gameID='.$Game->id.'">'.$Game->name.'</a>.<br>';
			}

			else
			{
				$DB->sql_put("UPDATE wD_Members SET userID = ".$SendToUser->id." WHERE userID=".$SendFromUser->id." AND gameID=".$Game->id);
				$ret.= 'In game <a href="board.php?gameID='.$Game->id.'">'.$Game->name.'</a> the user '.$SendFromUser->username.' was removed and replaced by '.$SendToUser->username.'.<br>';
			}
		}
		return $ret;
	}

	public function replaceCoutriesConfirm(array $params)
	{
		global $DB;

		$userID = (int)$params['userID'];
		$replaceID = (int)$params['replaceID'];
		$gameIDs = (int)$params['gameIDs'];

		list($userName) = $DB->sql_row("SELECT username FROM wD_Users WHERE id=".$userID);
		list($replaceName) = $DB->sql_row("SELECT username FROM wD_Users WHERE id=".$replaceID);

		if ($gameIDs == 0)
		{
			return 'The user '.$userName.' will be removed and replaced by '.$replaceName.' in all his active games.';
		}

		list($gameName) = $DB->sql_row("SELECT name FROM wD_Games WHERE id=".$gameIDs);
		return 'In game '.$gameName.' (id='.$gameIDs.') the user '.$userName.' will be removed and replaced by '.$replaceName.'.';
	}

	public function banUserConfirm(array $params)
	{
		global $DB;

		$User = new User($params['userID']);

		if( !isset($params['reason']) || strlen($params['reason'])==0 )
			return l_t('Couldn\'t ban user; no reason was given.');

		return l_t('Are you sure you want to ban %s (Reason: "%s")? This removed them from all their games, so be sure this is correct!',
			$User->username,$DB->msg_escape($params['reason']));
	}
	
	public function banUser(array $params)
	{
		global $User, $DB, $Game;

		$userID = (int)$params['userID'];

		if( !isset($params['reason']) || strlen($params['reason'])==0 )
			return l_t("Couldn't ban user because no reason was given.");

		$banReason = $DB->msg_escape($params['reason']);

		$banUser = new User($userID);

		if( $banUser->type['Banned'] )
			throw new Exception(l_t("The user is already banned"));

		if( $banUser->type['Admin'] )
			throw new Exception(l_t("Admins can't be banned"));
			
		if( $banUser->type['Bot'] )
			throw new Exception(l_t("Bots can't be banned"));

		if( $banUser->type['Moderator'] and ! $User->type['Admin'] )
			throw new Exception(l_t("Moderators can't be banned by non-admins"));

		User::banUser($userID, l_t("Banned by a moderator:").' '.$params['reason']);

		require_once(l_r('gamemaster/game.php'));

		/*
		 * Explain what has happened to the games the banned user was in, and extend the
		 * turn
		 */
		$tabl = $DB->sql_tabl("SELECT gameID, status FROM wD_Members WHERE userID = ".$userID);

		while( list($gameID, $status) = $DB->tabl_row($tabl) )
		{
			if ( $status != 'Playing' ) continue;

			$Variant=libVariant::loadFromGameID($gameID);
			$Game = $Variant->processGame($gameID);

			$banMessage = l_t('%s was banned: %s. ',$banUser->username,$banReason);

			if( $Game->phase == 'Pre-game' )
			{
				if(count($Game->Members->ByID)==1)
					processGame::eraseGame($Game->id);
				else
				{
					$DB->sql_put("DELETE FROM wD_Members WHERE gameID = ".$Game->id." AND userID = ".$userID);

					// If there are still people in the game reset the min bet in case the game was full to readd the join button.
					$Game->resetMinimumBet();
				}
			}
			elseif( $Game->processStatus != 'Paused' and $Game->phase != 'Finished' )
			{
				// The game may need a time extension to allow for a new player to be added

				// Would the time extension would give a difference of more than ten minutes? If not don't bother
				if ( (time() + $Game->phaseMinutes*60) - $Game->processTime > 10*60 ) {

					// It is worth adding an extension
					$DB->sql_put("UPDATE wD_Games SET processTime = ".time()." + phaseMinutes*60 WHERE id = ".$Game->id );
					$Game->processTime = time() + $Game->phaseMinutes*60;

					$banMessage .= l_t('The time until the next phase has been extended by one phase length '.
						'to give an opportunity to replace the player.')."\n".
						l_t('Remember to finalize your orders if you don\'t want to wait, so the game isn\'t held up unnecessarily!');
				}
			}

			// If the game is still running first remove the player from the game and reset the minimum bet so other can join.
			if( $Game->phase != 'Finished' && $Game->phase != 'Pre-game')
			{
				$Game->Members->ByUserID[$userID]->setLeft(1);
				$Game->resetMinimumBet();
			}

			libGameMessage::send('Global','GameMaster', $banMessage);

			$Game->Members->sendToPlaying('No', l_t('%s was banned, see in-game for details.',$banUser->username));
		}

		$DB->sql_put("UPDATE wD_Orders o INNER JOIN wD_Members m ON ( m.gameID = o.gameID AND m.countryID = o.countryID )
					SET o.toTerrID = NULL, o.fromTerrID = NULL WHERE m.userID = ".$userID);

		unset($Game);

		return l_t('This user was banned, and had their %s points removed and their games set to civil disorder.',$banUser->points);
	}

	public function tempBan(array $params)
	{
		global $DB;

		$userID = (int)$params['userID'];
		$days   = (int)$params['ban'];
		
		$banUser = new User($userID);
		
		if( $banUser->type['Bot'] )
			throw new Exception(l_t("Bots can't be banned"));

		if( !isset($params['reason']) || strlen($params['reason'])==0 )
			return 'Cannot temp ban user without a reason.';

		$reason = $DB->msg_escape($params['reason']);

		User::tempBanUser($userID, $days, $reason);

		if ($days == 0)
			return 'This user is now unblocked and can join and create games again.';

		return 'This user is now blocked from joining, rejoining, and creating games for <b>'.$days.'</b> days.';
	}

	public function modExcuseDelay(array $params)
	{
		global $DB;

		$userID = (int)$params['userID'];
		$excuseID   = (int)$params['excuseID'];

		if( !isset($params['reason']) || strlen($params['reason'])==0 )
			return l_t('Couldn\'t ban user; no reason was given.');

		$modReason = $DB->msg_escape($params['reason']);

		$DB->sql_put("UPDATE wD_MissedTurns SET modExcused = 1, modExcusedReason = '".$modReason."' WHERE id=".$excuseID);

		return 'This user\'s missed turn has been excused.';
	}

	public function givePoints(array $params)
	{
		global $DB;
		$User = new User($params['userID']);

		$userID = (int)$params['userID'];
		$points = (int)$params['points'];

		$giveUser = new User($userID);

		if( $points > 0 )
			User::pointsTransfer($giveUser->id, 'Supplement', $points);
		else
		{
			$points = (-1)*$points;

			if ( ($User->points - $points) < 0) 
			{
				$modMessedUp = $User->points;
				$DB->sql_put("UPDATE wD_Users SET points = 0 WHERE id=".$userID);
				return l_t('This user had all their points ('.$modMessedUp.') removed because a mod tried removing more points than the user had.',$points,libHTML::points());
			}

			$DB->sql_put("UPDATE wD_Users SET points = points - ".$points." WHERE id=".$userID);
		}

		return l_t('This user was transferred %s %s.',$points,libHTML::points());
	}

	public function updateEmergencyDate(array $params)
	{
		global $DB;

		$userID = (int)$params['userID'];
		$setting = (int)$params['setting'];
		$targetUser = new User($userID);

		if( $setting >= 0 ) { $targetUser->updateEmergencyPauseDate($setting); }

		return 'This users emergency pause date was set to '.$setting;
	}

	public function unbanUser(array $params)
	{
		global $DB;

		$userID = (int)$params['userID'];

		$unbanUser = new User($userID);

		if( ! $unbanUser->type['Banned'] )
			throw new Exception(l_t("Can't unban a user which isn't banned"));

		$DB->sql_put(
			"UPDATE wD_Users SET type = 'User' WHERE id = ".$userID
		);

		return l_t('This user was unbanned.');
	}

	public function setDirector(array $params)
	{
		global $DB;

		$userID = (int)$params['userID'];
		$gameID = (int)$params['gameID'];

		$DB->sql_put("UPDATE wD_Games SET directorUserID = ".$userID." WHERE id = ".$gameID);

		return l_t("The specified user ID has been assigned as the director for this game.");
	}

	public function excusedMissedTurnsIncreaseAll(array $params)
	{
		global $DB;

		$gameID = (int)$params['gameID'];

		$DB->sql_put("UPDATE wD_Members SET excusedMissedTurns = excusedMissedTurns + 1 WHERE gameID = ".$gameID);
		return l_t("All users in this game have been given an extra excused missed turn.");
	}

	public function excusedMissedTurnsDecreaseAll(array $params)
	{
		global $DB;

		$gameID = (int)$params['gameID'];

		$DB->sql_put("UPDATE wD_Members SET excusedMissedTurns = excusedMissedTurns - 1 WHERE gameID = ".$gameID." and excusedMissedTurns > 0");
		return l_t("All users in this game have had an excused missed turn removed.");
	}

	public function excusedMissedTurnsIncrease(array $params)
	{
		global $DB;

		$userIDtoUpdate = (int)$params['userID'];
		$gameID = (int)$params['gameID'];

		if ($userIDtoUpdate > 0)
		{
			$DB->sql_put("UPDATE wD_Members SET excusedMissedTurns = excusedMissedTurns + 1 WHERE gameID = ".$gameID." and userID = ".$userIDtoUpdate);
			return l_t("UserID: ".$userIDtoUpdate." has been given an extra excused missed turn in this game.");
		}
	}

	public function excusedMissedTurnsDecrease(array $params)
	{
		global $DB;

		$userIDtoUpdate = (int)$params['userID'];
		$gameID = (int)$params['gameID'];

		if ($userIDtoUpdate > 0)
		{
			$DB->sql_put("UPDATE wD_Members SET excusedMissedTurns = excusedMissedTurns - 1 WHERE gameID = ".$gameID." and userID = ".$userIDtoUpdate." and excusedMissedTurns > 0");
			return l_t("UserID: ".$userIDtoUpdate." has had an excused missed turn removed in this game.");
		}
	}

	public function recalculateUserRR(array $params)
	{
		global $DB;

		$userIDtoUpdate = (int)$params['userID'];

		require_once(l_r('gamemaster/gamemaster.php'));
		 
		$year = time() - 31536000;
		$lastMonth = time() - 2419200;
		$lastWeek = time() - 604800;

		$RELIABILITY_QUERY = "
		UPDATE wD_Users u 
		set u.reliabilityRating = greatest(0, 
		(100 *(1 - ((SELECT COUNT(1) FROM wD_MissedTurns t  WHERE t.userID = u.id AND t.modExcused = 0 and t.turnDateTime > ".$year.") / greatest(1,u.yearlyPhaseCount))))
		-(6*(SELECT COUNT(1) FROM wD_MissedTurns t  WHERE t.userID = u.id AND t.liveGame = 0 AND t.modExcused = 0 and t.samePeriodExcused = 0 and t.systemExcused = 0 and t.turnDateTime > ".$lastMonth."))
		-(6*(SELECT COUNT(1) FROM wD_MissedTurns t  WHERE t.userID = u.id AND t.liveGame = 1 AND t.modExcused = 0 and t.samePeriodExcused = 0 and t.systemExcused = 0 and t.turnDateTime > ".$lastWeek."))
		-(5*(SELECT COUNT(1) FROM wD_MissedTurns t  WHERE t.userID = u.id AND t.liveGame = 1 AND t.modExcused = 0 and t.samePeriodExcused = 0 and t.systemExcused = 0 and t.turnDateTime > ".$lastMonth."))
		-(5*(SELECT COUNT(1) FROM wD_MissedTurns t  WHERE t.userID = u.id AND t.liveGame = 0 AND t.modExcused = 0 and t.samePeriodExcused = 0 and t.systemExcused = 0 and t.turnDateTime > ".$year.")))
		where u.id = ".$userIDtoUpdate;

		$DB->sql_put($RELIABILITY_QUERY);

		return "This user's RR has been recalculated.";
	}
}

?>
