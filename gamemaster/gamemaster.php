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
			$DB->sql_put("INSERT INTO wD_AccessLog
				( userID, lastRequest, hits, ip, userAgent, cookieCode, browserFingerprint )
				SELECT userID, lastRequest, hits, ip, userAgent, cookieCode, browserFingerprint
				FROM wD_Sessions
				WHERE userID IN (".$userIDs.")");

			$DB->sql_put("DELETE FROM wD_Sessions WHERE userID IN (".$userIDs.")");

			if( isset(Config::$customForumURL) )
			{
				$DB->sql_put("UPDATE wD_Users
					SET timeLastSessionEnded = ".time().", lastMessageIDViewed = (SELECT MAX(f.id) FROM wD_ForumMessages f)
					WHERE id IN (".$userIDs.")");
			}
			else
			{
				// No need for this query if using a third party DB
				$DB->sql_put("UPDATE wD_Users SET timeLastSessionEnded = ".time()." WHERE id IN (".$userIDs.")");
			}
		}

		$DB->sql_put("COMMIT");
	}

	/**
	 * Update users' phase-per-year count, which is used to calculate reliability ratings. This has to be 
	 * done carefully as the refresh rate is fairly high and the dataset is v large. The queries below have 
	 * been optimized to ensure they don't scan the table but go straight to the index boundaries.
	 */
	static public function updatePhasePerYearCount($recalculateAll = false)
	{
		global $DB;

		//-- Careful, the below is carefully optimized to use the indexes in the best way, small changes can make this v slow:

		if( $recalculateAll )
		{
			// Recalculating everything; set all turns younger than a year to be in reliability period, and count all the phases younger than a year for each user
			$DB->sql_put("UPDATE wD_TurnDate SET isInReliabilityPeriod = CASE WHEN turnDateTime>UNIX_TIMESTAMP() - 60*60*24*365 THEN 1 ELSE 0 END;");

			$DB->sql_put("UPDATE wD_Users u LEFT JOIN (SELECT userID, COUNT(1) yearlyPhaseCount FROM wD_TurnDate WHERE isInReliabilityPeriod = 1 GROUP BY userID) phases ON phases.userID = u.id SET u.yearlyPhaseCount = COALESCE(phases.yearlyPhaseCount,0);");
		}
		else
		{
			/*
			Every phase in non-bot games a users phasePerYear count is incremented and wD_TurnDate is added to, and when run 
			this routine should find all phases from over a year ago which are still flagged as in the reliability period, and
			decrement them from the users yearly phase count.

			The aim is to need to scan as few turndate records as possible, and update as few user records as possible
			*/

			// Set any phases that have just turned older than 1 year to have a NULL isInReliabilityPeriod flag, so that
			// the count of phases that have expired can be removed from the user's phases per year count.
			$DB->sql_put("UPDATE wD_TurnDate t
				INNER JOIN (
					-- Find the first id marked as in the last year using the isInReliabilityPeriod,turnDateTime index
					SELECT id FROM wD_TurnDate WHERE isInReliabilityPeriod = 1 ORDER BY isInReliabilityPeriod,turnDateTime LIMIT 1
				) lwr
				INNER JOIN (
					-- Up to the first id younger than a year using the turnDateTime index
					SELECT id FROM wD_TurnDate WHERE turnDateTime > UNIX_TIMESTAMP() - 365*24*60*60 ORDER BY turnDateTime LIMIT 1
				) upr
				SET t.isInReliabilityPeriod = NULL
				WHERE t.id >= lwr.id AND t.id <= upr.id;");

			$DB->sql_put("UPDATE wD_Users u
				INNER JOIN (
					SELECT t.userID, COUNT(1) yearlyPhaseCountJustExpired
					FROM wD_TurnDate t 
					WHERE t.isInReliabilityPeriod IS NULL
					GROUP BY t.userID
				) p ON p.userID = u.id
				SET u.yearlyPhaseCount = u.yearlyPhaseCount - p.yearlyPhaseCountJustExpired;");

			// Now set any phases that have just become older than a year as outside the reliability rating period:
			$DB->sql_put("UPDATE wD_TurnDate SET isInReliabilityPeriod = 0 WHERE isInReliabilityPeriod IS NULL;");
		}
		$DB->sql_put("COMMIT"); // Ensure no users are left locked
		$DB->sql_put("BEGIN"); // I think this might be needed to ensure we are within a transaction going forward?
	}
	/**
	 * Recalculates the reliability ratings for all users.
	 * 
	 * Each active phase players are in adds to TurnData, which GameMaster sums up for each user for the past 
	 * year every 15 mins
	 */
	static public function updateReliabilityRating()
	{
		global $DB, $Misc;

		/*
		The RR calculation is based on this query which recalculates the RR for a user, but in 
		UPDATE wD_Users u 
		set u.reliabilityRating = greatest(0, 
		(100 *(1 - ((SELECT COUNT(1) FROM wD_MissedTurns t  WHERE t.userID = u.id AND t.modExcused = 0 and t.turnDateTime > ".$year.") / greatest(1,u.yearlyPhaseCount))))
		-(6*(SELECT COUNT(1) FROM wD_MissedTurns t  WHERE t.userID = u.id AND t.liveGame = 0 AND t.modExcused = 0 and t.samePeriodExcused = 0 and t.systemExcused = 0 and t.turnDateTime > ".$lastMonth."))
		-(6*(SELECT COUNT(1) FROM wD_MissedTurns t  WHERE t.userID = u.id AND t.liveGame = 1 AND t.modExcused = 0 and t.samePeriodExcused = 0 and t.systemExcused = 0 and t.turnDateTime > ".$lastWeek."))
		-(5*(SELECT COUNT(1) FROM wD_MissedTurns t  WHERE t.userID = u.id AND t.liveGame = 1 AND t.modExcused = 0 and t.samePeriodExcused = 0 and t.systemExcused = 0 and t.turnDateTime > ".$lastMonth."))
		-(5*(SELECT COUNT(1) FROM wD_MissedTurns t  WHERE t.userID = u.id AND t.liveGame = 0 AND t.modExcused = 0 and t.samePeriodExcused = 0 and t.systemExcused = 0 and t.turnDateTime > ".$year.")))
		where u.id = ".$userIDtoUpdate;
		*/

		// Calculates the RR for members. 
		$DB->sql_put("UPDATE wD_Users u
		LEFT JOIN (
			SELECT 
				t.userID, 
				SUM(
				CASE 
					-- If not missed for an exempt reason
					WHEN t.systemExcused = 0 AND t.samePeriodExcused = 0
					THEN
						CASE
						-- If not live ..
						WHEN liveGame = 0
						THEN
							CASE WHEN t.turnDateTime > UNIX_TIMESTAMP() - 28*24*60*60 
							THEN 0.11 -- .. add 11% for missed turns newer than 28 days
							ELSE 0.05  -- .. or 5% for missed turns older than 28 days
							END
						ELSE -- liveGame = 1
						
							CASE WHEN t.turnDateTime > UNIX_TIMESTAMP() - 7*24*60*60 
							THEN 0.11 -- .. add 11% for missed turns newer than 7 days
							WHEN t.turnDateTime > UNIX_TIMESTAMP() - 28*24*60*60
							THEN 0.05  -- .. or 5% for missed turns newer than 28 days
							ELSE 0.0 
							END
						END
					-- If missed for an exempt reason add a value from 100 to 0 that gets smaller as the user does more games.
					-- Goes from 99 with 100 phases / year, and 1 with 0 phases / year.
					ELSE 0.0
				END) missedTurnPenalty,
				COUNT(1) missedTurnCount
			FROM wD_MissedTurns t
			WHERE t.modExcused = 0 AND t.turnDateTime > UNIX_TIMESTAMP() - 60*60*24*365
			GROUP BY t.userID
		) t ON t.userID = u.id
		SET u.reliabilityRating = 100.0 * GREATEST(((1.0 - COALESCE(missedTurnCount,0) / GREATEST(u.yearlyPhaseCount,1)) 
			- COALESCE(t.missedTurnPenalty,0)), 0);
		");
		
		$DB->sql_put("COMMIT");
	}
}

?>
