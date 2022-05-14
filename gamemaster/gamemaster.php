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
			$DB->sql_put("UPDATE wD_TurnDate SET isInReliabilityPeriod = CASE WHEN turnDateTime>UNIX_TIMESTAMP() - 60*60*24*365 THEN 1 ELSE 0 END;");
		}

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
				SELECT t.userID, COUNT(*) yearlyPhaseCount
				FROM (SELECT userID FROM wD_TurnDate WHERE isInReliabilityPeriod IS NULL ".($recalculateAll?" OR 1=1 " :"").") phasesChanged
				INNER JOIN wD_TurnDate t ON phasesChanged.userID = t.userID
				WHERE t.isInReliabilityPeriod = 1
				GROUP BY t.userID
			) p ON p.userID = u.id
			SET u.yearlyPhaseCount = p.yearlyPhaseCount;");
		$DB->sql_put("UPDATE wD_TurnDate SET isInReliabilityPeriod = 0 WHERE isInReliabilityPeriod IS NULL;");
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

		// Calculates the RR for members. 
		$DB->sql_put("SELECT a.id, a.reliabilityRating RR_NOW, b.reliabilityRating RR_NEW FROM wD_Users a
		INNER JOIN (
			SELECT userID, IF(reliabilityRating<0,0,reliabilityRating) reliabilityRating
			FROM (
				SELECT t.userID, 
				SUM(CASE 
				-- If not missed for an exempt reasonwd_turndate
				WHEN t.systemExcused = 0 AND t.samePeriodExcused = 0
				THEN
					CASE
					-- If not live ..
					WHEN liveGame = 0
					THEN
						-- .. then take 6 for missed turns newer than 1 month ago
						CASE WHEN t.turnDateTime > UNIX_TIMESTAMP() - 31*24*60*60 THEN -6 ELSE 0 END
						- -- .. and 5 for older missed turns (only up to 1 year)
						5
					ELSE -- liveGame = 1
						-- Or if live .. take 6 for missed turns under a week ago
						CASE WHEN t.turnDateTime > UNIX_TIMESTAMP() - 7*24*60*60 THEN -6 ELSE 0 END
						+
						-- And take 5 for missed turns under a month ago
						CASE WHEN t.turnDateTime > UNIX_TIMESTAMP() - 31*24*60*60 THEN -5 ELSE 0 END
					END
				-- If missed for an exempt reason add a value from 100 to 0 that gets smaller as the user does more games.
				-- Goes from 99 with 100 phases / year, and 1 with 0 phases / year.
				ELSE 100 * (1 - 1 / IF(u.yearlyPhaseCount < 1, 1, u.yearlyPhaseCount))
				END) reliabilityRating
			FROM wD_MissedTurns t  
			INNER JOIN wD_Users u ON u.id = t.UserID
			WHERE t.modExcused = 0 AND t.turnDateTime > UNIX_TIMESTAMP() - 60*60*24*365
			GROUP BY t.userID
			) b 
		) b ON a.id = b.userID;");
		
		$DB->sql_put("COMMIT");
	}
}

?>
