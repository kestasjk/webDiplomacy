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
 * This class deals with changing the status of a member of a certain game, including
 * the giving of winnings or refunds.
 *
 * @package GameMaster
 */
class processMember extends Member
{
	function pointsInfoLog()
	{
		return array(
				'points'=>$this->points, 'pointsWon'=>$this->pointsWon, 'bet'=>$this->bet,
				'unitNo'=>$this->unitNo, 'supplyCenterNo'=>$this->supplyCenterNo
			);
	}

	/**
	 * Leave the game before it has started, refunding points, deleting the member record,
	 * and possibly erasing the game.
	 */
	function leave()
	{
		global $DB, $User;

		$Game = $this->Game;

		$leftMessage=l_t("You've left <strong>%s</strong> before it started, and have been returned your bet of <strong>%s</strong>.",
			$Game->name,$Game->Members->ByUserID[$User->id]->bet.libHTML::points());

		$this->cancelBet();

		$DB->sql_put("DELETE FROM wD_Members WHERE id=".$this->id);

		if(count($Game->Members->ByUserID)==1)
		{
			// No-one else left in the game
			processGame::eraseGame($Game->id);
		}
		else
		{
			// Notify the remaining players
			$Game->Members->sendExcept($this,'No',l_t("<strong>%s</strong> left the game.",$this->username));
		}

		header('refresh: 4; url=index.php');
		$this->send('No','No', $leftMessage);
		libHTML::notice('Left game', $leftMessage);
	}

	/**
	 * Create a new member record, load it into the Members object, should only occur in a Pre-game setting.
	 *
	 * @param $userID The userID
	 * @param $bet The bet, will throw an exception if the user doesn't have enough
	 */
	static function create($userID, $bet)
	{
		global $DB, $Game;

		assert('$Game instanceof processGame');

		// It is assumed this is being run within a transaction

		$DB->sql_put("INSERT INTO wD_Members SET
			userID = ".$userID.", gameID = ".$Game->id.", orderStatus='None,Completed,Ready', bet = 0, timeLoggedIn = ".time());
                $DB->sql_put('DELETE FROM wD_WatchedGames WHERE gameID='.$Game->id.' AND userID='.$userID);

		$Game->Members->load();

		$Game->Members->ByUserID[$userID]->makeBet($bet);
	}

	/**
	 * Makes a user deposit a certain number of points into this game, updating the game's pot.
	 * Throws an exception if the user doesn't have enough points.
	 *
	 * Uses an UPDATE lock on the users column to prevent simultaneous bets
	 *
	 * @param int $bet The number of points the user is betting
	 * @param int $userID The user ID of the user betting the points
	 * @return int The amount bet
	 */
	function makeBet($bet)
	{
		if ( $bet > $this->points )
		{
			throw new Exception(l_t('You do not have enough points to join this game. You need to bet %s.',$bet.' '.libHTML::points()));
		}

		User::pointsTransfer($this->userID, 'Bet', $bet, $this->gameID, $this->id);

		$this->points -= $bet;
		$this->Game->pot += $bet;

		global $User;
		if($User instanceof User && $User->id == $this->userID)
			$User->points -= $bet;

		return $bet;
	}

	/**
	 * Returns the bet made by this member, updates the game's pot, removing all traces it was made
	 * @return int The amount returned
	 */
	function cancelBet()
	{
		global $DB;

		$bet = $this->bet;

		if ( $this->status != 'Playing' && $this->status != 'Left' )
		{
			list($supplementAmount) = $DB->sql_row(
				"SELECT points FROM wD_PointsTransactions WHERE type='Supplement'
				AND userID=".$this->userID." AND gameID=".$this->gameID." AND memberID=".$this->id
			);

			if( isset($supplementAmount) && $supplementAmount>0 )
				$bet -= $supplementAmount;

		}

		assert('$bet <= $this->Game->pot');

		User::pointsTransfer($this->userID, 'Cancel', $bet, $this->gameID, $this->id);

		$this->points += $bet;
		$this->Game->pot -= $bet;

		global $User;
		if($User instanceof User && $User->id == $this->userID)
			$User->points += $bet;

		return $bet;
	}

	/**
	 * Award this user some points, if they need a refund on their bet (they have under 100 total points
	 * and need to be given back the amount they bet up to having 100 total points). The maximum
	 * supplement value is the amount bet into this game.
	 *
	 * @return int The number of points awarded.
	 */
	function awardSupplement()
	{
		$this->pointsWon=0;
		$awardSupplement = User::pointsSupplement($this->userID, 0, $this->bet,$this->gameID, $this->points);
		$this->points += $awardSupplement;
		return $awardSupplement;
	}

	/**
	 * Add winnings to this member's user account. If the number of winnings is less than the amount bet
	 * the member may need to be refunded some points, also sends the user a message if they advance within
	 * the top 100 players.
	 *
	 * This is how the different status-setting functions break down regarding giving points:
	 * Given already: setWon, setResigned, setCancelled, setNotEnoughPlayers, setAbandoned, setSurvived
	 * Give within: setDrawn, setTakenOver
	 *
	 * @param int The number of points won
	 */
	function awardPoints($awardedPoints)
	{
		global $DB, $Game;

		// User::points* update the database, but not this object

		// Might we need to be topped up?
		if ( $awardedPoints < $this->bet )
			$supplement=User::pointsSupplement($this->userID, $awardedPoints, $this->bet,$this->gameID, $this->points);
		else
			$supplement=0;

		$this->pointsWon = $awardedPoints;

		if ( $awardedPoints == 0 )
			return $supplement; // Don't record 0 point transactions

		User::pointsTransfer($this->userID, 'Won', $awardedPoints, $this->gameID, $this->id);

		$pointsGiven = $supplement + $awardedPoints;
		$this->points += $pointsGiven;

		if ( $this->points > 100 )
		{
			list($oldPosition) = $DB->sql_row("SELECT COUNT(id)+1 FROM wD_Users WHERE points > ".($this->points-$pointsGiven));
			list($position) = $DB->sql_row("SELECT COUNT(id)+1 FROM wD_Users WHERE points > ".$this->points);

			if ( $position > $oldPosition )
				$this->send('No','No',l_t("Your winnings from this game moved your global ranking from #%s to #%s!",$oldPosition,$position));
		}

		return $pointsGiven;
	}

	/**
	 * Update the player's status, updating their Game->Members->ByStatus position. set[Status] is used
	 * to send messages.
	 *
	 * @param string $status 'Playing' 'Defeated' 'Left' 'Resigned' 'Survived' 'Drawn' 'Won'
	 */
	private function setStatus($status)
	{
		global $DB;

		$DB->sql_put("UPDATE wD_Members SET status='".$status."' WHERE id = ".$this->id);

		unset($this->Game->Members->ByStatus[$this->status][$this->id]);
		$this->status = $status;
		$this->Game->Members->ByStatus[$this->status][$this->id] = $this;

		switch($status)
		{
			case 'Defeated':
			case 'Resigned':
			case 'Survived':
			case 'Drawn':
			case 'Won':
				$this->cacheMessageCount();
				break;
			default:
		}
	}

	/**
	 * Cache the number of messages this user has sent in this game into the gameMessagesSent field,
	 * for use in the profile page.
	 */
	private function cacheMessageCount()
	{
		global $DB;

		$DB->sql_put("
			UPDATE wD_Members m SET gameMessagesSent = (
				SELECT COUNT(gm.gameID) FROM wD_GameMessages gm WHERE m.gameID = gm.gameID AND m.countryID = gm.fromCountryID
			)
			WHERE m.id = ".$this->id);
	}

	/**
	 * The player didn't win, but survived. Maybe they got nothing in a WTA game, maybe they won something.
	 * Messages are sent and the status is updated.
	 *
	 * @param int $winnings The number of points this player has won
	 */
	function setSurvived($winnings)
	{
		global $Game;

		$winnings = $this->awardPoints($winnings);

		$this->setStatus('Survived');

		if ( $Game->potType == 'Unranked')
		{
		$this->send('No','No',l_t("The game has ended: You survived and were returned %s",$winnings.' '.libHTML::points()));
		}
		else if ( $Game->potType != 'Points-per-supply-center')
		{
			$but="";
			if($winnings)
				$but = l_t(" (but you did get %s back, to make up your starting 100)",$winnings.' '.libHTML::points());

			$this->send('No','No',l_t("The game has ended: You survived until the end, but because this is a winner takes all game you got no points returned%s. Better luck next time!",$but));
		}
		else
		{
			$this->send('No','No',l_t("The game has ended: You survived and got %s!",$winnings.' '.libHTML::points()));
		}
	}

	/**
	 * The user has left the game and can be taken over by other players, however
	 * he hasn't completely left, so he can't yet be supplemented.
	 *
	 * ignore is true when this CD is forced by a moderator
	 */
	function setLeft($ignore=0)
	{
		global $DB;

		$this->setStatus('Left');

		// Register the civil disorder
		$DB->sql_put(
			"INSERT INTO wD_CivilDisorders ( gameID, userID, countryID, turn, bet, SCCount ,forcedByMod)
			VALUES ( ".$this->gameID.", ".$this->userID.", ".$this->countryID.", ".$this->Game->turn.", ".$this->bet.", ".$this->supplyCenterNo.", $ignore)"
		);

		/*
		 * Don't do addWinnings(0), because that will refund their points; they
		 * can still rejoin so leave their points untouched.
		 *
		 * If they don't rejoin they they'll get addWinnings(0) at the end via setResigned.
		 */

		$this->send('No','No',l_t("Your empire has gone inactive, and fallen into civil disorder. It can now be ".
			"taken over by anyone, unless you take it back!"));
		$this->Game->Members->sendExcept($this,'No',l_t('%s has gone into civil disorder.',$this->Game->Variant->countries[$this->countryID-1]));
	}

	/**
	 * The game was abandoned, give supplement, send the member a message about it.
	 */
	function setAbandoned()
	{
		$refundedPoints = $this->awardSupplement();

		// No need to set status, we're about to be deleted
		$but="";
		if($refundedPoints)
			$but=l_t("You received %s to bring your total points back to the 100 minimum. ",$refundedPoints.' '.libHTML::points());

		$this->send('No','No',l_t("Due to inactivity the game was abandoned and removed. %sBetter luck next time!",$but));
	}

	/**
	 * The game was cancelled due to not enough players, give refund, send the member a message about it.
	 */
	function setNotEnoughPlayers()
	{
		$refundedPoints = $this->cancelBet();

		// No need to set status, we're about to be deleted
		$this->send('No','No',
				l_t("This game has been cancelled, and you got your bet of %s back: ".
				"The game didn't reach the %s required players; try finding players to join before ".
				"creating a game, create a game with a longer phase, or join an existing game.",
			$refundedPoints.libHTML::points(),count($this->Game->Variant->countries)));
	}

	/**
	 * The game was cancelled, refund points, send the member a message about it.
	 */
	function setCancelled()
	{
		$refundedPoints = $this->cancelBet();

		$this->setStatus('Survived');
		$this->send('No', 'No',
				l_t("This game has been cancelled, and you got your bet of %s back.",$refundedPoints.libHTML::points()));
	}

	/**
	 * A user which left has been taken over, give supplement, then send a message.
	 */
	function setTakenOver()
	{
		$refundedPoints = $this->awardSupplement();

		$but="";
		if($refundedPoints)
			$but=l_t(", but you have been refunded %s to make up your starting 100",$refundedPoints);

		$this->send('No','No',l_t("Your empire in civil disorder was taken over, so you have lost your ".
			"bet in this game%s. Better luck next time!",$but));
	}

	/**
	 * A user which left has now had the game finished, give supplement, and will be sent a message back.
	 */
	function setResigned()
	{
		$refundedPoints = $this->awardSupplement();

		$this->setStatus('Resigned');

		$but="";
		if($refundedPoints)
			$but=l_t("You have been refunded %s to make up your starting 100. ",$refundedPoints);

		$this->send('No','No',l_t("The game ended and your empire survived, but it was in civil disorder. %s".
			"Better luck next time!",$but));
	}

	/**
	 * Set the player as defeated, send a message and give a refund if necessary.
	 */
	function setDefeated($points)
	{
		if ($points != 0) {
				$winnings = $this->awardPoints($points);
			    $this->send('No','No',l_t("You were defeated and returned %s; better luck next time!",$winnings));
		} else {
				$refundedPoints = $this->awardSupplement();
				$but="";
				if($refundedPoints)
				  $but=l_t(", but you have been refunded %s to make up your starting 100",$refundedPoints);
			    $this->send('No','No',l_t("You were defeated, and lost your bet%s; better luck next time!",$but));
		}
		$this->setStatus('Defeated');

		$this->Game->Members->sendExcept($this,'No',l_t('%s was defeated.',$this->Game->Variant->countries[$this->countryID-1]));
	}

	/**
	 * Set the player's status to Drawn, give them their equal share of the winnings, if they survived, and send a message
	 *
	 * @param int $winnings The amount that they've won (not yet given), may be given more due to a supplement
	 */
	function setDrawn($winnings)
	{
		global $Game;

		$winnings = $this->awardPoints($winnings);

		$this->setStatus('Drawn');

		$this->send('No','No',l_t("You have drawn with your rivals, and survived! ".
			"You win %s, your share of the pot!",$winnings." ".libHTML::points()));
	}

	/**
	 * The player has won, give congratulations, but the winnings have already been given.
	 *
	 * @param int $winnings
	 */
	function setWon($winnings)
	{
		global $Game,$DB;

		$winnings = $this->awardPoints($winnings);

		$this->setStatus('Won');

		$this->send('No','No',l_t("Congrats, you have won the game, and get %s!",$winnings." ".libHTML::points()));
		$this->Game->Members->sendExcept($this,'No',l_t('%s won the game, and got %s!',$this->Game->Variant->countries[$this->countryID-1],$winnings." ".libHTML::points()));
	}

	private static $processStatusFields=array('countryID','bet','missedPhases','status');
	function processStatus()
	{
		$a=array();
		foreach(self::$processStatusFields as $field)
			$a[$field]=$this->{$field};

		$a['orderStatus']=''.$this->orderStatus;
		$a['timeLoggedIn']=libTime::stamp($this->timeLoggedIn);
		$a['votes']=implode(',',$this->votes);

		return $a;
	}
}

?>
