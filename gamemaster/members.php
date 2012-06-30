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

require_once('gamemaster/member.php');

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
		$this->sendToPlaying('No',"Game progressed to ".$this->Game->phase.", ".$this->Game->datetxt($this->Game->turn));
	}

	/**
	 * Send message about the game being paused
	 */
	function notifyPaused()
	{
		$this->sendToPlaying('No',"Game has been paused.");
	}

	/**
	 * Send message about the game being unpaused
	 */
	function notifyUnpaused()
	{
		$this->sendToPlaying('No',"Game has been unpaused.");
	}

	/**
	 * Send message about the game phase being extended
	 */
	function notifyExtended()
	{
		require_once "lib/gamemessage.php";
		$msg= "Per 2/3 majority vote the gamephase got extended by 4 days.\n(Voters: ";
		foreach($this->ByStatus['Playing'] as $Member)
			if (in_array('Extend',$Member->votes))
				$msg.= $Member->country . ' / ';
		$msg=rtrim($msg,' /') . ")"; 
		libGameMessage::send(0, 'GameMaster', $msg , $this->Game->id);		
		$this->sendToPlaying('No',"The gamephase got extended by 4 days.");
	}
	
	/**
	 * Clear all extend votes from each Member for the next phase
	 */
	function clearExtendVotes()
	{
		global $DB;
		$extVoteSet=false;
		foreach($this->ByStatus['Playing'] as $Member)
		{
			if (in_array('Extend',$Member->votes))
			{
				$extVoteSet=true;
				unset($Member->votes[array_search('Extend', $Member->votes)]);
				$DB->sql_put("UPDATE wD_Members SET votes='".implode(',',$Member->votes)."' WHERE id=".$Member->id);	
			}
		}
		if ($extVoteSet)
		{
			require_once "lib/gamemessage.php";
			libGameMessage::send(0, 'GameMaster', 'Extend-request didn\'t reach 2/3 majority. All extend-votes cleared.' , $this->Game->id);
		}
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
						WHERE t.supply='Yes' AND ( NOT ts.countryID = 'Neutral' )
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

			switch($Member->missedPhases)
			{
				case 1:
					/*
					 * Players can be set to civil disorder with only one missed
					 * phase if it looks like they're about to be defeated
					 */
					if( 1 < $Member->supplyCenterNo or 1 < $Member->unitNo )
						break;
				case 2:
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
				$Member->setDefeated();
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
			// If more than one is left over see if any of them have supplyCenterTarget or more supply centers
			foreach($this->ByStatus['Playing'] as $Member)
			{
				if ( $this->Game->targetSCs > 0 )
				{
					if ( $this->Game->targetSCs <= $Member->supplyCenterNo )
					{
						return $this->check_for_Winner_that_works_with_same_SC_count();
					}
				}
				elseif ( $this->Game->Variant->supplyCenterTarget <= $Member->supplyCenterNo )
				{
					return $this->check_for_Winner_that_works_with_same_SC_count();
				}
				// The players which have lost go into 'Survived' mode when the other player is set to Won
			}
		}
		
		// Do an additional check if we reached maxTurns:
		if (($this->Game->turn == ($this->Game->maxTurns - 1)) && ($this->Game->maxTurns > 0))
			return $this->check_for_Winner_that_works_with_same_SC_count();
		
		return false;
	}

	/**
	 * Set members to drawn, giving points to those still around and supplements to those who had left
	 */
	function setDrawn()
	{
		$this->prepareLog();
		assert('count($this->ByStatus[\'Playing\']) > 0');

		$winnings = round($this->Game->pot / count($this->ByStatus['Playing']));

		foreach($this->ByStatus['Left'] as $Member)
			$Member->setResigned( );

		foreach($this->ByStatus['Playing'] as $Member)
			$Member->setDrawn( $winnings );
		$this->writeLog();
	}
	
	/**
	 * Set all but one members to defeated. 
	 */
	function setConcede()
	{
		$this->prepareLog();
		assert('count($this->ByStatus[\'Playing\']) > 0');

		foreach($this->ByStatus['Left'] as $Member)
			$Member->setResigned();

		foreach($this->ByStatus['Playing'] as $Member)
		{
			if (in_array('Concede',$Member->votes))
				$Member->setDefeated();
		}
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

		// Calculate the percent of the pot each player gets, based on SCs and the type of game (WTA/PPSC),
		// and whether they're 'Left'
		$potShareRatios = $this->potShareRatios($Winner);

		// These are pre-calculated because if they aren't the pot has to be decreased, and active
		// supply-centers recalculated as each member gets their winnings. This was the final pot of the
		// game can be preserved in the game record
		foreach($potShareRatios as $countryID=>$ratio)
		{
			$Member = $this->ByCountryID[$countryID];

			// Only Left/Playing countries will be included here
			// status=Left -> shareRatio=0
			$pointsWon = ceil($ratio * $this->Game->pot);

			// $pointsPaid is given to the set* functions, so that a message about actual points returned
			// is given (including supplement), rather than only the number of points won

			// Now the actual status is set 'Playing'->'Survived'/'Won', 'Left'->'Resigned'
			if($Member->id == $Winner->id)
				$Winner->setWon($pointsWon);
			elseif($Member->status == 'Playing')
				$Member->setSurvived($pointsWon);
			elseif($Member->status == 'Left')
				$Member->setResigned();
			else
				trigger_error("Invalid member status type for points distribution.");
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
	 * Calculate the share of the pot everyone in this game will get, given that $Winner is the
	 * winner, return as a countryID indexed array of share ratios.
	 *
	 * @param Member $Winner The winner
	 * @return array $ratios[$countryID]=$shareOfThePotDue;
	 */
	private function potShareRatios(Member $Winner)
	{
		$ratios=array();

		// We need a number for all 'Playing' or 'Left' countries, even a 0.0 may trigger required supplement points
		foreach($this->ByStatus['Left'] as $Member)
			$ratios[$Member->countryID] = 0.0;
		foreach($this->ByStatus['Playing'] as $Member)
			$ratios[$Member->countryID] = 0.0;

		if( $this->Game->potType == 'Winner-takes-all' )
		{
			// WTA; easy
			$ratios[$Winner->countryID] = 1.0;
		}
		else
		{
			/*
			 * PPSC; calculate based on active-player-owned supply-centers, but
			 * things are complicated because players with over $SCTarget SCs are limited
			 * to the winnings they would get from $SCTarget, and the remainder is
			 * distributed among the survivors according to their winnings.
			 */
			$SCsInPlayCount = (float)$this->supplyCenterCount('Playing');

			assert('$SCsInPlayCount > 0');

			$SCTarget = $this->Game->Variant->supplyCenterTarget;

			if( $Winner->supplyCenterNo > $SCTarget )
			{
				/*
				 * Winner is greedy and got more SCs than he needed:
				 * - Get the number of extra SCs he has
				 * - Reduce his total to $SCTarget
				 * - Subtract the extra amount from the total SCs so they scale down
				 */

				/*
				 * Subtracting the over-the-limit extra SCs from the winner and
				 * from the total SC count effectively makes the algorithm behave
				 * as if they didn't exist
				 */
				$SCsInPlayCount -= ( $Winner->supplyCenterNo - $SCTarget );
				$ratios[$Winner->countryID] = $SCTarget/$SCsInPlayCount;
			}
			else
				$ratios[$Winner->countryID] = $Winner->supplyCenterNo/$SCsInPlayCount;

			foreach($this->ByStatus['Playing'] as $Member)
			{
				if( $Member->id == $Winner->id ) continue;

				$ratios[$Member->countryID] = $Member->supplyCenterNo/$SCsInPlayCount;
			}
		}

		return $ratios;
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
			throw new Exception("The password you supplied is incorrect, please try again.");

		if ( !$this->Game->isJoinable() )
			throw new Exception("You cannot join this game.");

		// Check for additional requirements:
		if ( $this->Game->minPhases > $User->phasesPlayed)
			throw new Exception("You did not play enough phases to join this game. (Required:".$this->Game->minPhases." / You:".$User->phasesPlayed.")");
		if ( $this->Game->minRating > $User->getReliability())
			throw new Exception("You reliable-rating is too low to join this game. (Required:".$this->Game->minRating."% / You:".$User->getReliability()."%)");
		if ( $this->Game->maxLeft < $User->gamesLeft )
			throw new Exception("You went CD in too many games. (Required: not more than ".$this->Game->maxLeft." / You:".$User->gamesLeft.")");

		// Handle RL-relations
		require_once ("lib/relations.php");			
		if ($message = libRelations::checkRelationsGame($User, $this->Game))
			throw new Exception($message);

		// Check for reliability-rating:
		if ( count($this->Game->Variant->countries)>2 && $this->Game->phase == 'Pre-game' && $message = $User->isReliable())
			libHTML::notice('Reliable rating not high enough', $message);

		// Check if there is a mute against a player
		list($muted) = $DB->sql_row("SELECT count(*) FROM wD_Members AS m
									LEFT JOIN wD_BlockUser AS f ON ( m.userID = f.userID )
									LEFT JOIN wD_BlockUser AS t ON ( m.userID = t.blockUserID )
								WHERE m.gameID = ".$this->Game->id." AND (f.blockUserID =".$User->id." OR t.userID =".$User->id.")");
		if ($muted > 0)
		{
			throw new Exception("You can't join. A player in this game has you muted or you muted a player in this game");
		}
				
		// We can join, the only question is how?

		if ( $this->Game->phase == 'Pre-game' )
		{
			// Creates the Member record, the member object, and records the bet
			if( $countryID!=-1 )
			{
				if (isset($this->ByCountryID[$countryID]))
					throw new Exception("You cannot join this game as ".$this->Game->Variant->countries[$countryID -1]." someone else was faster.");
				processMember::create($User->id, $this->Game->minimumBet,$countryID);
			}
			else
				processMember::create($User->id, $this->Game->minimumBet);

			$M = $this->ByUserID[$User->id];
			if ($this->Game->isMemberInfoHidden() )
				$this->sendExcept($M,'No', 'Someone has joined the game.');
			else
				$this->sendExcept($M,'No',$User->username.' has joined the game.');
			$M->send('No','No','You have joined! Good luck');

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
				throw new Exception("You haven't specified which countryID you want to take over.");

			$CD = $this->ByCountryID[$countryID];

			if ( $CD->status != 'Left' )
				throw new Exception('The player selected is not in civil disorder.');

			$bet = $CD->pointsValue();
			if ( $User->points < $bet )
				throw new Exception("You do not have enough points to take over that countryID.");

			$CD->setTakenOver(); // Refund its points if required, and send it a message

			// Start updating the member record and object
			list($orderCount) = $DB->sql_row("SELECT COUNT(id) FROM wD_Orders
					WHERE gameID = ".$CD->gameID."
						AND countryID = ".$CD->countryID);

			$DB->sql_put("UPDATE wD_Members
					SET userID = ".$User->id.", status='Playing', orderStatus=REPLACE(orderStatus,'Ready',''),
						missedPhases = 0, timeLoggedIn = ".time()."
					WHERE id = ".$CD->id);

			unset($this->ByUserID[$CD->userID]);
			unset($this->ByStatus['Left'][$CD->id]);

			$playerLeftID=$CD->userID;
			$CD->userID = $User->id;
			$CD->status = 'Playing';
			$CD->missedPhases = 0;
			$CD->orderStatus->Ready=false;
			$CD->points = $User->points;
			$CD->missedMoves = $User->missedMoves;
			$CD->phasesPlayed = $User->phasesPlayed;
			$CD->gamesLeft = $User->gamesLeft;

			if (($User->leftBalanced < $User->gamesLeft) && (count($this->Game->Variant->countries) > 2) && ($this->Game->phaseMinutes > 30) )
				$DB->sql_put("UPDATE wD_Users SET leftBalanced = leftBalanced +1 WHERE id=".$User->id);		
			
			$this->ByUserID[$CD->userID] = $CD;
			$this->ByStatus['Playing'][$CD->id] = $CD;

			$CD->makeBet($bet);
			$this->Game->resetMinimumBet();

			$CDCountryName=$this->Game->Variant->countries[$CD->countryID-1];

			if ( $this->Game->isMemberInfoHidden() )
			{
				require_once "lib/gamemessage.php";
				$msg = 'Someone has taken over '.$CDCountryName.' replacing "<a href="profile.php?userID='.$playerLeftID.'">'.$CD->username.'</a>". Reconsider your alliances.';
				libGameMessage::send(0, 'GameMaster', $msg , $this->Game->id);
				$this->sendExcept($CD,'No','Someone has taken over '.$CDCountryName.'.');
			}
			else
			{
				require_once "lib/gamemessage.php";
				$msg = $User->username.' has taken over '.$CDCountryName.' replacing "<a href="profile.php?userID='.$playerLeftID.'">'.$CD->username.'</a>". Reconsider your alliances.';
				libGameMessage::send(0, 'GameMaster', $msg, $this->Game->id);
				$this->sendExcept($CD,'No',$User->username.' has taken over '.$CDCountryName.'.');
			}
			$CD->send('No','No','You took over '.$CDCountryName.'! Good luck');
		}
			
		$this->Game->gamelog('New member joined');

		$this->joinedRedirect();
	}

	/**
	 * Redirect to a game after joining it. Script ends here.
	 */
	function joinedRedirect()
	{
		// We have successfully joined, now give a message to tell the user so
		header('refresh: 4; url=board.php?gameID='.$this->Game->id);

		$message = '<p class="notice">You are being redirected to <a href="board.php?gameID='.$this->Game->id.'">'.$this->Game->name.'</a>. Good luck!</p>';

		libHTML::notice("Joined ".$this->Game->name, $message);
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
	
	/**
	 * Check the previous turns if more than one players reach the target SCs at the same turn.
	 *
	 * @return processMember The winning Member.
	 */
	function check_for_Winner_that_works_with_same_SC_count()
	{
		$winners=array();
		$maxSC=0;
		foreach($this->ByStatus['Playing'] as $Member)
		{
			if ( $Member->supplyCenterNo > $maxSC )
			{
				$maxSC=$Member->supplyCenterNo;
				$winners=array();
			}	
			if ( (count($winners)==0) or ($Member->supplyCenterNo == $maxSC) )
				$winners[]=$Member->countryID;
		}
		if (count($winners) > 1)
		{
			global $DB;
			for ($turn=$this->Game->turn; $turn>-1; $turn--)
			{
				$sql='SELECT ts.countryID, COUNT(*) AS ct FROM wD_TerrStatusArchive ts 
						JOIN wD_Territories as t ON (t.id = ts.terrID AND t.mapID='.$this->Game->Variant->mapID.')
					WHERE t.supply="Yes" AND ts.turn='.$turn.' AND ts.gameID='.$this->Game->id.'
						AND ts.countryID IN ('.implode(', ', $winners).')
					GROUP BY ts.countryID 
					HAVING ct = (
						SELECT COUNT(*) AS ct2 FROM wD_TerrStatusArchive ts2
							JOIN wD_Territories as t2 ON (t2.id = ts2.terrID AND t2.mapID='.$this->Game->Variant->mapID.')
						WHERE t2.supply="Yes" AND ts2.turn='.$turn.' AND ts2.gameID='.$this->Game->id.'
							AND ts2.countryID IN ('.implode(', ', $winners).')
						GROUP BY ts2.countryID ORDER BY ct2 DESC LIMIT 1)';
				$tabl = $DB->sql_tabl($sql);
				$winners=array();
				while( list($countryID, $sc) = $DB->tabl_row($tabl) )
					$winners[]=$countryID;
				// Exit loop if only one winner is left...
				if (count($winners) == 1)
					$turn=0;
			}
		}
		// Still no winner found:
		if (count($winners) > 1)
			$winners[0]=$winners[rand(0,count($winners)-1)];
			
		return $this->ByCountryID[$winners[0]];		
	}
}
?>
