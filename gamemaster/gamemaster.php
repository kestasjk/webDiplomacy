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
 * A class which performs utility functions for the gamemaster script, such as
 * adding/removing/fetching items from the process-queue, and doing various maintenance
 * tasks.
 *
 * @package GameMaster
 */
class libGameMaster
{
	/**
	 * Removes temporary (keep='No') notices that are more than a week old.
	 */
	public static function clearStaleNotices()
	{
		global $DB;

		$DB->sql_put("DELETE FROM wD_Notices
			WHERE keep='No' AND timeSent < (".time()."-7*24*60*60)");
	}

	/**
	 * Update the session table; for users which have expired from it enter their data into the
	 * access log and add their hits to the global hits counter.
	 */
	static public function updateSessionTable()
	{
		global $DB, $Misc;

		$DB->sql_put("BEGIN");

		$tabl = $DB->sql_tabl("SELECT userID FROM wD_Sessions
						WHERE UNIX_TIMESTAMP(lastRequest) < UNIX_TIMESTAMP(CURRENT_TIMESTAMP) - 10 * 60");

		$userIDs = array();

		while ( list($userID) = $DB->tabl_row($tabl) )
			$userIDs[] = $userID;

		if ( count($userIDs) > 0 )
		{
			$userIDs = implode(', ', $userIDs);

			// Update the hit counter
			list($newhits) = $DB->sql_row("SELECT SUM(hits) FROM wD_Sessions WHERE userID IN (".$userIDs.")");

			$Misc->Hits += $newhits;
			$Misc->write();

			// Save access logs, to detect multi-accounters
			$DB->sql_put("INSERT DELAYED INTO wD_AccessLog
				( userID, lastRequest, hits, ip, userAgent, cookieCode )
				SELECT userID, lastRequest, hits, ip, userAgent, cookieCode
				FROM wD_Sessions
				WHERE userID IN (".$userIDs.")");

			$DB->sql_put("DELETE FROM wD_Sessions WHERE userID IN (".$userIDs.")");

			$DB->sql_put("UPDATE wD_Users
					SET timeLastSessionEnded = ".time().", lastMessageIDViewed = (SELECT MAX(f.id) FROM wD_ForumMessages f)
					WHERE id IN (".$userIDs.")");

		}

		$DB->sql_put("COMMIT");
	}

	/**
	 * Update the reliability ratings by taking count of the number of civil disorders, NMRs, and civil disorders taken over for each 
	 * user. Uses the wD_CivilDisorders and wD_NMRs tables and recalculates for all users that have logged in the last two weeks.
	 * 
	 * This is a relatively DB intensive query since it needs to check over three tables for all the users it includes, but it does 
	 * ensure the way the numbers are calculated can be tracked back to the specific games involved and tweaked down the or by other 
	 * installations (e.g. whether games just joined should be counted, etc).
	 * 
	 * This could be optimized by making it recalculate only for users who are members / who were members in games that have just been
	 * processed.
	 * 
	 * @param $recalculateAll If true don't filter on active users, but recalculate for all users, which takes longer
	 */
	static public function updateReliabilityRating($recalculateAll = false)
	{
		global $DB, $Misc;
		
		/*
		 * CDs and NMRs are straightforward counts.
		 * 
		 * GameCount is the number of memberships the user has, plus the number of civil disorders which the user didn't rejoin
		 * 
		 * CTTakenCount is calculated by taking all of a user's memberships to games, linking them
		 * to civil disorders in that game (other than the user's civil disorders), and making sure that only the latest civil disorder
		 * is counted (so that the user isn't said to have taken over from two civil disorders if a certain country went into civil 
		 * disorder twice)
		 * 
		 * Then the reliabilityRating is calculated based on the formula considered most appropriate. However the 
		 */
		$DB->sql_put("UPDATE wD_Users u 
			SET u.cdCount = (SELECT COUNT(c.userID) FROM wD_CivilDisorders c WHERE c.userID = u.id AND c.forcedByMod=0),
				u.nmrCount = (SELECT COUNT(n.userID) FROM wD_NMRs n WHERE n.userID = u.id),
				u.gameCount = (
					SELECT COUNT(*) 
					FROM wD_Members m
					WHERE m.userID = u.id) + (
					SELECT COUNT(*) 
					FROM wD_CivilDisorders c LEFT JOIN wD_Members m ON c.gameID = m.gameID AND c.userID = m.userID AND c.countryID = m.countryID
					WHERE m.id IS NULL AND c.userID = u.id),
				u.cdTakenCount = (
					SELECT COUNT(*)
					FROM wD_Members ct
					INNER JOIN wD_CivilDisorders c ON c.gameID = ct.gameID AND c.countryID = ct.countryID AND NOT c.userID = ct.userID
					WHERE ct.userID = u.id AND c.turn = (
						SELECT MAX(sc.turn) 
						FROM wD_CivilDisorders sc 
						WHERE sc.gameID = c.gameID AND sc.countryID = c.countryID
					)
				),
				u.reliabilityRating = ( 1.0 - (u.cdCount + u.deletedCDs / (u.gameCount+1) ))
			".($recalculateAll ? "" : "WHERE u.timeLastSessionEnded+(14*24*60*60) > ".$Misc->LastProcessTime));
		
		$DB->sql_put("COMMIT");
	}
}

?>
