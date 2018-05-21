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

require_once(l_r('gamemaster/member.php'));

/**
 * A class to handle manipulating a game's members, sending messages out to them,
 * allocating them points, letting them join, getting stats for them, etc.
 *
 * @package GameMaster
 */
class processMembers extends Members
{
	/**
	 * Load a processMember, overrides Members->loadMember which loads a Member
	 *
	 * @param array $row The database record array hash for this member
	 * @return processMember
	 */
	protected function loadMember(array $row)
	{
		return $this->Game->Variant->processMember($row);
	}

	/**
	 * Send message about the game moving forward
	 */
	function notifyGameProgressed()
	{
		$this->sendToPlaying('No',l_t("Game progressed to %s, %s",$this->Game->phase,$this->Game->datetxt($this->Game->turn)));
	}

	/**
	 * Send message about the game being paused
	 */
	function notifyPaused()
	{
		$this->sendToPlaying('No',l_t("Game has been paused."));
	}

	/**
	 * Send message about the game being unpaused
	 */
	function notifyUnpaused()
	{
		$this->sendToPlaying('No',l_t("Game has been unpaused."));
	}

	/**
	 * Count the units and supply centers of the members in this game, and refresh the
	 * Member objects and update the member records.
	 */
	function countUnitsSCs()
	{
		global $DB;

		// Reset
		foreach($this->ByCountryID as $countryID=>$Member)
		{
			$Member->unitNo = 0;
			$Member->supplyCenterNo = 0;
		}

		// Get unit numbers
		$tabl = $DB->sql_tabl("SELECT COUNT(id), countryID FROM wD_Units
						WHERE gameID = ".$this->Game->id."
						GROUP BY countryID
						HAVING COUNT(id) > 0");
		while ( list($unitNo, $countryID) = $DB->tabl_row($tabl) )
		{
			$this->ByCountryID[$countryID]->unitNo = $unitNo;
		}

		// Get supply center numbers
		$tabl = $DB->sql_tabl("SELECT COUNT(ts.terrID), ts.countryID
						FROM wD_TerrStatus ts
						INNER JOIN wD_Territories t ON ( ts.terrID = t.id )
						WHERE t.supply='Yes' AND ( NOT ts.countryID = 0 )
							AND ts.gameID = ".$this->Game->id."
							AND t.mapID=".$this->Game->Variant->mapID."
						GROUP BY ts.countryID
						HAVING COUNT(ts.terrID) > 0");
		$supplyCenters = array();
		while ( list($countSupplyCenters, $countryID) = $DB->tabl_row($tabl) )
		{
			$this->ByCountryID[$countryID]->supplyCenterNo = $countSupplyCenters;
		}

		// Put into member records
		foreach($this->ByCountryID as $countryID=>$Member)
		{
			$DB->sql_put("UPDATE wD_Members SET
					supplyCenterNo = ".$Member->supplyCenterNo.",
					unitNo = ".$Member->unitNo."
				WHERE gameID = ".$this->Game->id." AND countryID = ".$countryID);
		}
	}

	/**
	 * Returns true if any member has a different number of supply centers than units. Used to
	 * detect whether a builds phase is needed.
	 *
	 * @return boolean
	 */
	function checkForUnitSCDifference()
	{
		foreach($this->ByID as $Member)
			if( $Member->supplyCenterNo != $Member->unitNo )
				return true;

		return false;
	}

	/**
	 * Set players who have missed too many phases to be Left (which doesn't mean they get their
	 * points, they can still rejoin.
	 *
	 * @return boolean True if one or more have just left, false if no-one has just left
	 */
	function findSetLeft()
	{
		$left=false;

		// Eliminate players who've left
		foreach($this->ByStatus['Playing'] as $Member)
		{
			assert('$Member->missedPhases >= 0 and $Member->missedPhases <= 2');

			if($Member->missedPhases == 2)
			{
				$left=true;
				$Member->setLeft();
			}
		}

		return $left;
	}

	/**
	 * Set players who have lost all their SCs and units to be defeated, which will also
	 * return their points if needed.
	 *
	 * @return boolean True if someone has just been set to defeated, false otherwise
	 */
	function findSetDefeated()
	{
		$defeated=false;

		// Eliminate defeated players
		foreach($this->ByID as $Member)
		{
			/*
			 * Players who have 'Left' can be set to 'Defeated', but 'Defeated' players
			 * cannot become 'Left'
			 */
			if( $Member->status != 'Left' &&  $Member->status != 'Playing' ) continue;

			// The player is defeated if they have no supply centers or units
			if( 0 == $Member->supplyCenterNo and 0 == $Member->unitNo )
			{
				$defeated=true;
				$Member->setDefeated($this->Game->Scoring->pointsForDefeat($Member));
			}
		}

		return $defeated;
	}

	/**
	 * Check to see if there's only one player left, or one player has the winning number of supply centers. If so
	 * the game is declared game-over and points are distributed
	 *
	 * @return processMember The winning Member, or false if no winner
	 */
	function checkForWinner()
	{
		global $DB;
		
		/*
		 * See if only one person is left over
		 * If more than one is left over see if any of them have the winning number of more supply centers
		 */

		/*
		 * Dealing with refunds for players under 100 points:
		 * Defeated players immidiately get all their points back
		 * Players that have left don't get their points back, since they may come back yet
		 * Once the game is over the players which have left need to be given their refund, if
		 * they need one
		 */

		$countPlaying = count($this->ByStatus['Playing']);

		// Is the game over? Is there only 1/0 players left?
		if ( $countPlaying < 2 )
		{
			/*
			 * Defeated players have already been reimbursed, but players that
			 * Left but were not defeated have not been, so they may be under 100
			 * points but not have been refunded.
			 */

			if ( $countPlaying == 1 )
			{
				/*
				 * The winner isn't set to a winner yet, this would cause difficulties
				 * while counting the active SCs and seeing who is still playing. All
				 * member status changes occur within Members->pointsDistributePot()
				 */
				foreach($this->ByStatus['Playing'] as $Member);
				return $Member;
			}
			elseif ( $countPlaying == 0 )
				$this->Game->setAbandoned(); // Throws exception
		}
		else
		{
			// If there is a diplpmacy-phase check for a retreating-phase and wait another round for the retreats to finish.
			if ($this->Game->phase == 'Diplomacy')
			{
				list($retreating) = $DB->sql_row("SELECT COUNT(retreatingUnitID) FROM wD_TerrStatus WHERE gameID=".$this->Game->id);
				if($retreating)
					return false;
			}
			
			// If more than one is left over see if any of them have supplyCenterTarget or more supply centers
			foreach($this->ByStatus['Playing'] as $Member)
			{
				if ( $this->Game->Variant->supplyCenterTarget <= $Member->supplyCenterNo )
					return $Member;
				// The players which have lost go into 'Survived' mode when the other player is set to Won
			}
		}

		return false;
	}

	/**
	 * Set members to drawn, giving points to those still around and supplements to those who had left
	 */
	function setDrawn()
	{
		$this->prepareLog();
		assert('count($this->ByStatus[\'Playing\']) > 0');

		// Calculate the points each player gets.
		// These are pre-calculated because if they aren't the pot has to be decreased, and active
		// supply-centers recalculated as each member gets their winnings. This was the final pot of the
		// game can be preserved in the game record
        $points = array();

		foreach($this->ByStatus['Playing'] as $Member)
			$points[$Member->countryID] = $this->Game->Scoring->pointsForDraw($Member);

		foreach($this->ByStatus['Left'] as $Member)
			$Member->setResigned( );

		foreach($this->ByStatus['Playing'] as $Member)
			$Member->setDrawn( $points[$Member->countryID] );
		$this->writeLog();
	}

	/**
	 * Set members to cancelled due to not enough players, giving all their bets back
	 */
	function setNotEnoughPlayers()
	{
		$this->prepareLog();
		/*
		 * Not-enough-player games are completely removed like
		 * mid-game-abandoned games, except bets are given back
		 */

		foreach($this->ByID as $Member)
		{
			// All members must be playing, since we're pre-game
			$Member->setNotEnoughPlayers();
		}
		$this->writeLog();
	}

	/**
	 * Notify members still playing/left that the game has been abandoned, and give
	 * supplements where needed.
	 */
	function setAbandoned()
	{
		$this->prepareLog();
		/*
		 * Abandoned games are completely removed, but first reimbursements
		 * need to be given to users which left. (They haven't got their
		 * reimbursements yet because they may have rejoined the game up
		 * until now)
		 */

		foreach($this->ByID as $Member)
		{
			// Ignore defeated players
			if($Member->status!='Left'&&$Member->status!='Playing')
				continue;

			$Member->setAbandoned();
		}
		$this->writeLog();
	}

	/**
	 * Set members to cancelled giving their bets back
	 */
	function setCancelled()
	{
		$this->prepareLog();
		foreach($this->ByID as $Member)
		{
			$Member->setCancelled(  );

			//TODO: Technically this should look up CD players which were taken over and repay
			// their bet too, but such a scenario will be very rare
		}
		$this->writeLog();
	}

	private function pointsInfoLog()
	{
		$log=array('pot'=>$this->Game->pot);

		if( !is_array($this->ByCountryID) )
		foreach($this->ByID as $Member)
		{
			$mLog=$Member->pointsInfoLog();
			$mLog['countryID']=$Member->countryID;

			foreach($mLog as $name=>$value)
				$log[$Member->userID.'-'.$name]=$value;
		}

		return $log;
	}

	private $logBefore;
	private function prepareLog()
	{
		if( !isset(Config::$pointsLogFile) || !Config::$pointsLogFile )
			return;

		$this->logBefore = $this->pointsInfoLog();
	}

	private function writeLog()
	{
		if( !isset(Config::$pointsLogFile) || !Config::$pointsLogFile )
			return;

		assert('is_array($this->logBefore);');

		$before=$this->logBefore;
		$after=$this->pointsInfoLog();
		$log=array('gameID'=>$this->Game->id);

		foreach($before as $name=>$value)
			$log[$name]=array('before'=>$value,'after'=>$after[$name]);

		if( !file_put_contents(Config::$pointsLogFile, libTime::text().":\n".print_r($log,true)."\n-----\n\n", FILE_APPEND) )
			trigger_error("Couldn't write points log to log file");
	}

	/**
	 * Set this member to have won. Calculates the share of the pot everyone gets and sets them all as
	 * Won, Survived, or Resigned, giving points accordingly.
	 *
	 * @param Member $Winner The processMember which won
	 */
	function setWon(Member $Winner)
	{
		$this->prepareLog();

		// Calculate the points each player gets.
		// These are pre-calculated because if they aren't the pot has to be decreased, and active
		// supply-centers recalculated as each member gets their winnings. This was the final pot of the
		// game can be preserved in the game record
        $points = array();

		foreach($this->ByStatus['Left'] as $Member)
			$points[$Member->countryID] = $this->Game->Scoring->pointsForSurvive($Member);
		foreach($this->ByStatus['Playing'] as $Member)
				$points[$Member->countryID] = $this->Game->Scoring->pointsForSurvive($Member);
        $points[$Winner->countryID] = $this->Game->Scoring->pointsForWin($Winner);

		foreach($points as $countryID=>$pointsWon)
		{
			$Member = $this->ByCountryID[$countryID];

			// Now the actual status is set 'Playing'->'Survived'/'Won', 'Left'->'Resigned'
			if($Member->id == $Winner->id)
				$Winner->setWon($pointsWon);
			elseif($Member->status == 'Playing')
				$Member->setSurvived($pointsWon);
			elseif($Member->status == 'Left')
				$Member->setResigned();
			else
				trigger_error(l_t("Invalid member status type for points distribution."));
		}

		$this->writeLog();

		// Members, messages, points all sent and finished
	}

	/**
	 * Returns true if more than 1/3rd of active players are NMR. (No Moves Recieved)
	 *
	 * @return boolean
	 */
	function isOverNMRLimit()
	{
		$countPlaying=0;
		$countNMR=0;

		foreach($this->ByStatus['Playing'] as $MemberPlaying)
		{
			$countPlaying++;

			if( !( $MemberPlaying->orderStatus->Saved || $MemberPlaying->None ) )
				$countNMR++;
		}

		if( ($countNMR/$countPlaying) >= (1/3) )
			return true;
		else
			return false;
	}
	

	/**
	 * Allow the user to join a game. The User must have enough points, the Game must be
	 * locked for UPDATE, if the game is private a password must be supplied, if there is
	 * a civil disorder player the member ID of the civil disorder member must be
	 * supplied, there mustn't be too many users in the game, the user mustn't join the
	 * same game twice, etc, etc.
	 *
	 * If successful the user is redirected to the game they just joined. If unsuccessful
	 * an exception is thrown.
	 *
	 * @param string[optional] $password The optional password supplied to enter the game
	 * @param string[optional] $countryID The countryID to be taken (filtered)
	 */
	function join($password="", $countryID=-1)
	{
		global $DB, $User;

		$countryID=(int)$countryID;

		// If we're not locked for UPDATE we can't keep things consistant
		assert('$this->Game->lockMode == UPDATE');

		if ( $this->Game->private and md5($password) != $this->Game->password and $password != $this->Game->password )
			throw new Exception(l_t("The password you supplied is incorrect, please try again."));

		if ( !$this->Game->isJoinable() )
			throw new Exception(l_t("You cannot join this game."));

		if ( !($this->Game->minimumReliabilityRating <= $User->reliabilityRating) )
			throw new Exception(l_t("Your Reliability Rating of %s%% is not high enough to join this game, which is restricted to %s%% RR and above.",
				$User->reliabilityRating, $this->Game->minimumReliabilityRating));

		// We can join, the only question is how?

		if ( $this->Game->phase == 'Pre-game' )
		{
			// Creates the Member record, the member object, and records the bet
			processMember::create($User->id, $this->Game->minimumBet);

			$M = $this->ByUserID[$User->id];
			if ($this->Game->isMemberInfoHidden() )
				$this->sendExcept($M,'No', l_t('Someone has joined the game.'));
			else
				$this->sendExcept($M,'No',l_t('%s has joined the game.',$User->username));
			$M->send('No','No',l_t('You have joined! Good luck'));

			if( count($this->ByUserID) == count($this->Game->Variant->countries) )
			{
				// Ready to start
				$this->Game->resetMinimumBet();
			}
		}
		else
		{
			// Taking over from CD: Valid countryID to take over? Got enough points?
			if ( 0>=$countryID || count($this->Game->Variant->countries)<$countryID )
				throw new Exception(l_t("You haven't specified which countryID you want to take over."));

			$CD = $this->ByCountryID[$countryID];

			if ( $CD->status != 'Left' )
				throw new Exception(l_t('The player selected is not in civil disorder.'));

			$bet = $CD->pointsValueInTakeover();
			if ( $User->points < $bet )
				throw new Exception(l_t("You do not have enough points to take over that countryID."));

			$CD->setTakenOver(); // Refund its points if required, and send it a message

			// Start updating the member record and object
			list($orderCount) = $DB->sql_row("SELECT COUNT(id) FROM wD_Orders
					WHERE gameID = ".$CD->gameID."
						AND countryID = ".$CD->countryID);

			$DB->sql_put("UPDATE wD_Members
					SET userID = ".$User->id.", status='Playing', orderStatus=REPLACE(orderStatus,'Ready',''),
						missedPhases = 0, timeLoggedIn = ".time()."
					WHERE id = ".$CD->id);
			$DB->sql_put('DELETE FROM wD_WatchedGames WHERE userID='.$User->id. ' AND gameID='.$this->Game->id);		

			unset($this->ByUserID[$CD->userID]);
			unset($this->ByStatus['Left'][$CD->id]);

			$CD->userID = $User->id;
			$CD->status = 'Playing';
			$CD->missedPhases = 0;
			$CD->orderStatus->Ready=false;
			$CD->points = $User->points;

			$this->ByUserID[$CD->userID] = $CD;
			$this->ByStatus['Playing'][$CD->id] = $CD;

			$CD->makeBet($bet);
			$this->Game->resetMinimumBet();

			$CDCountryName=$this->Game->Variant->countries[$CD->countryID-1];

			if ( $this->Game->isMemberInfoHidden() )
				$this->sendExcept($CD,'No',l_t('Someone has taken over %s.',$CDCountryName));
			else
				$this->sendExcept($CD,'No',l_t('%s has taken over %s.',$User->username,$CDCountryName));
			$CD->send('No','No',l_t('You took over %s! Good luck',$CDCountryName));
		}

		$this->Game->gamelog(l_t('New member joined'));

		$this->joinedRedirect();
	}

	/**
	 * Redirect to a game after joining it. Script ends here.
	 */
	function joinedRedirect()
	{
		// We have successfully joined, now give a message to tell the user so
		header('refresh: 4; url=board.php?gameID='.$this->Game->id);

		$message = '<p class="notice">'.l_t('You are being redirected to %s. Good luck!','<a href="board.php?gameID='.$this->Game->id.'">'.$this->Game->name.'</a>').'</p>';

		libHTML::notice(l_t("Joined %s",$this->Game->name), $message);
	}

	/**
	 * Updates the reliability stats for the users in this game.
	 */
	function updateReliabilityStats()
	{
		global $DB;
 		require_once(l_r('gamemaster/gamemaster.php'));      	
		$DB->sql_put(libGameMaster::RELIABILITY_QUERY . "WHERE u.id IN (".implode(",",array_keys($this->ByUserID)) . ')');
	}

	function processSummary()
	{
		$a=array(
			'votesPassed'=>implode(',',$this->votesPassed()),
			'ready'=>($this->isReady()?'true':'false'),
			'members'=>array()
		);

		foreach($this->ByID as $Member)
			$a['members'][] = $Member->processStatus();

		return $a;
	}
}
?>
