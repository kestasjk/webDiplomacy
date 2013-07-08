<?php
/*
    Copyright (C) 2013 Oliver Auth

	This file is part of vDiplomacy.

    vDiplomacy is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    vDiplomacy is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with webDiplomacy.  If not, see <http://www.gnu.org/licenses/>.
*/

class libReliability
{

	public static $grades = array (
		98=>'98+', 90=>'90+', 80=>'80+', 60=>'60+', 40=>'40+', 10=>'10+', 0=>'0', '-100'=>'Rookie'
	);
	
	/**
	 * Calc a reliability rating.  Reliability rating is 100 minus phases missed / phases played * 200, not to be lower than 0
	 * Examples: If a user misses 5% of their games, rating would be 90, 15% would be 70, etc. 
	 * Certain features of the site (such as creating and joining games) will be restricted if the reliability rating is too low.
	 * @return reliability
	 */
	static public function calcReliability($missedMoves, $phasesPlayed, $gamesLeft, $leftBalanced)
	{
		if ( $phasesPlayed == 0 )
			$reliability = 100;
		else
			$reliability = ceil(100 - $missedMoves / $phasesPlayed * 200 - (10 * ($gamesLeft - $leftBalanced)));

		if ($reliability < 0) $reliability = 0;
		
		if ( $phasesPlayed < 20 ) $reliability = $reliability * -1;
		if ( $phasesPlayed < 20 && $reliability == 0) $reliability = -1;

		return $reliability;
	}

	/**
	 * Get a user's or members reliability rating.	 
	 * @return reliability
	 */
	static public function getReliability($User)
	{
		return self::calcReliability($User->missedMoves, $User->phasesPlayed, $User->gamesLeft, $User->leftBalanced);
	}
	
	/**
	 * Display the Grade to the given reliability
	 */
	static public function Grade($reliability)
	{
		foreach (self::$grades as $limit=>$grade)
			if ($reliability >= $limit)
				return $grade;
	}
	
	/**
	 * Get a user's Grade... 
	 * @return grade as string...
	 */
	static public function getGrade($User)
	{
		$reliability = libReliability::calcReliability($User->missedMoves, $User->phasesPlayed, $User->gamesLeft, $User->leftBalanced);
		return libReliability::Grade($reliability);
	}
		
	/**
	 * Check if the users reliability is high enough to join/create more games
	 * @return true or error message	 
	 */
	static public function isReliable($User)
	{
		global $DB;
		
		// A player can't join new games, as long as he has active CountrySwiches.
		list($openSwitches)=$DB->sql_row('SELECT COUNT(*) FROM wD_CountrySwitch WHERE (status = "Send" OR status = "Active") AND fromID='.$User->id);
		if ($openSwitches > 0)
			return "<p><b>NOTICE:</b></p><p>You can't join or create new games, as you have active CountrySwitches at the moment.</p>";

		$reliability = self::getReliability($User);
		$maxGames = ceil($reliability / 10);
		list($totalGames) = $DB->sql_row("SELECT COUNT(*) FROM wD_Members m, wD_Games g WHERE m.userID=".$User->id." and m.gameID=g.id and g.phase!='Finished' and m.bet>1");
		
		// This will prevent newbies from joining 10 games and then leaving right away.
		if ( $totalGames > 4 && $User->phasesPlayed < 20 ) 
			return "<p>You're taking on too many games at once for a new member.<br>Please relax and enjoy the game or games that you are currently in before joining/creating a new one.<br>You need to play at least <strong>20 phases</strong>, bevore you can join more than 4 games. Once you played 20 phases your reliability-rating will affect how many games you can play at once. You can than join 1 game for each 10% RR. If your RR if better than 90% you can join as many games as you want.<br>2-player variants are not affected by this restriction.</p>";
		
		// If the rating is 90 or above, there is no game limit restriction
		if ($maxGames < 10 && $User->phasesPlayed >= 20) { 
			if ( $reliability == 0 )
				return "<p>NOTICE: You are not allowed to join or create any games given your reliability rating of ZERO (meaning you have missed more than 50% of your orders across all of your games)</p><p>You can improve your reliability rating by not missing any orders, even if it's just saving the default 'Hold' for everything.</p><p>If you are not currently in a game and cannot join one because of this restriction, then you may contact an <a href=\"modforum.php\">admin</a> and briefly explain your extremely low rating.  The admin, at his or her discretion, may set your reliability rating high enough to allow you 1 game at a time. By consistently putting in orders every turn in that new game, your reliability rating will improve enough to allow you more simultaneous games. 2-player variants are not affected by this restriction.</p>";
			elseif ( $totalGames >= $maxGames ) // Can't have more than reliability rating / 10 games up
				return "<p>NOTICE: You cannot join or create a new game, because you seem to be having trouble keeping up with the orders in the ones you already have</p><p>You can improve your reliability rating by not missing any orders, even if it's just saving the default 'Hold' for everything.</p><p>Please note that if you are marked as 'Left' for a game, your rating will continue to take hits until someone takes over for you.</p><p>Your current rating of <strong>".$reliability."</strong> allows you to have no more than <strong>".$maxGames."</strong> concurrent games before you see this message.  Every 10 reliability points will allow you an additional game. 2-player variants are not affected by this restriction. Any you can join as many 'open' spots in ongoing games as you like if there are no additional restrictions for the game.</p>";
		}
	}
	
	/**
	 * Update a members reliability-stats
	 */
	static function updateReliability($Member, $type, $calc)
	{
		global $DB, $Game;
		
		if ($type == 'leftBalanced' && ($Member->leftBalanced >= $Member->gamesLeft))
			return;
			
		if ( (count($Game->Variant->countries) > 2) && ($Game->phaseMinutes > 30) )
			$DB->sql_put("UPDATE wD_Users SET ".$type." = ".$type." ".$calc." WHERE id=".$Member->userID);		
	}

	/**
	 * Adjust the missed turns of each member and update the phase counter
	 * for games with more then 2 players and not live games...
	 * "Left" users are included (for civil disorder to total phases ratio calculating)
	 */
	static function updateReliabilities($Members)
	{
		foreach($Members->ByStatus['Playing'] as $Member)
		{
			self::updateReliability($Member, 'phasesPlayed', '+ 1');
			if ($Member->orderStatus == '')
				self::updateReliability($Member, 'missedMoves', '+ 1');
		}
		
		foreach($Members->ByStatus['Left'] as $Member)
		{
			self::updateReliability($Member, 'phasesPlayed', '+ 1');
			self::updateReliability($Member, 'missedMoves' , '+ 1');
		}
	}
	
}

?>
