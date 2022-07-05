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
			$this->markBackFromLeft();
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
	 * Toggle the value of a member's vote, e.g. Pause or Draw. Sets/unsets it in the database and in $this->votes[]
	 * Also detects if a vote has passed and will schedule it for processing if so.
	 *
	 * Will usually be called from board.php, with the vote form buttons provided in the gamepanel
	 *
	 * @param string $voteName The vote which is being toggled
	 */
	public function toggleVote($voteName)
	{
		global $DB,$User;

		// Unpause is stored as Pause in the database
		if ( $voteName == 'Unpause' )
			$voteName = 'Pause';

		if(!in_array($voteName, Members::$votes))
			throw new Exception(l_t("Invalid vote"));

		$voteOn = in_array($voteName, $this->votes);
		if($voteOn)
			unset($this->votes[array_search($voteName, $this->votes)]);
		else
			$this->votes[] = $voteName;

		// Keep a log that a vote was set in the game messages, so the vote time is recorded
		require_once(l_r('lib/gamemessage.php'));
		
		if( $this->Game->playerTypes=='MemberVsBots' && !$User->type['Bot'] && in_array($voteName, array('Pause','Cancel')) )
		{
			libGameMessage::send($this->countryID, $this->countryID, ($voteOn?'Un-':'').'Voted for '.$voteName, $this->gameID);
			// If it's a member vs bots game allow the member to pause or cancel the game
			if( $voteOn )
				$DB->sql_put("UPDATE wD_Members SET votes=REPLACE(votes,'".$voteName."','') WHERE gameID=".$this->gameID);
			else
				$DB->sql_put("UPDATE wD_Members SET votes=CONCAT(COALESCE(CONCAT(votes,','),''),'".$voteName."') WHERE gameID=".$this->gameID);
		}
		else
		{
			libGameMessage::send($this->countryID, $this->countryID, ($voteOn?'Un-':'').'Voted for '.$voteName, $this->gameID);
			$DB->sql_put("UPDATE wD_Members SET votes='".implode(',',$this->votes)."' WHERE id=".$this->id);
		}
	}

	/**
	 * Register that you have viewed the messages from a certain countryID and
	 * no longer need notification of them
	 *da
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
