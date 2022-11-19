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
 * This class will enable adminActions and adminActionsForum moderator
 * tasks to be performed, but also allow tasks which only admins should
 * be able to perform.
 * This will be included anyway, but the class will only be initialized if
 * the user is an admin.
 *
 * @package Admin
 */
class adminActionsSeniorMod extends adminActionsForum
{
	public function __construct()
	{
		global $Misc;

		parent::__construct();

		$seniorModActions = array(
			'panic' => array(
				'name' => 'Toggle panic button',
				'description' => 'Toggle the panic button; turning it on prevents games from being processed, users joining games,
						users registering. It is intended to limit the damage a problem can do.
						<em>This effectively shuts down the site, so be sure before pressing this.</em>',
				'params' => array(),
			),
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
			'resetPass' => array(
				'name' => 'Reset password',
				'description' => 'Resets a users password',
				'params' => array('userID'=>'User ID'),
			),
			'setDirector' => array(
				'name' => 'Set a user as a game director',
				'description' => 'Sets the given user ID to be the director of the given game ID (set to 0 to remove someone as game director).
					This will give them mod capabilities for this game.',
				'params' => array('gameID'=>'Game ID','userID'=>'User ID'),
			),
			'setProcessTimeToNow' => array(
				'name' => 'Process game now',
				'description' => 'Set a game process time to now, resulting in it being processed now.<br />
					<em>Be careful:</em> this will cause any players without submitted moves to NMR.',
				'params' => array('gameID'=>'Game ID'),
			),
			'modExcuseDelay' => array(
				'name' => 'Mod Excuse Missed Turn',
				'description' => 'Enter the user to excuse and the ID of the missed turn to excuse (found on RR breakdown page)',
				'params' => array('userID'=>'User ID', 'excuseID'=>'Excuse ID','reason'=>'Reason')
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
		);

		adminActions::$actions = array_merge(adminActions::$actions, $seniorModActions);
	}

	public function panic(array $params)
	{
		global $Misc;

		$Misc->Panic = 1-$Misc->Panic;
		$Misc->write();

		return l_t('Panic button '.($Misc->Panic?'turned on':'turned off'));
	}

	public function modExcuseDelay(array $params)
	{
		global $DB;

		$userID = (int)$params['userID'];
		$excuseID   = (int)$params['excuseID'];

		if( !isset($params['reason']) || strlen($params['reason'])==0 )
			return l_t('Couldn\'t excuse delay for user; no reason was given.');

		$modReason = $DB->msg_escape($params['reason']);

		// Set the mod excused flag, and set the reliability period to -1 to trigger a recalculation
		$DB->sql_put("UPDATE wD_MissedTurns SET modExcused = 1, modExcusedReason = '".$modReason."' WHERE id=".$excuseID);
		
		require_once(l_r('gamemaster/gamemaster.php'));
		 
		libGameMaster::updateReliabilityRatings(false, array($userID));

		return 'This user\'s missed turn has been excused and RRs have been recalculated.';
	}

	public function modExcuseDelayByPeriod(array $params)
	{
		global $DB;

		$periodStartTime = (int)$params['periodStartTime'];
		$periodEndTime   = (int)$params['periodEndTime'];

		if( !isset($params['reason']) || strlen($params['reason'])==0 )
			return l_t('Couldn\'t excuse delay for period; no reason was given.');

		$modReason = $DB->msg_escape($params['reason']);

		// Set the mod excused flag, and set the reliability period to -1 to trigger a recalculation
		$DB->sql_put("UPDATE wD_MissedTurns SET modExcused = 1, modExcusedReason = '".$modReason."' WHERE turnDateTime>=".$periodStartTime." AND turnDateTime <= ".$periodEndTime);

		$tabl = $DB->sql_tabl("SELECT DISTINCT userID FROM wD_MissedTurns WHERE turnDateTime>=".$periodStartTime." AND turnDateTime <= ".$periodEndTime);
		$userIDs = array();
		while(list($userID) = $DB->tabl_row($tabl)) $userIDs[]=$userID;
		 
		require_once(l_r('gamemaster/gamemaster.php'));
		libGameMaster::updateReliabilityRatings(false, $userIDs);

		return 'Missed turns in this period have been excused and RRs have been recalculated.';
	}

	public function setProcessTimeToNowConfirm(array $params)
	{
		global $DB;

		require_once(l_r('objects/game.php'));

		$Variant=libVariant::loadFromGameID($params['gameID']);
		$Game = $Variant->Game($params['gameID']);

		return l_t('Are you sure you want to start processing this game now?');
	}

	public function setDirector(array $params)
	{
		global $DB;

		$userID = (int)$params['userID'];
		$gameID = (int)$params['gameID'];

		$DB->sql_put("UPDATE wD_Games SET directorUserID = ".$userID." WHERE id = ".$gameID);

		return l_t("The specified user ID has been assigned as the director for this game.");
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
}
?>
