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
				( userID, firstRequest, lastRequest, hits, ip, userAgent, cookieCode, browserFingerprint )
				SELECT userID, firstRequest, lastRequest, hits, ip, userAgent, cookieCode, browserFingerprint
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
	static public function updateReliabilityRatings($recalculateAll = false, $recalculateUsers = false)
	{
		global $DB;

		//-- Careful, the below is carefully optimized to use the indexes in the best way, small changes can make this v slow:

		if( $recalculateAll || is_array($recalculateUsers) )
		{
			$whereClause = "";
			if( is_array($recalculateUsers) )
			{
				$whereClause = " WHERE userID IN (".implode(",", $recalculateUsers).") ";
			}
			// Recalculating everything; set all turns younger than a year to be in reliability period, and count all the phases younger than a year for each user
			$DB->sql_put("UPDATE wD_TurnDate SET isInReliabilityPeriod = CASE WHEN turnDateTime>UNIX_TIMESTAMP() - 60*60*24*365 THEN 1 ELSE 0 END;");

			$DB->sql_put("UPDATE wD_Users u LEFT JOIN (
					SELECT userID, COUNT(1) yearlyPhaseCount 
					FROM wD_TurnDate 
					WHERE isInReliabilityPeriod = 1 
					GROUP BY userID
				) phases ON phases.userID = u.id 
				SET u.yearlyPhaseCount = COALESCE(phases.yearlyPhaseCount,0),
					u.isPhasesDirty = 1 ".
			$whereClause.";");
				
			// Do the same for missed turns, which is more complicated as there are several different reliability periods:
			$DB->sql_put("UPDATE wD_MissedTurns 
				SET reliabilityPeriod = CASE 
						WHEN turnDateTime > UNIX_TIMESTAMP() - 7*24*60*60 THEN 3 
						WHEN turnDateTime > UNIX_TIMESTAMP() - 28*24*60*60 THEN 2 
						WHEN turnDateTime > UNIX_TIMESTAMP() - 365*24*60*60 THEN 1 
						ELSE 0
				END ".
			$whereClause.";");

			// Set the week period missed turns to the users
			$DB->sql_put("UPDATE wD_Users u LEFT JOIN (
					SELECT userID, reliabilityPeriod,
						SUM(CASE WHEN liveGame = 0 AND samePeriodExcused = 0 AND systemExcused = 0 AND modExcused = 0 THEN 1 ELSE 0 END) nonLiveNew,
						SUM(CASE WHEN liveGame = 1 AND samePeriodExcused = 0 AND systemExcused = 0 AND modExcused = 0 THEN 1 ELSE 0 END) liveNew,
						SUM(CASE WHEN modExcused = 0 THEN 1 ELSE 0 END) totalNew 
					FROM wD_MissedTurns
					WHERE reliabilityPeriod = 3
					GROUP BY userID, reliabilityPeriod
				) phases ON phases.userID = u.id
				SET u.missedPhasesLiveLastWeek = COALESCE(phases.liveNew,0),
					u.missedPhasesNonLiveLastWeek = COALESCE(phases.nonLiveNew,0),
					u.missedPhasesTotalLastWeek = COALESCE(phases.totalNew,0),
					u.isPhasesDirty = 1 ".
			$whereClause.";");
					
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
				u.isPhasesDirty = 1 ".
			$whereClause.";");
				
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
				u.isPhasesDirty = 1 ".
			$whereClause.";");
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

	// Generate the SQL to aggregate each of the user code match summary columns
	static private function userConnectionAggregateSQL()
	{
		$sql = "";
		$codes = array('Cookie','IP','Fingerprint','FingerprintPro','LatLon','Network','City','UserTurn','Region','MessageLength','MessageCount');
		foreach($codes as $code)
		{
			$sql .= "
			UPDATE wD_UserConnections uc
			INNER JOIN (
			 SELECT a.userID, a.type, COUNT(*) matches, SUM(a.count) matchCount
			 FROM wD_UserCodeConnections a
			 INNER JOIN wD_UserCodeConnections b ON a.type = b.type AND a.code = b.code AND a.userID <> b.userID
			  WHERE a.type = '".$code."' AND a.isNew = 1
			 GROUP BY a.userID, a.type
			 UNION
			  SELECT a.userID, a.type, COUNT(*) matches, SUM(a.count) matchCount
			 FROM wD_UserCodeConnections a
			 INNER JOIN wD_UserCodeConnections b ON a.type = b.type AND a.code = b.code AND a.userID <> b.userID
			  WHERE a.type = '".$code."' AND b.isNew = 1
			 GROUP BY a.userID, a.type
			) rec ON rec.userID = uc.userId
			SET matched".$code." = matched".$code." + rec.matches, matched".$code."Total = matched".$code."Total + rec.matchCount;
			
			UPDATE wD_UserConnections uc
			INNER JOIN (
			SELECT a.userID, a.type, COUNT(*) codes, SUM(count) codesCount
			FROM wD_UserCodeConnections a
			WHERE a.type = '".$code."' AND a.isNew = 1
			GROUP BY a.userID, a.type
			) rec ON rec.userID = uc.userId
			SET count".$code." = count".$code." + rec.codes, count".$code."Total = count".$code."Total + rec.codesCount;
			";
		}
		
		return $sql;
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
 period0    = period0    + IF(h=0  OR h=1  OR h=2 ,c,0),
 period1    = period1    + IF(h=1  OR h=2  OR h=3 ,c,0),
 period2    = period2    + IF(h=2  OR h=3  OR h=4 ,c,0),
 period3    = period3    + IF(h=3  OR h=4  OR h=5 ,c,0),
 period4    = period4    + IF(h=4  OR h=5  OR h=6 ,c,0),
 period5    = period5    + IF(h=5  OR h=6  OR h=7 ,c,0),
 period6    = period6    + IF(h=6  OR h=7  OR h=8 ,c,0),
 period7    = period7    + IF(h=7  OR h=8  OR h=9 ,c,0),
 period8    = period8    + IF(h=8  OR h=9  OR h=10,c,0),
 period9    = period9    + IF(h=9  OR h=10 OR h=11,c,0),
 period10   = period10   + IF(h=10 OR h=11 OR h=12,c,0),
 period11   = period11   + IF(h=11 OR h=12 OR h=13,c,0),
 period12   = period12   + IF(h=12 OR h=13 OR h=14,c,0),
 period13   = period13   + IF(h=13 OR h=14 OR h=15,c,0),
 period14   = period14   + IF(h=14 OR h=15 OR h=16,c,0),
 period15   = period15   + IF(h=15 OR h=16 OR h=17,c,0),
 period16   = period16   + IF(h=16 OR h=17 OR h=18,c,0),
 period17   = period17   + IF(h=17 OR h=18 OR h=19,c,0),
 period18   = period18   + IF(h=18 OR h=19 OR h=20,c,0),
 period19   = period19   + IF(h=19 OR h=20 OR h=21,c,0),
 period20   = period20   + IF(h=20 OR h=21 OR h=22,c,0),
 period21   = period21   + IF(h=21 OR h=22 OR h=23,c,0),
 period22   = period22   + IF(h=22 OR h=23 OR h=0 ,c,0),
 period23   = period23   + IF(h=23 OR h=0  OR h=1 ,c,0),
 totalHits = totalHits + c
 ;
 
  INSERT INTO wD_UserCodeConnections (userID, type, code, earliest, latest, count)
SELECT userID, type, code , earliestRequest, latestRequest, requestCount
FROM (
 SELECT userID, 'Cookie' type, cookieCode code, MIN(lastRequest) earliestRequest, MAX(lastRequest) latestRequest, SUM(hits) requestCount
 FROM wD_AccessLog
 WHERE lastRequest >= FROM_UNIXTIME(".$lastUpdate.")
 GROUP BY userID, cookieCode
) r
ON DUPLICATE KEY UPDATE isNew=1, earliest=least(earliestRequest, earliest), latest=greatest(latestRequest, latest), count=count+requestCount;

INSERT INTO wD_UserCodeConnections (userID, type, code, earliest, latest, count)
SELECT userID, type, code , earliestRequest, latestRequest, requestCount
FROM (
 SELECT userID, 'IP' type, ip code, MIN(lastRequest) earliestRequest, MAX(lastRequest) latestRequest, SUM(hits) requestCount
 FROM wD_AccessLog
 WHERE lastRequest >= FROM_UNIXTIME(".$lastUpdate.")
 GROUP BY userID, ip
) r
ON DUPLICATE KEY UPDATE isNew=1, earliest=least(earliestRequest, earliest), latest=greatest(latestRequest, latest), count=count+requestCount;

INSERT INTO wD_UserCodeConnections (userID, type, code, earliest, latest, count)
SELECT userID, type, code , earliestRequest, latestRequest, requestCount
FROM (
	/* Round lat/lon to 1 decimal place */
 SELECT a.userID, 'LatLon' type, UNHEX(LPAD(CONV(ROUND((u.latitude+90.0)*10,0)*10000+ROUND((u.longitude+180.0)*10,0),10,16),16,'0')) code, MIN(a.earliest) earliestRequest, MAX(a.latest) latestRequest, SUM(a.count) requestCount
 FROM wD_UserCodeConnections a
 INNER JOIN wD_IPLookups u ON a.code = u.ipCode
 WHERE a.type='IP' AND u.timeLookedUp >= ".$lastUpdate."
 GROUP BY a.userID, a.code
) r
ON DUPLICATE KEY UPDATE isNew=1, earliest=least(earliestRequest, earliest), latest=greatest(latestRequest, latest), count=count+requestCount;

INSERT INTO wD_UserCodeConnections (userID, type, code, earliest, latest, count)
SELECT userID, type, code , earliestRequest, latestRequest, requestCount
FROM (
 SELECT a.userID, 'City' type, LEFT(u.city,16) code, MIN(a.earliest) earliestRequest, MAX(a.latest) latestRequest, SUM(a.count) requestCount
 FROM wD_UserCodeConnections a
 INNER JOIN wD_IPLookups u ON a.code = u.ipCode
 WHERE a.type='IP' AND u.timeLookedUp >= ".$lastUpdate."
 GROUP BY a.userID, u.city
) r
ON DUPLICATE KEY UPDATE isNew=1, earliest=least(earliestRequest, earliest), latest=greatest(latestRequest, latest), count=count+requestCount;

INSERT INTO wD_UserCodeConnections (userID, type, code, earliest, latest, count)
SELECT userID, type, code , earliestRequest, latestRequest, requestCount
FROM (
 SELECT a.userID, 'Region' type, LEFT(u.region,16) code, MIN(a.earliest) earliestRequest, MAX(a.latest) latestRequest, SUM(a.count) requestCount
 FROM wD_UserCodeConnections a
 INNER JOIN wD_IPLookups u ON a.code = u.ipCode
 WHERE a.type='IP' AND u.timeLookedUp >= ".$lastUpdate."
 GROUP BY a.userID, u.region
) r
ON DUPLICATE KEY UPDATE isNew=1, earliest=least(earliestRequest, earliest), latest=greatest(latestRequest, latest), count=count+requestCount;

INSERT INTO wD_UserCodeConnections (userID, type, code, earliest, latest, count)
SELECT userID, type, code , earliestRequest, latestRequest, requestCount
FROM (
 SELECT a.userID, 'Network' type, UNHEX(LPAD(REPLACE(REPLACE(REPLACE(network,':',''),'/',''),'.',''),16,'0')) code, MIN(a.earliest) earliestRequest, MAX(a.latest) latestRequest, SUM(a.count) requestCount
 FROM wD_UserCodeConnections a
 INNER JOIN wD_IPLookups u ON a.code = u.ipCode
 WHERE a.type='IP' AND u.timeLookedUp >= ".$lastUpdate."
 GROUP BY a.userID, u.network
) r
ON DUPLICATE KEY UPDATE isNew=1, earliest=least(earliestRequest, earliest), latest=greatest(latestRequest, latest), count=count+requestCount;

INSERT INTO wD_UserCodeConnections (userID, type, code, earliest, latest, count)
SELECT userID, type, code , earliestRequest, latestRequest, requestCount
FROM (
 SELECT userID, 'Fingerprint' type, browserFingerprint code, MIN(lastRequest) earliestRequest, MAX(lastRequest) latestRequest, SUM(hits) requestCount
 FROM wD_AccessLog
 WHERE lastRequest >= FROM_UNIXTIME(".$lastUpdate.")
 AND browserFingerprint IS NOT NULL
 GROUP BY userID, browserFingerprint
) r
ON DUPLICATE KEY UPDATE isNew=1, earliest=least(earliestRequest, earliest), latest=greatest(latestRequest, latest), count=count+requestCount;

INSERT INTO wD_UserCodeConnections (userID, type, code, earliest, latest, count)
SELECT userID, type, code , earliestRequest, latestRequest, requestCount
FROM (
 SELECT linkedId userId, 'FingerprintPro' type, 
	FROM_BASE64(visitorId) code, MIN(FROM_UNIXTIME(CAST(LEFT(requestId,10) AS INT))) earliestRequest, 
	MAX(FROM_UNIXTIME(CAST(LEFT(requestId,10) AS INT))) latestRequest, 
	COUNT(*) requestCount
  FROM wD_FingerprintProRequests f
  WHERE CAST(LEFT(requestId,10) AS INT) >= ".$lastUpdate."
  GROUP BY linkedID, visitorID
) r
ON DUPLICATE KEY UPDATE isNew=1, earliest=least(earliestRequest, earliest), latest=greatest(latestRequest, latest), count=count+requestCount;

DROP TABLE IF EXISTS wD_Tmp_TurnCount;
CREATE TABLE wD_Tmp_TurnCount
SELECT a.userID, b.userID code, MIN(a.turnDateTime) earliestT, MAX(a.turnDateTime) latestT, COUNT(*) tCount
  FROM wD_TurnDate a
  INNER JOIN wD_TurnDate b ON a.gameID = b.gameID AND a.userID <> b.userID AND a.turnDateTime = b.turnDateTime
  WHERE a.turnDateTime >= ".$lastUpdate." AND b.turnDateTime >= ".$lastUpdate."
  GROUP BY a.userID, b.userID;

  INSERT INTO wD_UserCodeConnections (userID, type, code, earliest, latest, count)
  SELECT userID, 'UserTurn' type, UNHEX(LPAD(CONV(code,10,16),16,'0')), FROM_UNIXTIME(earliestT), FROM_UNIXTIME(latestT), tCount
  FROM wD_Tmp_TurnCount r
  ON DUPLICATE KEY UPDATE isNew=1, earliest=least(FROM_UNIXTIME(earliestT), earliest), latest=greatest(FROM_UNIXTIME(latestT), latest), count=count+tCount;
  
  DROP TABLE IF EXISTS wD_Tmp_MissedTurnCount;
  CREATE TABLE wD_Tmp_MissedTurnCount
  SELECT a.userID, b.userID code, MIN(a.turnDateTime) earliestT, MAX(a.turnDateTime) latestT, COUNT(*) tCount
	FROM  wD_MissedTurns  a
	INNER JOIN  wD_MissedTurns  b ON a.gameID = b.gameID AND a.userID <> b.userID AND a.turnDateTime = b.turnDateTime
	WHERE a.turnDateTime >= ".$lastUpdate." AND b.turnDateTime >= ".$lastUpdate."
	GROUP BY a.userID, b.userID;
    
INSERT INTO wD_UserCodeConnections (userID, type, code, earliest, latest, count)
SELECT userID, 'UserTurnMissed' type, UNHEX(LPAD(CONV(code,10,16),16,'0')), FROM_UNIXTIME(earliestT), FROM_UNIXTIME(latestT), tCount
FROM wD_Tmp_MissedTurnCount r
ON DUPLICATE KEY UPDATE isNew=1, earliest=least(FROM_UNIXTIME(earliestT), earliest), latest=greatest(FROM_UNIXTIME(latestT), latest), count=count+tCount;

 INSERT INTO wD_UserConnections (userID)
 SELECT DISTINCT u.userID 
 FROM wD_UserCodeConnections u
 LEFT JOIN wD_UserConnections c ON c.userID = u.userID
 WHERE u.isNew = 1 AND c.userID IS NULL;

 ".self::userConnectionAggregateSQL()."

UPDATE wD_UserCodeConnections SET isNew = 0 WHERE isNew = 1;
		");

		$DB->sql_put("COMMIT");
	}

	static public function updateGameMessageStats()
	{
		global $Misc, $DB;

		list($lastMessageID) = $DB->sql_row("SELECT MAX(id) FROM wD_GameMessages");

		if( $lastMessageID > ( $Misc->LastMessageID + 100 ) )
		{
			// If there are more than 100 messages to process merge these new messages into the user connection stats
			$DB->sql_put("COMMIT");
			$DB->sql_put("BEGIN");
			$DB->sql_script("
DROP TABLE IF EXISTS wD_Tmp_MessageCount;

CREATE TABLE wD_Tmp_MessageCount
  SELECT m1.userID userID, m2.userID code, FROM_UNIXTIME(MIN(timeSent)) earliestM, FROM_UNIXTIME(MAX(timeSent))  latestM, SUM(CHAR_LENGTH(g.message) ) countMLen, COUNT(*) countM
  FROM wD_GameMessages g
  INNER JOIN wD_Members m1 ON m1.gameID = g.gameID AND m1.countryID = g.fromCountryID
  INNER JOIN wD_Members m2 ON m2.gameID = g.gameID AND m2.countryID = g.toCountryID
  WHERE g.id > ".$Misc->LastMessageID." AND g.id <= ".$lastMessageID."
  GROUP BY m1.userID, m2.userID;

INSERT INTO wD_UserCodeConnections (userID, type, code, earliest, latest, count)
SELECT userID, 'MessageLength' type, UNHEX(LPAD(CONV(code,10,16),16,'0')) code , earliestM, latestM, countM
FROM wD_Tmp_MessageCount r
ON DUPLICATE KEY UPDATE isNew=1, earliest=least(r.earliestM, earliest), latest=greatest(r.latestM, latest), count=count+r.countM;

INSERT INTO wD_UserCodeConnections (userID, type, code, earliest, latest, count)
SELECT userID, 'MessageCount' type, UNHEX(LPAD(CONV(code,10,16),16,'0')) code , earliestM, latestM, countM
FROM wD_Tmp_MessageCount r
ON DUPLICATE KEY UPDATE isNew=1, earliest=least(r.earliestM, earliest), latest=greatest(r.latestM, latest), count=count+r.countM;
");
			$Misc->LastMessageID = $lastMessageID;
			$Misc->write();
			$DB->sql_put("COMMIT");
			$DB->sql_put("BEGIN");
		}
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
