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
 * Handles the functions which a user wants to perform when they view a game they're a member of;
 * logging on, finalizing, etc. Also loads orders ready to be updated.
 *
 * @package Board
 */
class userMember extends panelMember
{
	/**
	 * Load a Member object into a userMember object, load the orders, and lock them all for UPDATE
	 *
	 * @param Member $Member The Member object containing variables to build a userMember from
	 */
	public function __construct(Member $Member)
	{
		global $DB, $Game;

		parent::__construct($Member);

		$commit=true;

		if ( $this->status == 'Left' )
		{
			$this->setBackFromLeft();
		}
		elseif( (time() - $this->timeLoggedIn) > 3*60)
		{
			$DB->sql_put("UPDATE wD_Members SET timeLoggedIn = ".time()." WHERE id = ".$this->id);
			$this->timeLoggedIn=time();
			$this->missedPhases=0;
		}
		else
			$commit=false;

		if( $commit )
			$DB->sql_put("COMMIT");
	}

	/**
	 * Set that this user is no longer in civil disorder for this membership. Sets as playing, removes
	 * civildisorder record, sets their orderStatus depending on whether they
	 * have orderes to enter, puts them into the correct Game->Members->ByStatus list.
	 */
	protected function setBackFromLeft()
	{
		global $DB,$Game,$User;
		
		if ( $this->Game->Members->isTempBanned() )
		{
			throw new Exception("You are blocked from rejoining your games.");
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

		$DB->sql_put(
				"UPDATE wD_Members
				SET status = 'Playing', ".( $this->orderStatus->updated ? "orderStatus='".$this->orderStatus."', " : '' )."
					timeLoggedIn = ".time()."
				WHERE id = ".$this->id
			);
	}

	/**
	 * Toggle the value of a member's vote, e.g. Pause or Draw. Sets/unsets it in the database and in $this->votes[]
	 * Also detects if a vote has passed and will schedule it for processing if so.
	 *
	 * Will usually be called from board.php, with the vote form buttons provided in the gamepanel
	 *
	 * @param string $voteName The vote which is being toggled
	 */
	public function toggleVote($voteName)
	{
		global $DB;

		// Unpause is stored as Pause in the database
		if ( $voteName == 'Unpause' )
			$voteName = 'Pause';

		if(!in_array($voteName, Members::$votes))
			throw new Exception(l_t("Invalid vote"));

		if(in_array($voteName, $this->votes))
			unset($this->votes[array_search($voteName, $this->votes)]);
		else
			$this->votes[] = $voteName;

		$DB->sql_put("UPDATE wD_Members SET votes='".implode(',',$this->votes)."' WHERE id=".$this->id);
	}

	/**
	 * Register that you have viewed the messages from a certain countryID and
	 * no longer need notification of them
	 *
	 * @param string $seenCountryID The countryID who's messages were read
	 */
	public function seen($seenCountryID)
	{
		global $DB;

		foreach($this->newMessagesFrom as $i => $countryID)
			if ( $countryID == $seenCountryID )
			{
				unset($this->newMessagesFrom[$i]);
				break;
			}

		$DB->sql_put("UPDATE wD_Members
						SET newMessagesFrom = '".implode(',',$this->newMessagesFrom)."'
						WHERE id = ".$this->id);
	}
}
?>
