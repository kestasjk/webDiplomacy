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

require_once(l_r('objects/basic/set.php'));

/**
 * An object representing a relationship between a user and a game. Mostly contains
 * information used for printing the Game->summary(), when not loaded as userMember or
 * processMember
 *
 * @package Base
 * @subpackage Game
 */
class Member
{
	/**
	 * The member ID
	 * @var int
	 */
	var $id;

	/**
	 * The user ID
	 * @var int
	 */
	var $userID;

	/**
	 * The game ID
	 * @var int
	 */
	var $gameID;

	/**
	 * The countryID this member is playing as.
	 * @var int
	 */
	var $countryID;

	/**
	 * The country this member is playing as. Will be 'Unassigned' if pre-game.
	 * @var string
	 */
	var $country;

	/**
	 * The member status; 'Playing','Left','Defeated'
	 * @var string
	 */
	var $status;

	/**
	 * The username corresponding to this member
	 * @var string
	 */
	var $username;

	/**
	 * The number of points the user currently has available to bet
	 * @var int
	 */
	var $points;

	/**
	 * The amount the user bet into the game
	 * @var int
	 */
	var $bet;
	
	/**
	 * An array of countries from which this member has new messages. 'Global' may
	 * also be within this array.
	 *
	 * @var string[]
	 */
	var $newMessagesFrom;

	/**
	 * The time the player last logged into the game
	 *
	 * @var int
	 */
	var $timeLoggedIn;

	/**
	 * A link to the Game object this Member is a member of
	 *
	 * @var Game
	 */
	var $Game;

	/**
	 * The number of phases this Member has missed in a row
	 *
	 * @var int
	 */
	var $missedPhases;

	/**
	 * The number of excused misses the member has left
	 */
	var $excusedMissedTurns;

	/**
	 * The number of units this member owns
	 *
	 * @var int
	 */
	var $unitNo;

	/**
	 * The number of supply centers this member owns
	 * @var int
	 */
	var $supplyCenterNo;

	/**
	 * Whether this member is online or not
	 * @var bool
	 */
	var $online;

	/**
	 * An array of vote-flags which this member has voted for
	 *
	 * @var string[]
	 */
	var $votes;

	var $pointsWon;

	/**
	 * An array of the order status flags currently set: 'None','Saved','Completed','Ready'
	 *
	 * @var string[]
	 */
	var $orderStatus;

	/**
	 * A comma delimited list of the user's access permissions (used to determine what kind of donator the user is)
	 *
	 * @var string
	 */
	var $userType;

	/**
	 * 0 if the user wants to receive notifications, 1 if they don't
	 *
	 * @var int
	 */
	var $hideNotifications;

	/**
	 * Create a Member object from a database Member record row
	 * @param array $row Member record
	 */
	public function __construct($row)
	{
		foreach ( $row as $name => $value )
		{
			$this->{$name} = $value;
		}

		if( $this->countryID==0 )
			$this->country='Unassigned';
		else
			$this->country = $this->Game->Variant->countries[$this->countryID-1];

		// If making a userMember the $row is a userMember object not an array, and these operations have already been performed
		if ( ! $row instanceof Member )
		{
			if( strlen($this->votes ?? '') )
				$this->votes = explode(',', $this->votes);
			else
				$this->votes=array();

			if( strlen($this->newMessagesFrom ?? '') )
				$this->newMessagesFrom = explode(',', $this->newMessagesFrom);
			else
				$this->newMessagesFrom = array();

			$this->orderStatus=new setMemberOrderStatus($this->orderStatus);

			$this->online = (bool)$this->online;
		}
	}

	/**
	 * Generate a profile link
	 * @return string
	 */
	function profile_link()
	{
		if ( $this->Game->phase == 'Pre-game' )
		{
			$output = '<a href="userprofile.php?userID='.$this->userID.'">'.$this->username;
		}
		else
		{
			$output = '<a class="country'.$this->countryID.'" ';

			if ($this->status == 'Defeated')
			{
				$output .= 'style="text-decoration: line-through" ';
			}

			$output .= 'href="userprofile.php?userID='.$this->userID.'">'.$this->username;
		}
		return $output.' ('.$this->points.User::typeIcon($this->userType).')</a>';
	}

	/**
	 * CD takeovers cost 0. This is a function because they weren't always free, and keeping the function means we can always change it later.
	 * @return int
	 */
	function pointsValueInTakeover() 
	{
		return 0;
	}	

	/**
	 * A textual display of this user's last log-in time
	 * @return string Last log-in time
	 */
	function lastLoggedInTxt()
	{
		return libTime::timeLengthText(time()-$this->timeLoggedIn).' ('.libTime::text($this->timeLoggedIn).')';
	}
	/**
	 * A textual display of this user's last log-in time, for when the exact time shouldn't be shown as it's anonymous
	 * @return string Very rough last login time
	 */
	function lastLoggedInTxt_Anon()
	{
		$daysSinceLoggedIn = (time()-$this->timeLoggedIn) / (24*60*60);
		if( $daysSinceLoggedIn < 1 )
			return '< 1 day';
		else if ( $daysSinceLoggedIn < 7 )
			return '< 1 week';
		else
			return 'over a week';
	}

	function send($keep, $private, $text, $fromCountryID=null)
	{
		notice::send(
			$this->userID, $this->gameID, 'Game',
			$keep, $private, $text, $this->Game->name, $this->gameID);
	}

	/**
	 * Set that this user is no longer in civil disorder for this membership. Sets as playing, removes
	 * civildisorder record, sets their orderStatus depending on whether they
	 * have orderes to enter, puts them into the correct Game->Members->ByStatus list.
	 */
	function markBackFromLeft()
	{
		global $DB,$Game,$User;
		
		if( !$this->status == 'Left' )
		{
			throw new Exception("Unnecessary call to markBackFromLeft, member is ".$this->status.". These calls lock the database so should be avoided.");
		}
		
		if ( $this->Game->Members->isTempBanned() )
		{
			throw new Exception("You are blocked from rejoining your games.");
		}
		
		if ( $User->reliabilityRating < $this->Game->minimumReliabilityRating )
		{
			throw new Exception("Your reliability rating is too low to rejoin this game.");
		}

		unset($this->Game->Members->ByStatus[$this->status][$this->id]);
		$this->status = 'Playing';
		$this->Game->Members->ByStatus[$this->status][$this->id] = $this;

		/*
		 * Remove the CD mark from this person's record
		 * Someone could possible go into CD, be taken over, join another country, go CD again, then rejoin, so country has to be specified
		 */
		 // Was this a mod forced CD?
		$DB->sql_tabl("SELECT * FROM wD_CivilDisorders
					WHERE forcedByMod=0 
					AND gameID = ".$this->gameID."
					AND userID = ".$this->userID."
					AND countryID = ".$this->countryID);

		if ($DB->affected() != 0) 
		{
            $DB->sql_put("UPDATE wD_Users SET deletedCDs = deletedCDs + 1 where id=" .$this->userID);
		}
		 
		$DB->sql_put("DELETE FROM wD_CivilDisorders
					WHERE gameID = ".$this->gameID."
					AND userID = ".$this->userID."
					AND countryID = ".$this->countryID
				);
				
		$this->orderStatus->Ready=false;

		error_log("updating wD_Members ".$this->id);
		$DB->sql_put(
				"UPDATE wD_Members
				SET status = 'Playing', ".( $this->orderStatus->updated ? "orderStatus='".$this->orderStatus."', " : '' )."
					timeLoggedIn = ".time()."
				WHERE id = ".$this->id
			);
		
		// Reset the min bet so that the game no longer appears in open games searches. 
		require_once(l_r('gamemaster/game.php'));
		$Variant=libVariant::loadFromGameID($this->gameID);
		$ProcessGame = $Variant->processGame($this->gameID);
		$ProcessGame->resetMinimumBet();
	}
}
?>
