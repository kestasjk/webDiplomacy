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

		$DB->sql_put("COMMIT");
		$DB->sql_put("BEGIN");
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

	// Finds and processes all games where all playing members excluding bots have voted for something
	// A gameID can be given because the setVote function expects a game with a vote set to be processed
	// immidiately and it changing it might break the beta UI. 
	// TODO: Test whether votes need to be processed immidiately via api.php or if they can wait for gamemaster to run
	static public function findAndApplyGameVotes($gameID = -1)
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
				AND g.phase <> 'Finished' ".($gameID != -1 ? "AND g.id = ".$gameID : "")."
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
