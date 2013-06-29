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
 * This class is run from adminActionsForms, when run from within board.php, which limits the
 * actions which can be performed to those which TDs need to be able to run, and ensures that the gameID
 * parameter is always for the correct game.
 *
 * @package Admin
 */
class adminActionsTD extends adminActionsForms
{
	public static $actions = array(
			'drawGame' => array(
				'name' => 'Draw game',
				'description' => 'Splits points among all the surviving players in a game equally, and ends the game.',
				'params' => array(),
			),
			'cancelGame' => array(
				'name' => 'Cancel game',
				'description' => 'Splits points among all players in a game equally, and deletes the game.',
				'params' => array(),
			),
			'togglePause' => array(
				'name' => 'Toggle-pause game',
				'description' => 'Flips a game\'s paused status; if it\'s paused it\'s unpaused, otherwise it\'s paused.',
				'params' => array(),
			),
			'makePublic' => array(
				'name' => 'Make public a private game',
				'description' => 'Removes a private game\'s password.',
				'params' => array(),
			),
			'makePrivate' => array(
				'name' => 'Make a public game private',
				'description' => 'Add a password to a private game.',
				'params' => array('password'=>'Password'),
			),
			'cdUser' => array(
				'name' => 'Force a user into CD',
				'description' => 'Force a user into CD in this game.',
				'params' => array('userID'=>'User ID'),
			),
			'setProcessTimeToPhase' => array(
				'name' => 'Reset process time',
				'description' => 'Set a game process time to now + the phase length, resetting the turn length',
				'params' => array(),
			),
			'setProcessTimeToNow' => array(
				'name' => 'Process game now',
				'description' => 'Set a game process time to now, resulting in it being processed now',
				'params' => array(),
			),
			'changePhaseLength' => array(
				'name' => 'Change phase length',
				'description' => 'Change the maximum number of minutes that a phase lasts.
					The time must be given in minutes (5 minutes to 10 days = 5-14400).<br />
					Also the next process time is reset to the new phase length.',
				'params' => array('phaseMinutes'=>'Minutes per phase'),
			),
			'countryReallocate' => array(
				'name' => 'Reallocate countries',
				'description' => 'Alter which player has which country. Enter a list like so:
					"<em>R,T,A,G,U,F,E</em>".<br />
					The result will be that England will be set to Russia, France to Turkey, etc.<br /><br />
					If you aren\'t sure about the order of each country just enter the gameID without anything else and the list of
					countries in the order will be output.<br /><br />
					To prevent people sharing invalid info before the countries have been reallocated only no-message games
					can have their countries reallocated; messages should be enabled only after the countries have been reallocated.<br /><br />
					(The substitution string to reverse the reallocation will be generated, in case you need to reverse the reallocation.)<br />
					(If changing the countries of a variant for which the first letter of the countries are not distinct countryID numbers must be used instead.)<br />
					(Alternatively you can enter [userID1]=[countryLetter1],[userID2]=[countryLetter2],etc)',
				'params' => array(
					'reallocations'=>'Reallocations list (e.g "<em>R,T,A,G,U,F,E</em>")'
					)
			),
			'alterMessaging' => array(
				'name' => 'Alter game messaging',
				'description' => 'Change a game\'s messaging settings, e.g. to convert from gunboat to public-only or all messages allowed.',
				'params' => array(
					'newSetting'=>'Enter a number for the desired setting: 1=Regular, 2=PublicPressOnly, 3=NoPress'
					),
			)
		);

	private $fixedGameID;
	public function __construct()
	{
		global $Game;
		
		$this->fixedGameID = $Game->id;
	}
	public function countryReallocate(array $params)
	{
		global $DB;

		$gameID=$this->fixedGameID;

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
	public function alterMessaging(array $params)
	{
		global $DB;

		$gameID=(int)$this->fixedGameID;
		$newSetting=(int)$params['newSetting'];

		switch($newSetting)
		{
			case 1: $newSettingName='Regular'; break;
			case 2: $newSettingName='PublicPressOnly'; break;
			case 3: $newSettingName='NoPress'; break;
			default: throw new Exception(l_t("Invalid messaging setting; enter 1, 2, or 3."));
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

		$Variant=libVariant::loadFromGameID($this->fixedGameID);
		$Game = $Variant->Game($this->fixedGameID);

		if( $Game->processStatus != 'Not-processing' || $Game->phase == 'Finished' )
			throw new Exception(l_t("Game is either crashed/paused/finished/processing, and so the next-process time cannot be altered."));

		$oldPhaseMinutes = $Game->phaseMinutes;

		$Game->phaseMinutes = $newPhaseMinutes;
		$Game->processTime = time()+($newPhaseMinutes*60);

		$DB->sql_put("UPDATE wD_Games SET
			phaseMinutes = ".$Game->phaseMinutes.",
			processTime = ".$Game->processTime."
			WHERE id = ".$Game->id);

		return l_t('Process time changed from %s to %s. Next process time is %s.',
			libTime::timeLengthText($oldPhaseMinutes*60),libTime::timeLengthText($Game->phaseMinutes*60),libTime::text($Game->processTime));
	}
	public function setProcessTimeToPhaseConfirm(array $params)
	{
		global $DB;

		require_once(l_r('objects/game.php'));

		$gameID = intval($this->fixedGameID);

		$Variant=libVariant::loadFromGameID($gameID);
		$Game = $Variant->Game($gameID);

		return l_t('Are you sure you want to reset the phase process time of this game to process in %s hours?',($Game->phaseMinutes/60));
	}
	public function setProcessTimeToPhase(array $params)
	{
		global $DB;

		$gameID = intval($this->fixedGameID);

		$Variant=libVariant::loadFromGameID($gameID);
		$Game = $Variant->Game($gameID);

		if( $Game->processStatus != 'Not-processing' || $Game->phase == 'Finished' )
			return l_t('This game is paused/crashed/finished.');

		$DB->sql_put(
			"UPDATE wD_Games
			SET processTime = ".time()." + phaseMinutes * 60
			WHERE id = ".$Game->id
		);

		return l_t('Process time reset successfully');
	}
	public function makePublic(array $params)
	{
		global $DB;

		$gameID = intval($this->fixedGameID);

		$DB->sql_put(
			"UPDATE wD_Games
			SET password = NULL
			WHERE id = ".$gameID
		);

		return l_t('Password removed');
	}
	public function makePrivate(array $params)
	{
		global $DB;

		$gameID = intval($this->fixedGameID);
		$password=$params['password'];

		$DB->sql_put(
			"UPDATE wD_Games
			SET password = UNHEX('".md5($password)."')
			WHERE id = ".$gameID
		);

		return l_t('Password set to "%s"',$password);
	}
	public function setProcessTimeToNow(array $params)
	{
		global $DB;

		$gameID = intval($this->fixedGameID);

		$Variant=libVariant::loadFromGameID($gameID);
		$Game = $Variant->Game($gameID);

		if( $Game->processStatus != 'Not-processing' || $Game->phase == 'Finished' )
			return l_t('This game is paused/crashed/finished.');

		$DB->sql_put(
			"UPDATE wD_Games
			SET processTime = ".time()."
			WHERE id = ".$Game->id
		);

		return 'Process time set to now successfully';
	}
	public function setProcessTimeToNowConfirm(array $params)
	{
		global $DB;

		require_once(l_r('objects/game.php'));

		$Variant=libVariant::loadFromGameID($this->fixedGameID);
		$Game = $Variant->Game($this->fixedGameID);

		return l_t('Are you sure you want to start processing this game now?');
	}
	public function drawGameConfirm(array $params)
	{
		global $DB;

		require_once(l_r('objects/game.php'));

		$Variant=libVariant::loadFromGameID($this->fixedGameID);
		$Game = $Variant->Game($this->fixedGameID);

		return l_t('Are you sure you want to draw this game? This is really hard to undo, so be sure this is correct!');
	}
	public function drawGame(array $params)
	{
		global $DB, $Game;

		$gameID = (int)$this->fixedGameID;

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

		$Variant=libVariant::loadFromGameID($this->fixedGameID);
		$Game = $Variant->Game($this->fixedGameID);

		return l_t('Are you sure you want to cancel this game? This is really hard to undo, so be sure this is correct!');
	}
	public function cancelGame(array $params)
	{
		global $DB, $Game;

		$gameID = (int)$this->fixedGameID;

		$DB->sql_put("BEGIN");

		require_once(l_r('gamemaster/game.php'));
		$Variant=libVariant::loadFromGameID($gameID);
		$Game = $Variant->processGame($gameID);

		if( $Game->phase == 'Diplomacy' or $Game->phase == 'Retreats' or $Game->phase == 'Builds' )
		{
			$Game->setCancelled(); // This throws an exception, since it expects to be run from within the
			// main gamemaster loop, and wants to stop the loop from continuing to use this game after
			// it has been cancelled. But it also contains its own commit, so the exception does not prevent
			// the game from being cancelled (it is messy though).
			
			// This point after $Game->setCancelled(); shouldn't actually be reached.
		}
		elseif( $Game->phase == 'Finished' )
		{
			/* 
			 * Some special action is needed; this game has already finished.
			 * 
			 * We need to get back all winnings that have been distributed first, then we need to 
			 * return all starting bets.
			 */
			$transactions = array();
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
			}
			
			// Now backup and erase the game from existence, then commit:
			processGame::eraseGame($Game->id);
		}
		else
		{
			throw new Exception(l_t('This game is in phase %s, so it can\'t be cancelled',$Game->phase), 987);
		}
		
		// $DB->sql_put("COMMIT"); // $

		return l_t('This game was cancelled.'); 
	}
	public function togglePause(array $params)
	{
		global $DB, $Game;

		$gameID = (int)$this->fixedGameID;

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
		if ( isset($this->fixedGameID) && $this->fixedGameID )
		{
			$Variant=libVariant::loadFromGameID($this->fixedGameID);
			$Game = $Variant->Game($this->fixedGameID);
		}

		return l_t('Are you sure you want to put this user into civil-disorder'.(isset($Game)?', in this game':', in all his games').'?');
	}
	public function cdUser(array $params)
	{
		global $DB;

		require_once(l_r('gamemaster/game.php'));

		$User = new User($params['userID']);
		
			$Variant=libVariant::loadFromGameID($this->fixedGameID);
			$Game = $Variant->processGame($this->fixedGameID);

			if( $Game->phase == 'Pre-game' || $Game->phase == 'Finished' )
				throw new Exception(l_t("Invalid phase to set CD"));

			$Game->Members->ByUserID[$User->id]->setLeft();
			
			$Game->resetMinimumBet();

		return l_t('This user put into civil-disorder in this game');
	}
	public function setCivilDisorderConfirm(array $params)
	{
		global $DB;

		$User = new User($params['userID']);

		require_once(l_r('objects/game.php'));
		$Variant=libVariant::loadFromGameID($this->fixedGameID);
		$Game = $Variant->Game($this->fixedGameID);

		return l_t('Are you sure you want to set this user to civil disorder in this game?');
	}
	public function setCivilDisorder(array $params)
	{
		global $DB;

		$User = new User($params['userID']);

		require_once(l_r('gamemaster/game.php'));
		$Variant=libVariant::loadFromGameID($this->fixedGameID);
		$Game = $Variant->processGame($this->fixedGameID);

		foreach($Game->Members->ByID as $Member)
		{
			if ( $User->id != $Member->userID ) continue;

			if ( $Member->status == 'Playing' )
			{
				$DB->sql_put("UPDATE wD_Members SET status = 'Left' WHERE id=".$Member->id);
				$DB->sql_put("INSERT INTO wD_CivilDisorders ( gameID, userID, countryID, turn, bet, SCCount )
					VALUES ( ".$Game->id.", ".$User->id.", ".$Member->countryID.", ".$Game->turn.", ".$Member->bet.", ".$Member->SCCount.")");

				$Game->resetMinimumBet();
				
				return l_t('User set to civil disorder in game');
			}
		}

		throw new Exception(l_t("User not in specified game"));
	}
}
