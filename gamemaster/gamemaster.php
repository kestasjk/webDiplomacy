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
	 * Update users' phase-per-year count and the missing turn counts which are then used to calculate reliability ratings. 
	 * This has to be done carefully as the refresh rate is fairly high and the dataset is v large. The queries below have 
	 * been optimized to ensure they don't scan the table but go straight to the index boundaries based on different periods.
	 * 
	 * All these updates rely on turns falling into different age buckets, and being able to look things up quickly by the bucket,
	 * the time, and the id.
	 * 
	 * For wD_TurnDate there is just one bucket; younger than a year and older than a year. 
	 * 
	 * Every gamemaster cycle the TurnDate with the oldest record that is within the year is selected, 
	 * and the oldest record that is flagged as younger than a year is selected.
	 * All records from the oldest flagged as younger than a year up to and not including the oldest record that is within 
	 * the year are set to NULL to indicate they are moving from being within the year / period to being outside it.
	 * 
	 * These NULL turn records can be efficiently queried to find the user IDs that need to be updated, and counted to know
	 * how many turns to subtract from the users' turn per year counter.
	 * 
	 * This allows up to date reliability ratings without the full table scans during exclusive lock periods, which caused locking
	 * as the datasets grew in size.
	 */
	static public function updateReliabilityRatings($recalculateAll = false)
	{
		global $DB;

		//-- Careful, the below is carefully optimized to use the indexes in the best way, small changes can make this v slow:

		if( $recalculateAll )
		{
			// Recalculating everything; set all turns younger than a year to be in reliability period, and count all the phases younger than a year for each user
			$DB->sql_put("UPDATE wD_TurnDate SET isInReliabilityPeriod = CASE WHEN turnDateTime>UNIX_TIMESTAMP() - 60*60*24*365 THEN 1 ELSE 0 END;");

			$DB->sql_put("UPDATE wD_Users u LEFT JOIN (
					SELECT userID, COUNT(1) yearlyPhaseCount 
					FROM wD_TurnDate 
					WHERE isInReliabilityPeriod = 1 
					GROUP BY userID
				) phases ON phases.userID = u.id 
				SET u.yearlyPhaseCount = COALESCE(phases.yearlyPhaseCount,0),
					u.isPhasesDirty = 1;");
				
			// Do the same for missed turns, which is more complicated as there are several different reliability periods:
			$DB->sql_put("UPDATE wD_MissedTurns 
				SET reliabilityPeriod = CASE 
						WHEN turnDateTime > UNIX_TIMESTAMP() - 7*24*60*60 THEN 3 
						WHEN turnDateTime > UNIX_TIMESTAMP() - 28*24*60*60 THEN 2 
						WHEN turnDateTime > UNIX_TIMESTAMP() - 365*24*60*60 THEN 1 
						ELSE 0
				END");

			// Set the week period missed turns to the users
			$DB->sql_put("UPDATE wD_Users u LEFT JOIN (
					SELECT userID,
						SUM(CASE WHEN liveGame = 0 AND samePeriodExcused = 0 AND systemExcused = 0 AND modExcused = 0 THEN 1 ELSE 0 END) nonLiveNew,
						SUM(CASE WHEN liveGame = 1 AND samePeriodExcused = 0 AND systemExcused = 0 AND modExcused = 0 THEN 1 ELSE 0 END) liveNew,
						SUM(CASE WHEN modExcused = 0 THEN 1 ELSE 0 END) totalNew 
					FROM wD_MissedTurns
					WHERE reliabilityPeriod = 3
					GROUP BY userID
				) phases ON phases.userID = u.id
				SET u.missedPhasesLiveLastWeek = COALESCE(phases.liveNew,0),
					u.missedPhasesNonLiveLastWeek = COALESCE(phases.nonLiveNew,0),
					u.missedPhasesTotalLastWeek = COALESCE(phases.totalNew,0),
					u.isPhasesDirty = 1;");
					
			// Set the month period missed turns to the users
			$DB->sql_put("UPDATE wD_Users u LEFT JOIN (
				SELECT userID,
					SUM(CASE WHEN liveGame = 0 AND samePeriodExcused = 0 AND systemExcused = 0 AND modExcused = 0 THEN 1 ELSE 0 END) nonLiveNew,
					SUM(CASE WHEN liveGame = 1 AND samePeriodExcused = 0 AND systemExcused = 0 AND modExcused = 0 THEN 1 ELSE 0 END) liveNew,
					SUM(CASE WHEN modExcused = 0 THEN 1 ELSE 0 END) totalNew 
				FROM wD_MissedTurns
				WHERE reliabilityPeriod = 2
				GROUP BY userID
			) phases ON phases.userID = u.id
			SET u.missedPhasesLiveLastMonth = COALESCE(phases.liveNew,0),
				u.missedPhasesNonLiveLastMonth = COALESCE(phases.nonLiveNew,0),
				u.missedPhasesTotalLastMonth = COALESCE(phases.totalNew,0),
				u.isPhasesDirty = 1;");
				
			// Set the year period missed turns to the users
			$DB->sql_put("UPDATE wD_Users u LEFT JOIN (
				SELECT userID,
					SUM(CASE WHEN liveGame = 0 AND samePeriodExcused = 0 AND systemExcused = 0 AND modExcused = 0 THEN 1 ELSE 0 END) nonLiveNew,
					SUM(CASE WHEN liveGame = 1 AND samePeriodExcused = 0 AND systemExcused = 0 AND modExcused = 0 THEN 1 ELSE 0 END) liveNew,
					SUM(CASE WHEN modExcused = 0 THEN 1 ELSE 0 END) totalNew 
				FROM wD_MissedTurns
				WHERE reliabilityPeriod = 1
				GROUP BY userID
			) phases ON phases.userID = u.id
			SET u.missedPhasesLiveLastYear = COALESCE(phases.liveNew,0),
				u.missedPhasesNonLiveLastYear = COALESCE(phases.nonLiveNew,0),
				u.missedPhasesTotalLastYear = COALESCE(phases.totalNew,0),
				u.isPhasesDirty = 1;");
		}
		else
		{
			/*
			This branch has the same end result as the above which recalculates everything, but is made to be quick enough that
			it can be rerun on every gamemaster cycle and will only update/reference the turn and user records that need to be 
			updated. There are many GB of turns and the missing turns is several MB, so doing a full scan every second isn't an option.


			Every phase in non-bot games a users phasePerYear count is incremented in gamemaster/members.php and wD_TurnDate is 
			added to. Left to itself the phases per year would go up always.
			
			This routine should find all phases from over a year ago which are still flagged as in the reliability period, and
			decrement them from the users yearly phase count.

			The aim is to need to scan as few turndate records as possible, and update as few user records as possible; only the
			turndate records moving from one turn to another, and only the users that have turns that have changed
			*/

			// Set any phases that have just turned older than 1 year to have a NULL isInReliabilityPeriod flag, so that
			// the count of phases that have expired can be removed from the user's phases per year count.
			$DB->sql_put("UPDATE wD_TurnDate turns
				INNER JOIN (
					-- Find the first id marked as in the last year using the isInReliabilityPeriod,turnDateTime index
					SELECT id FROM wD_TurnDate WHERE isInReliabilityPeriod = 1 ORDER BY isInReliabilityPeriod,turnDateTime LIMIT 1
				) oldestTurnFlaggedInPeriod
				INNER JOIN (
					-- Up to the first id younger than a year using the turnDateTime index
					SELECT id FROM wD_TurnDate WHERE turnDateTime > UNIX_TIMESTAMP() - 365*24*60*60 ORDER BY turnDateTime LIMIT 1
				) oldestTurnWithinPeriod
				SET turns.isInReliabilityPeriod = NULL
				WHERE oldestTurnFlaggedInPeriod.id <= turns.id AND turns.id < oldestTurnWithinPeriod.id;");

			// Now the turns with a NULL isInReliabilityPeriod are the turns that have just expired and can be removed from the user's 
			// turn count.
			$DB->sql_put("UPDATE wD_Users u
				INNER JOIN (
					SELECT t.userID, COUNT(1) yearlyPhaseCountJustExpired
					FROM wD_TurnDate t 
					WHERE t.isInReliabilityPeriod IS NULL
					GROUP BY t.userID
				) p ON p.userID = u.id
				SET u.yearlyPhaseCount = u.yearlyPhaseCount - p.yearlyPhaseCountJustExpired,
					u.isPhasesDirty = 1;");

			// Now set any phases that have just become older than a year as outside the reliability rating period:
			$DB->sql_put("UPDATE wD_TurnDate SET isInReliabilityPeriod = 0 WHERE isInReliabilityPeriod IS NULL;");

			$DB->sql_put("COMMIT"); // Ensure no users are left locked
			$DB->sql_put("BEGIN"); // I think this might be needed to ensure we are within a transaction going forward?

			// Start processing missed turns

			// Process all missed turns set to -1 (new):
			$DB->sql_put("UPDATE wD_MissedTurns turns
			SET turns.reliabilityPeriod = NULL
			WHERE reliabilityPeriod = -1;");
			// Update the user missed phase buckets for the new->week missed turns:
			$DB->sql_put("UPDATE wD_Users u
				INNER JOIN (
					SELECT t.userID, 
						SUM(CASE WHEN liveGame = 0 AND samePeriodExcused = 0 AND systemExcused = 0 AND modExcused = 0 THEN 1 ELSE 0 END) nonLiveNew,
						SUM(CASE WHEN liveGame = 1 AND samePeriodExcused = 0 AND systemExcused = 0 AND modExcused = 0 THEN 1 ELSE 0 END) liveNew,
						SUM(CASE WHEN modExcused = 0 THEN 1 ELSE 0 END) totalNew
					FROM wD_MissedTurns t 
					WHERE t.reliabilityPeriod IS NULL
					GROUP BY t.userID
				) changedPhases ON changedPhases.userID = u.id
				SET u.missedPhasesLiveLastWeek = u.missedPhasesLiveLastWeek + changedPhases.liveNew,
					u.missedPhasesNonLiveLastWeek = u.missedPhasesNonLiveLastWeek + changedPhases.nonLiveNew,
					u.missedPhasesTotalLastWeek = u.missedPhasesTotalLastWeek + changedPhases.totalNew,
					u.isPhasesDirty = 1;");
			// Set new missed turns to the week (3)
			$DB->sql_put("UPDATE wD_MissedTurns turns SET turns.reliabilityPeriod = 3 WHERE turns.reliabilityPeriod IS NULL;");

			$DB->sql_put("COMMIT"); // Ensure no users are left locked
			$DB->sql_put("BEGIN"); // I think this might be needed to ensure we are within a transaction going forward?

			// Process all missed turns that are set to 3 (this week) but should be 2 (this month):
			$DB->sql_put("UPDATE wD_MissedTurns turns
			INNER JOIN (
				-- Find the first id marked as in this period
				SELECT id FROM wD_MissedTurns WHERE reliabilityPeriod = 3 ORDER BY reliabilityPeriod,turnDateTime LIMIT 1
			) oldestTurnFlaggedInPeriod
			INNER JOIN (
				-- Up to the first id younger than this period using the turnDateTime index
				SELECT id FROM wD_MissedTurns WHERE turnDateTime > UNIX_TIMESTAMP() - 7*24*60*60 ORDER BY turnDateTime LIMIT 1
			) oldestTurnWithinPeriod
			SET turns.reliabilityPeriod = NULL
			WHERE oldestTurnFlaggedInPeriod.id <= turns.id AND turns.id < oldestTurnWithinPeriod.id;");
			// Update the user missed phase buckets for the week->month missed turns:
			$DB->sql_put("UPDATE wD_Users u
				INNER JOIN (
					SELECT t.userID, 
						SUM(CASE WHEN liveGame = 0 AND samePeriodExcused = 0 AND systemExcused = 0 AND modExcused = 0 THEN 1 ELSE 0 END) nonLiveNew,
						SUM(CASE WHEN liveGame = 1 AND samePeriodExcused = 0 AND systemExcused = 0 AND modExcused = 0 THEN 1 ELSE 0 END) liveNew,
						SUM(CASE WHEN modExcused = 0 THEN 1 ELSE 0 END) totalNew
					FROM wD_MissedTurns t 
					WHERE t.reliabilityPeriod IS NULL
					GROUP BY t.userID
				) changedPhases ON changedPhases.userID = u.id
				SET u.missedPhasesLiveLastWeek = u.missedPhasesLiveLastWeek - changedPhases.liveNew,
					u.missedPhasesLiveLastMonth = u.missedPhasesLiveLastMonth + changedPhases.liveNew,
					u.missedPhasesNonLiveLastWeek = u.missedPhasesNonLiveLastWeek - changedPhases.nonLiveNew,
					u.missedPhasesNonLiveLastMonth = u.missedPhasesNonLiveLastMonth + changedPhases.nonLiveNew,
					u.missedPhasesTotalLastWeek = u.missedPhasesTotalLastWeek - changedPhases.totalNew,
					u.missedPhasesTotalLastMonth = u.missedPhasesTotalLastMonth + changedPhases.totalNew,
					u.isPhasesDirty = 1;");
			// Set new missed turns to the month (2)
			$DB->sql_put("UPDATE wD_MissedTurns turns SET turns.reliabilityPeriod = 2 WHERE turns.reliabilityPeriod IS NULL;");

			$DB->sql_put("COMMIT"); // Ensure no users are left locked
			$DB->sql_put("BEGIN"); // I think this might be needed to ensure we are within a transaction going forward?

			// Process all missed turns that are set to 2 (this month) but should be 1 (this year):
			$DB->sql_put("UPDATE wD_MissedTurns turns
			INNER JOIN (
				-- Find the first id marked as in this period
				SELECT id FROM wD_MissedTurns WHERE reliabilityPeriod = 2 ORDER BY reliabilityPeriod,turnDateTime LIMIT 1
			) oldestTurnFlaggedInPeriod
			INNER JOIN (
				-- Up to the first id younger than this period using the turnDateTime index
				SELECT id FROM wD_MissedTurns WHERE turnDateTime > UNIX_TIMESTAMP() - 28*24*60*60 ORDER BY turnDateTime LIMIT 1
			) oldestTurnWithinPeriod
			SET turns.reliabilityPeriod = NULL
			WHERE oldestTurnFlaggedInPeriod.id <= turns.id AND turns.id < oldestTurnWithinPeriod.id;");
			// Update the user missed phase buckets for the month->year missed turns:
			$DB->sql_put("UPDATE wD_Users u
				INNER JOIN (
					SELECT t.userID, 
						SUM(CASE WHEN liveGame = 0 AND samePeriodExcused = 0 AND systemExcused = 0 AND modExcused = 0 THEN 1 ELSE 0 END) nonLiveNew,
						SUM(CASE WHEN liveGame = 1 AND samePeriodExcused = 0 AND systemExcused = 0 AND modExcused = 0 THEN 1 ELSE 0 END) liveNew,
						SUM(CASE WHEN modExcused = 0 THEN 1 ELSE 0 END) totalNew
					FROM wD_MissedTurns t 
					WHERE t.reliabilityPeriod IS NULL
					GROUP BY t.userID
				) changedPhases ON changedPhases.userID = u.id
				SET u.missedPhasesLiveLastMonth = u.missedPhasesLiveLastMonth - changedPhases.liveNew,
					u.missedPhasesLiveLastYear = u.missedPhasesLiveLastYear + changedPhases.liveNew,
					u.missedPhasesNonLiveLastMonth = u.missedPhasesNonLiveLastMonth - changedPhases.nonLiveNew,
					u.missedPhasesNonLiveLastYear = u.missedPhasesNonLiveLastYear + changedPhases.nonLiveNew,
					u.missedPhasesTotalLastMonth = u.missedPhasesTotalLastMonth - changedPhases.totalNew,
					u.missedPhasesTotalLastYear = u.missedPhasesTotalLastYear + changedPhases.totalNew,
					u.isPhasesDirty = 1;");
			// Set new missed turns to the year (1)
			$DB->sql_put("UPDATE wD_MissedTurns turns SET turns.reliabilityPeriod = 1 WHERE turns.reliabilityPeriod IS NULL;");

			$DB->sql_put("COMMIT"); // Ensure no users are left locked
			$DB->sql_put("BEGIN"); // I think this might be needed to ensure we are within a transaction going forward?

			// Process all missed turns that are set to 1 (this year) but should be 0 (expired):
			$DB->sql_put("UPDATE wD_MissedTurns turns
			INNER JOIN (
				-- Find the first id marked as in this period
				SELECT id FROM wD_MissedTurns WHERE reliabilityPeriod = 1 ORDER BY reliabilityPeriod,turnDateTime LIMIT 1
			) oldestTurnFlaggedInPeriod
			INNER JOIN (
				-- Up to the first id younger than this period using the turnDateTime index
				SELECT id FROM wD_MissedTurns WHERE turnDateTime > UNIX_TIMESTAMP() - 365*24*60*60 ORDER BY turnDateTime LIMIT 1
			) oldestTurnWithinPeriod
			SET turns.reliabilityPeriod = NULL
			WHERE oldestTurnFlaggedInPeriod.id <= turns.id AND turns.id < oldestTurnWithinPeriod.id;");
			// Update the user missed phase buckets for the month->year missed turns:
			$DB->sql_put("UPDATE wD_Users u
				INNER JOIN (
					SELECT t.userID, 
						SUM(CASE WHEN liveGame = 0 AND samePeriodExcused = 0 AND systemExcused = 0 AND modExcused = 0 THEN 1 ELSE 0 END) nonLiveNew,
						SUM(CASE WHEN liveGame = 1 AND samePeriodExcused = 0 AND systemExcused = 0 AND modExcused = 0 THEN 1 ELSE 0 END) liveNew,
						SUM(CASE WHEN modExcused = 0 THEN 1 ELSE 0 END) totalNew
					FROM wD_MissedTurns t 
					WHERE t.reliabilityPeriod IS NULL
					GROUP BY t.userID
				) changedPhases ON changedPhases.userID = u.id
				SET u.missedPhasesLiveLastYear = u.missedPhasesLiveLastYear - changedPhases.liveNew,
					u.missedPhasesNonLiveLastYear = u.missedPhasesNonLiveLastYear - changedPhases.nonLiveNew,
					u.missedPhasesTotalLastYear = u.missedPhasesTotalLastYear - changedPhases.totalNew,
					u.isPhasesDirty = 1;");
			// Set new missed turns to expired (0)
			$DB->sql_put("UPDATE wD_MissedTurns turns SET turns.reliabilityPeriod = 0 WHERE turns.reliabilityPeriod IS NULL;");
			
		}
		$DB->sql_put("COMMIT"); // Ensure no users are left locked
		$DB->sql_put("BEGIN"); // I think this might be needed to ensure we are within a transaction going forward?
/*
		The RR calculation is based on this query which recalculates the RR for a user:
		UPDATE wD_Users u 
		set u.reliabilityRating = greatest(0, 
		(100 *
			(
				1 - 
				(
					(SELECT COUNT(1) FROM wD_MissedTurns t  WHERE t.userID = u.id AND t.modExcused = 0 and t.turnDateTime > ".$year.") 
					/ 
					greatest(1,u.yearlyPhaseCount)
				)
			)
		)
		-(6*(SELECT COUNT(1) FROM wD_MissedTurns t  WHERE t.userID = u.id AND t.liveGame = 0 AND t.modExcused = 0 and t.samePeriodExcused = 0 and t.systemExcused = 0 and t.turnDateTime > ".$lastMonth."))
		-(6*(SELECT COUNT(1) FROM wD_MissedTurns t  WHERE t.userID = u.id AND t.liveGame = 1 AND t.modExcused = 0 and t.samePeriodExcused = 0 and t.systemExcused = 0 and t.turnDateTime > ".$lastWeek."))
		-(5*(SELECT COUNT(1) FROM wD_MissedTurns t  WHERE t.userID = u.id AND t.liveGame = 1 AND t.modExcused = 0 and t.samePeriodExcused = 0 and t.systemExcused = 0 and t.turnDateTime > ".$lastMonth."))
		-(5*(SELECT COUNT(1) FROM wD_MissedTurns t  WHERE t.userID = u.id AND t.liveGame = 0 AND t.modExcused = 0 and t.samePeriodExcused = 0 and t.systemExcused = 0 and t.turnDateTime > ".$year.")))
		where u.id = ".$userIDtoUpdate;

		See gamemaster/game.php Game::recordNMRs for the logic that inserts MissedTurns and has the logic for samePeriodExcused and systemExcused.
		See gamemaster/members.php Members::handleNMRs for the logic that decrements the excusedMissedTurns and sets a player to left etc, sends a message, bans players etc
		See adminActionsSeniorMod.php for the code that sets modExcused (and at the same time the reliabilityPeriod to NULL)

		samePeriodExcused is set for all missed turns that are within a 24 hour period of the first missed turn
		systemExcused is set for all missed turns where there was an excusedMissedTurn remaining in the wD_Members table
		modExcused is set when a mod excuses the missed turn via the admin control panel

		So the logic for reliability ratings is: 
			Exclude all mod-excused missed turns; they do not count towards the total missed turns.

			100% - % of missed turns over the last year (missed turns in last year / turns played in last year)
			
			For games that aren't same-period-excused (within 24 hours of another) or system-excused (forgiven by an excused turn):
			
				For non-live games:
					Take off 5% if within the last year
					Take off 11% (6+5) if within the last month
				For live games:
					Take off 5% if within the last month
					Take off 11% (6+5) if within the last week
			
		Note that MissedTurns should really be MissedPhases, as you get one for each NMR for each missed phase; turns/phases 
		are used interchangeably below, but phases is what is meant always.
		*/
		$DB->sql_put("UPDATE wD_Users u SET u.reliabilityRating = 100 * (
			greatest(0,
			1.0
			- ((u.missedPhasesTotalLastYear+u.missedPhasesTotalLastMonth+u.missedPhasesTotalLastWeek) / IF(u.yearlyPhaseCount=0,1,u.yearlyPhaseCount))
			- 0.11 * u.missedPhasesLiveLastWeek
			- 0.11 * (u.missedPhasesNonLiveLastWeek+u.missedPhasesNonLiveLastMonth)
			- 0.05 * u.missedPhasesLiveLastMonth
			- 0.05 * u.missedPhasesNonLiveLastYear)),
			u.isPhasesDirty = 0
		WHERE u.isPhasesDirty = 1;");

		$DB->sql_put("COMMIT"); // Ensure no users are left locked
		$DB->sql_put("BEGIN"); // I think this might be needed to ensure we are within a transaction going forward?

	}

	static public function updateUserConnections($lastUpdate = 0)
	{
		global $DB;

		$DB->sql_put("BEGIN");

		if( $lastUpdate == 0 )
		{
			$DB->sql_put("DELETE FROM wD_UserConnections");	
			$DB->sql_put("DELETE FROM wD_UserCodeConnections");
		}
		/*
		This update scans all new access logs since the given last update time.

		For all new records it adds the hours of the week to the user's connection table to allow usage-patterns to be compared
		It also adds any new codes (IP/Cookie/fingerprint/etc) found since the last update time to the list of codes associated with that user,
		or else adds to the code counter for that user.

		Then all new codes for each user are linked to all other users that have been linked to that code, and the count of links is 
		increased for that user.

		*/
		$DB->sql_script("
		INSERT INTO wD_UserConnections (userID)
SELECT userID
FROM (
 SELECT userID, DAYOFWEEK(lastRequest)-1 d, HOUR(lastRequest) h, SUM(hits) c
 FROM wD_AccessLog
 WHERE lastRequest >= FROM_UNIXTIME(".$lastUpdate.")
 GROUP BY userID, DAYOFWEEK(lastRequest), HOUR(lastRequest)
) rec
ON DUPLICATE KEY UPDATE  
 day0hour0  = day0hour0  + IF(d=0 AND h=0 ,c,0),
 day0hour1  = day0hour1  + IF(d=0 AND h=1 ,c,0),
 day0hour2  = day0hour2  + IF(d=0 AND h=2 ,c,0),
 day0hour3  = day0hour3  + IF(d=0 AND h=3 ,c,0),
 day0hour4  = day0hour4  + IF(d=0 AND h=4 ,c,0),
 day0hour5  = day0hour5  + IF(d=0 AND h=5 ,c,0),
 day0hour6  = day0hour6  + IF(d=0 AND h=6 ,c,0),
 day0hour7  = day0hour7  + IF(d=0 AND h=7 ,c,0),
 day0hour8  = day0hour8  + IF(d=0 AND h=8 ,c,0),
 day0hour9  = day0hour9  + IF(d=0 AND h=9 ,c,0),
 day0hour10 = day0hour10 + IF(d=0 AND h=10,c,0),
 day0hour11 = day0hour11 + IF(d=0 AND h=11,c,0),
 day0hour12 = day0hour12 + IF(d=0 AND h=12,c,0),
 day0hour13 = day0hour13 + IF(d=0 AND h=13,c,0),
 day0hour14 = day0hour14 + IF(d=0 AND h=14,c,0),
 day0hour15 = day0hour15 + IF(d=0 AND h=15,c,0),
 day0hour16 = day0hour16 + IF(d=0 AND h=16,c,0),
 day0hour17 = day0hour17 + IF(d=0 AND h=17,c,0),
 day0hour18 = day0hour18 + IF(d=0 AND h=18,c,0),
 day0hour19 = day0hour19 + IF(d=0 AND h=19,c,0),
 day0hour20 = day0hour20 + IF(d=0 AND h=20,c,0),
 day0hour21 = day0hour21 + IF(d=0 AND h=21,c,0),
 day0hour22 = day0hour22 + IF(d=0 AND h=22,c,0),
 day0hour23 = day0hour23 + IF(d=0 AND h=23,c,0),
 day1hour0  = day1hour0  + IF(d=1 AND h=0 ,c,0),
 day1hour1  = day1hour1  + IF(d=1 AND h=1 ,c,0),
 day1hour2  = day1hour2  + IF(d=1 AND h=2 ,c,0),
 day1hour3  = day1hour3  + IF(d=1 AND h=3 ,c,0),
 day1hour4  = day1hour4  + IF(d=1 AND h=4 ,c,0),
 day1hour5  = day1hour5  + IF(d=1 AND h=5 ,c,0),
 day1hour6  = day1hour6  + IF(d=1 AND h=6 ,c,0),
 day1hour7  = day1hour7  + IF(d=1 AND h=7 ,c,0),
 day1hour8  = day1hour8  + IF(d=1 AND h=8 ,c,0),
 day1hour9  = day1hour9  + IF(d=1 AND h=9 ,c,0),
 day1hour10 = day1hour10 + IF(d=1 AND h=10,c,0),
 day1hour11 = day1hour11 + IF(d=1 AND h=11,c,0),
 day1hour12 = day1hour12 + IF(d=1 AND h=12,c,0),
 day1hour13 = day1hour13 + IF(d=1 AND h=13,c,0),
 day1hour14 = day1hour14 + IF(d=1 AND h=14,c,0),
 day1hour15 = day1hour15 + IF(d=1 AND h=15,c,0),
 day1hour16 = day1hour16 + IF(d=1 AND h=16,c,0),
 day1hour17 = day1hour17 + IF(d=1 AND h=17,c,0),
 day1hour18 = day1hour18 + IF(d=1 AND h=18,c,0),
 day1hour19 = day1hour19 + IF(d=1 AND h=19,c,0),
 day1hour20 = day1hour20 + IF(d=1 AND h=20,c,0),
 day1hour21 = day1hour21 + IF(d=1 AND h=21,c,0),
 day1hour22 = day1hour22 + IF(d=1 AND h=22,c,0),
 day1hour23 = day1hour23 + IF(d=1 AND h=23,c,0),
 day2hour0  = day2hour0  + IF(d=2 AND h=0 ,c,0),
 day2hour1  = day2hour1  + IF(d=2 AND h=1 ,c,0),
 day2hour2  = day2hour2  + IF(d=2 AND h=2 ,c,0),
 day2hour3  = day2hour3  + IF(d=2 AND h=3 ,c,0),
 day2hour4  = day2hour4  + IF(d=2 AND h=4 ,c,0),
 day2hour5  = day2hour5  + IF(d=2 AND h=5 ,c,0),
 day2hour6  = day2hour6  + IF(d=2 AND h=6 ,c,0),
 day2hour7  = day2hour7  + IF(d=2 AND h=7 ,c,0),
 day2hour8  = day2hour8  + IF(d=2 AND h=8 ,c,0),
 day2hour9  = day2hour9  + IF(d=2 AND h=9 ,c,0),
 day2hour10 = day2hour10 + IF(d=2 AND h=10,c,0),
 day2hour11 = day2hour11 + IF(d=2 AND h=11,c,0),
 day2hour12 = day2hour12 + IF(d=2 AND h=12,c,0),
 day2hour13 = day2hour13 + IF(d=2 AND h=13,c,0),
 day2hour14 = day2hour14 + IF(d=2 AND h=14,c,0),
 day2hour15 = day2hour15 + IF(d=2 AND h=15,c,0),
 day2hour16 = day2hour16 + IF(d=2 AND h=16,c,0),
 day2hour17 = day2hour17 + IF(d=2 AND h=17,c,0),
 day2hour18 = day2hour18 + IF(d=2 AND h=18,c,0),
 day2hour19 = day2hour19 + IF(d=2 AND h=19,c,0),
 day2hour20 = day2hour20 + IF(d=2 AND h=20,c,0),
 day2hour21 = day2hour21 + IF(d=2 AND h=21,c,0),
 day2hour22 = day2hour22 + IF(d=2 AND h=22,c,0),
 day2hour23 = day2hour23 + IF(d=2 AND h=23,c,0),
 day3hour0  = day3hour0  + IF(d=3 AND h=0 ,c,0),
 day3hour1  = day3hour1  + IF(d=3 AND h=1 ,c,0),
 day3hour2  = day3hour2  + IF(d=3 AND h=2 ,c,0),
 day3hour3  = day3hour3  + IF(d=3 AND h=3 ,c,0),
 day3hour4  = day3hour4  + IF(d=3 AND h=4 ,c,0),
 day3hour5  = day3hour5  + IF(d=3 AND h=5 ,c,0),
 day3hour6  = day3hour6  + IF(d=3 AND h=6 ,c,0),
 day3hour7  = day3hour7  + IF(d=3 AND h=7 ,c,0),
 day3hour8  = day3hour8  + IF(d=3 AND h=8 ,c,0),
 day3hour9  = day3hour9  + IF(d=3 AND h=9 ,c,0),
 day3hour10 = day3hour10 + IF(d=3 AND h=10,c,0),
 day3hour11 = day3hour11 + IF(d=3 AND h=11,c,0),
 day3hour12 = day3hour12 + IF(d=3 AND h=12,c,0),
 day3hour13 = day3hour13 + IF(d=3 AND h=13,c,0),
 day3hour14 = day3hour14 + IF(d=3 AND h=14,c,0),
 day3hour15 = day3hour15 + IF(d=3 AND h=15,c,0),
 day3hour16 = day3hour16 + IF(d=3 AND h=16,c,0),
 day3hour17 = day3hour17 + IF(d=3 AND h=17,c,0),
 day3hour18 = day3hour18 + IF(d=3 AND h=18,c,0),
 day3hour19 = day3hour19 + IF(d=3 AND h=19,c,0),
 day3hour20 = day3hour20 + IF(d=3 AND h=20,c,0),
 day3hour21 = day3hour21 + IF(d=3 AND h=21,c,0),
 day3hour22 = day3hour22 + IF(d=3 AND h=22,c,0),
 day3hour23 = day3hour23 + IF(d=3 AND h=23,c,0),
 day4hour0  = day4hour0  + IF(d=4 AND h=0 ,c,0),
 day4hour1  = day4hour1  + IF(d=4 AND h=1 ,c,0),
 day4hour2  = day4hour2  + IF(d=4 AND h=2 ,c,0),
 day4hour3  = day4hour3  + IF(d=4 AND h=3 ,c,0),
 day4hour4  = day4hour4  + IF(d=4 AND h=4 ,c,0),
 day4hour5  = day4hour5  + IF(d=4 AND h=5 ,c,0),
 day4hour6  = day4hour6  + IF(d=4 AND h=6 ,c,0),
 day4hour7  = day4hour7  + IF(d=4 AND h=7 ,c,0),
 day4hour8  = day4hour8  + IF(d=4 AND h=8 ,c,0),
 day4hour9  = day4hour9  + IF(d=4 AND h=9 ,c,0),
 day4hour10 = day4hour10 + IF(d=4 AND h=10,c,0),
 day4hour11 = day4hour11 + IF(d=4 AND h=11,c,0),
 day4hour12 = day4hour12 + IF(d=4 AND h=12,c,0),
 day4hour13 = day4hour13 + IF(d=4 AND h=13,c,0),
 day4hour14 = day4hour14 + IF(d=4 AND h=14,c,0),
 day4hour15 = day4hour15 + IF(d=4 AND h=15,c,0),
 day4hour16 = day4hour16 + IF(d=4 AND h=16,c,0),
 day4hour17 = day4hour17 + IF(d=4 AND h=17,c,0),
 day4hour18 = day4hour18 + IF(d=4 AND h=18,c,0),
 day4hour19 = day4hour19 + IF(d=4 AND h=19,c,0),
 day4hour20 = day4hour20 + IF(d=4 AND h=20,c,0),
 day4hour21 = day4hour21 + IF(d=4 AND h=21,c,0),
 day4hour22 = day4hour22 + IF(d=4 AND h=22,c,0),
 day4hour23 = day4hour23 + IF(d=4 AND h=23,c,0),
 day5hour0  = day5hour0  + IF(d=5 AND h=0 ,c,0),
 day5hour1  = day5hour1  + IF(d=5 AND h=1 ,c,0),
 day5hour2  = day5hour2  + IF(d=5 AND h=2 ,c,0),
 day5hour3  = day5hour3  + IF(d=5 AND h=3 ,c,0),
 day5hour4  = day5hour4  + IF(d=5 AND h=4 ,c,0),
 day5hour5  = day5hour5  + IF(d=5 AND h=5 ,c,0),
 day5hour6  = day5hour6  + IF(d=5 AND h=6 ,c,0),
 day5hour7  = day5hour7  + IF(d=5 AND h=7 ,c,0),
 day5hour8  = day5hour8  + IF(d=5 AND h=8 ,c,0),
 day5hour9  = day5hour9  + IF(d=5 AND h=9 ,c,0),
 day5hour10 = day5hour10 + IF(d=5 AND h=10,c,0),
 day5hour11 = day5hour11 + IF(d=5 AND h=11,c,0),
 day5hour12 = day5hour12 + IF(d=5 AND h=12,c,0),
 day5hour13 = day5hour13 + IF(d=5 AND h=13,c,0),
 day5hour14 = day5hour14 + IF(d=5 AND h=14,c,0),
 day5hour15 = day5hour15 + IF(d=5 AND h=15,c,0),
 day5hour16 = day5hour16 + IF(d=5 AND h=16,c,0),
 day5hour17 = day5hour17 + IF(d=5 AND h=17,c,0),
 day5hour18 = day5hour18 + IF(d=5 AND h=18,c,0),
 day5hour19 = day5hour19 + IF(d=5 AND h=19,c,0),
 day5hour20 = day5hour20 + IF(d=5 AND h=20,c,0),
 day5hour21 = day5hour21 + IF(d=5 AND h=21,c,0),
 day5hour22 = day5hour22 + IF(d=5 AND h=22,c,0),
 day5hour23 = day5hour23 + IF(d=5 AND h=23,c,0),
 day6hour0  = day6hour0  + IF(d=6 AND h=0 ,c,0),
 day6hour1  = day6hour1  + IF(d=6 AND h=1 ,c,0),
 day6hour2  = day6hour2  + IF(d=6 AND h=2 ,c,0),
 day6hour3  = day6hour3  + IF(d=6 AND h=3 ,c,0),
 day6hour4  = day6hour4  + IF(d=6 AND h=4 ,c,0),
 day6hour5  = day6hour5  + IF(d=6 AND h=5 ,c,0),
 day6hour6  = day6hour6  + IF(d=6 AND h=6 ,c,0),
 day6hour7  = day6hour7  + IF(d=6 AND h=7 ,c,0),
 day6hour8  = day6hour8  + IF(d=6 AND h=8 ,c,0),
 day6hour9  = day6hour9  + IF(d=6 AND h=9 ,c,0),
 day6hour10 = day6hour10 + IF(d=6 AND h=10,c,0),
 day6hour11 = day6hour11 + IF(d=6 AND h=11,c,0),
 day6hour12 = day6hour12 + IF(d=6 AND h=12,c,0),
 day6hour13 = day6hour13 + IF(d=6 AND h=13,c,0),
 day6hour14 = day6hour14 + IF(d=6 AND h=14,c,0),
 day6hour15 = day6hour15 + IF(d=6 AND h=15,c,0),
 day6hour16 = day6hour16 + IF(d=6 AND h=16,c,0),
 day6hour17 = day6hour17 + IF(d=6 AND h=17,c,0),
 day6hour18 = day6hour18 + IF(d=6 AND h=18,c,0),
 day6hour19 = day6hour19 + IF(d=6 AND h=19,c,0),
 day6hour20 = day6hour20 + IF(d=6 AND h=20,c,0),
 day6hour21 = day6hour21 + IF(d=6 AND h=21,c,0),
 day6hour22 = day6hour22 + IF(d=6 AND h=22,c,0),
 day6hour23 = day6hour23 + IF(d=6 AND h=23,c,0),
 totalHits = totalHits + c
 ;
 
  INSERT INTO wD_UserCodeConnections (userID, type, code, earliest, latest, count)
SELECT userID, type, code , earliestRequest, latestRequest, requestCount
FROM (
 SELECT userID, 'Cookie' type, CAST(cookieCode AS BINARY) code, MIN(lastRequest) earliestRequest, MAX(lastRequest) latestRequest, SUM(hits) requestCount
 FROM wD_AccessLog
 WHERE lastRequest >= FROM_UNIXTIME(".$lastUpdate.")
 GROUP BY userID, cookieCode
) r
ON DUPLICATE KEY UPDATE latest=greatest(latestRequest, latest), count=count+requestCount;

INSERT INTO wD_UserCodeConnections (userID, type, code, earliest, latest, count)
SELECT userID, type, code , earliestRequest, latestRequest, requestCount
FROM (
 SELECT userID, 'IP' type, ip code, MIN(lastRequest) earliestRequest, MAX(lastRequest) latestRequest, SUM(hits) requestCount
 FROM wD_AccessLog
 WHERE lastRequest >= FROM_UNIXTIME(".$lastUpdate.")
 GROUP BY userID, ip
) r
ON DUPLICATE KEY UPDATE latest=greatest(latestRequest, latest), count=count+requestCount;

INSERT INTO wD_UserCodeConnections (userID, type, code, earliest, latest, count)
SELECT userID, type, code , earliestRequest, latestRequest, requestCount
FROM (
 SELECT userID, 'Fingerprint' type, browserFingerprint code, MIN(lastRequest) earliestRequest, MAX(lastRequest) latestRequest, SUM(hits) requestCount
 FROM wD_AccessLog
 WHERE lastRequest >= FROM_UNIXTIME(".$lastUpdate.")
 AND browserFingerprint IS NOT NULL AND browserFingerprint <> 0
 GROUP BY userID, browserFingerprint
) r
ON DUPLICATE KEY UPDATE latest=greatest(latestRequest, latest), count=count+requestCount;

INSERT INTO wD_UserCodeConnections (userID, type, code, earliest, latest, count)
SELECT userID, type, code , earliestRequest, latestRequest, requestCount
FROM (
 SELECT linkedId userId, 'FingerprintPro' type, 
  FROM_BASE64(visitorId) code, FROM_UNIXTIME(CAST(LEFT(requestId,10) AS INT)) earliestRequest, 
  FROM_UNIXTIME(CAST(LEFT(requestId,10) AS INT)) latestRequest, 
  1 requestCount
  FROM wD_FingerprintProRequests f
  WHERE CAST(LEFT(requestId,10) AS INT) >= ".$lastUpdate."
) r
ON DUPLICATE KEY UPDATE latest=greatest(latestRequest, latest), count=count+requestCount;

 INSERT INTO wD_UserConnections (userID)
 SELECT DISTINCT u.userID 
 FROM wD_UserCodeConnections u
 LEFT JOIN wD_UserConnections c ON c.userID = u.userID
 WHERE u.isNew = 1 AND c.userID IS NULL;

UPDATE wD_UserConnections uc
INNER JOIN (
 SELECT a.userID, a.type, COUNT(*) matches
 FROM wD_UserCodeConnections a
 INNER JOIN wD_UserCodeConnections b ON a.type = b.type AND a.code = b.code AND a.userID <> b.userID
  WHERE a.type = 'IP' AND a.isNew = 1
 GROUP BY a.userID, a.type
 UNION
  SELECT a.userID, a.type, COUNT(*) matches
 FROM wD_UserCodeConnections a
 INNER JOIN wD_UserCodeConnections b ON a.type = b.type AND a.code = b.code AND a.userID <> b.userID
  WHERE a.type = 'IP' AND b.isNew = 1
 GROUP BY a.userID, a.type
) rec ON rec.userID = uc.userId
SET countMatchedIPUsers = countMatchedIPUsers + rec.matches;
UPDATE wD_UserConnections uc
INNER JOIN (
 SELECT a.userID, a.type, COUNT(*) matches
 FROM wD_UserCodeConnections a
 INNER JOIN wD_UserCodeConnections b ON a.type = b.type AND a.code = b.code AND a.userID <> b.userID
  WHERE a.type = 'Cookie' AND a.isNew = 1
 GROUP BY a.userID, a.type
 UNION
 SELECT a.userID, a.type, COUNT(*) matches
 FROM wD_UserCodeConnections a
 INNER JOIN wD_UserCodeConnections b ON a.type = b.type AND a.code = b.code AND a.userID <> b.userID
  WHERE a.type = 'Cookie' AND b.isNew = 1
 GROUP BY a.userID, a.type
) rec ON rec.userID = uc.userId
SET countMatchedCookieUsers = countMatchedCookieUsers + rec.matches;
UPDATE wD_UserConnections uc
INNER JOIN (
 SELECT a.userID, a.type, COUNT(*) matches
 FROM wD_UserCodeConnections a
 INNER JOIN wD_UserCodeConnections b ON a.type = b.type AND a.code = b.code AND a.userID <> b.userID
  WHERE a.type = 'Fingerprint' AND a.isNew = 1
 GROUP BY a.userID, a.type
 UNION
 SELECT a.userID, a.type, COUNT(*) matches
 FROM wD_UserCodeConnections a
 INNER JOIN wD_UserCodeConnections b ON a.type = b.type AND a.code = b.code AND a.userID <> b.userID
  WHERE a.type = 'Fingerprint' AND b.isNew = 1
 GROUP BY a.userID, a.type
) rec ON rec.userID = uc.userId
SET countMatchedFingerprintUsers = countMatchedFingerprintUsers + rec.matches;
UPDATE wD_UserConnections uc
INNER JOIN (
 SELECT a.userID, a.type, COUNT(*) matches
 FROM wD_UserCodeConnections a
 INNER JOIN wD_UserCodeConnections b ON a.type = b.type AND a.code = b.code AND a.userID <> b.userID
  WHERE a.type = 'FingerprintPro' AND a.isNew = 1
 GROUP BY a.userID, a.type
 UNION
 SELECT a.userID, a.type, COUNT(*) matches
 FROM wD_UserCodeConnections a
 INNER JOIN wD_UserCodeConnections b ON a.type = b.type AND a.code = b.code AND a.userID <> b.userID
  WHERE a.type = 'FingerprintPro' AND b.isNew = 1
 GROUP BY a.userID, a.type
) rec ON rec.userID = uc.userId
SET countMatchedFingerprintProUsers = countMatchedFingerprintProUsers + rec.matches;


UPDATE wD_UserCodeConnections SET isNew = 0 WHERE isNew = 1;
		");

		$DB->sql_put("COMMIT");
	}

	// Finds and processes all games where all playing members excluding bots have voted for something
	static public function findAndApplyGameVotes()
	{
		global $DB;

		$tabl = $DB->sql_tabl("SELECT g.variantID, g.id, 
			CASE 
			WHEN DrawVotes = Voters THEN 'Draw'
			WHEN CancelVotes = Voters THEN 'Cancel'
			WHEN ConcedeVotes = Voters THEN 'Concede'
			WHEN PauseVotes = Voters THEN 'Pause'
			ELSE ''
			END Vote
			FROM (
				SELECT g.variantID, g.id,
					SUM(1) Voters,
					SUM(CASE WHEN (votes & 1 ) = 1 THEN 1 ELSE 0 END) DrawVotes, 
					SUM(CASE WHEN (votes & 2 ) = 2 THEN 1 ELSE 0 END) PauseVotes, 
					SUM(CASE WHEN (votes & 4 ) = 4 THEN 1 ELSE 0 END) CancelVotes, 
					SUM(CASE WHEN (votes & 8 ) = 8 THEN 1 ELSE 0 END) ConcedeVotes
				FROM wD_Games g
				INNER JOIN wD_Members m ON m.gameID = g.id
				INNER JOIN wD_Users u ON u.id = m.userID
				WHERE m.status = 'Playing' AND NOT u.`type` LIKE '%Bot%'
				AND g.phase <> 'Finished'
				GROUP BY g.id
			) g
			WHERE g.Voters = g.DrawVotes
			OR g.Voters = g.CancelVotes
			OR g.Voters = g.PauseVotes
			OR g.Voters = g.ConcedeVotes");
		$gameVotes = array();
		while(list($variantID, $gameID, $vote) = $DB->tabl_row($tabl))
		{
			$gameVotes[$gameID] = array('variantID'=>$variantID, 'name'=>$vote);
		}
		$DB->sql_put("COMMIT");
		$DB->sql_put("BEGIN");
		if( count($gameVotes) > 0 )
		{
			foreach($gameVotes as $gameID => $vote)
			{
				$DB->sql_put("BEGIN");
				$Variant=libVariant::loadFromVariantID($vote['variantID']);
				$Game = $Variant->processGame($gameID, UPDATE);
				$Game->applyVote($vote['name']);
				$DB->sql_put("COMMIT");
			}
		}
		$DB->sql_put("COMMIT");
	}
	// Finds all games where all users (incuding bots) with orders have set ready. It's similar to the function above
	// but there are enough differences to make it messy to combine
	static public function findGameReadyVotes()
	{
		global $DB;

		$tabl = $DB->sql_tabl("SELECT g.id
			FROM (
				SELECT g.id,
					SUM(1) Players,
					SUM(CASE WHEN (orderStatus & 1 ) = 1 THEN 1 ELSE 0 END) NoOrders, 
					/* AND NOT ((orderStatus & 1 ) = 1) accounts for cases like 'None,Ready' which can get set as an order status */
					SUM(CASE WHEN (orderStatus & 2 ) = 2 AND NOT ((orderStatus & 1 ) = 1) THEN 1 ELSE 0 END) SavedOrders, 
					SUM(CASE WHEN (orderStatus & 2 ) = 2 AND NOT ((orderStatus & 1 ) = 1) THEN 1 ELSE 0 END) CompletedOrders, 
					SUM(CASE WHEN (orderStatus & 8 ) = 8 AND NOT ((orderStatus & 1 ) = 1) THEN 1 ELSE 0 END) ReadyOrders
				FROM wD_Games g
				INNER JOIN wD_Members m ON m.gameID = g.id
				INNER JOIN wD_Users u ON u.id = m.userID
				WHERE m.status = 'Playing'
				AND g.gameOver = 'No' AND g.phase <> 'Pre-game' AND g.phase <> 'Finished'
				GROUP BY g.id
			) g
			WHERE (g.Players - g.NoOrders) <= g.ReadyOrders"); 
		// Everyone is ready, or only people with no orders arent ready
		
		$readyGames = array();
		while(list($gameID) = $DB->tabl_row($tabl))
		{
			$readyGames[] = $gameID;
		}
		return $readyGames;
	}
}

?>
