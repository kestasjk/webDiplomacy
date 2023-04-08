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
	 * To analyze lots of different data that might associate users without lots of separate queries into large datasets
	 * data is aggregated in this function into the UserCodeConnections and UserConnections table.
	 * 
	 * UserCodeConnections contains for each user, for each type of code (IP, Cookie, Fingerprint, etc); the code, the 
	 * number of times that code has been seen, and the first and last time it was seen.
	 * e.g. if a user had an IP 1.2.3.4 since 2019 until 2021 and made 10000 requests during that period there would be
	 * a record in the UserCodeConnections table for that IP that would total to the 10000 hits, with a range from 2019 to 2021.
	 * 
	 * When new records are added they are inserted into UserCodeConnections, and if any duplicate codes that already exist
	 * are present the record will be updated with the new count and timestamp.
	 * 
	 * UserTurn: Links to other userIDs that the user has had a game turn with, for spotting users that always play together
	 * UserTurnMissed: Same as UserTurn, but for missed turns that both user accounts missed
	 * LatLon/City/Region/Network: Contains data from the IP lookup information, to link users by IP location
	 * Fingerprint/FingerprintPro: Links based on the fingerprint.js library / fingerprint pro
	 * Cookie: Links based on the cookie code that gets set
	 * 
	 * These are collected in a different function below from wD_GameMessages:
	 * MessageCount: Links to other userIDs that this user messaged, for spotting users with oddly high/low correspondence
	 * MessageLength: As above but for character count
	 * 
	 * UserConnections is also updated from the AccessLog with the period during the day that the account logged on, to detect
 	 * users that log in at similar times, or that are never online at a particular time e.g. early morning.


     * UserConnections contains a record for each user which summarizes how many connections with other users, what time of day
     * the user is typically online, and how many matches of each type of code (IP, Cookie, Fingerprint, etc) have been found.
     * This can be used with the UserCodeConnection records to tell whether a code connection is very odd given the user's total
     * stats.
     * e.g. if a user matches cookie codes with 5 other users but has used 10000 cookie codes over 15 years that's not a strong 
     * link, but if a user matches cookie codes with 5 other users and only has 5 cookie codes that's a very strong link.
     * 
     * countCookie contains the number of different cookie codes the user has used
     * countCookieTotal contains the number of times the user has used a cookie code
     * matchedCookie contains the number of other users that the user has matched cookie codes with
     * matchedCookieTotal contains the total number of times the user used the cookie code that matched another user
     * 
     * So someone might have countCookie = 1000, countCookieTotal = 100000000, matchedCookie = 5, matchedCookieTotal = 5000
     * and that wouldn't be a very strong indication, but if another user has countCookie = 5, countCookieTotal = 5000,
     * matchedCookie = 5, matchedCookieTotal = 5000 then that would be a very strong indication.
 * @package GameMaster
 */
class libUserConnections
{
    // Generate the SQL to aggregate each of the user code match summary columns, which aggregates up UserCodeConnections into UserConnections
	static private function userConnectionAggregateSQL()
	{
		$sql = "";
        // Codes to aggregate totals for
		$codes = array('Cookie','IP','IPVPN','Fingerprint','FingerprintPro','LatLon','Network','City','UserTurn','UserTurnMissed','MessageLength','MessageCount');
        // Codes to also look for matches for (matching all users who are in the same region / have played with the same user wouldn't be useful)
        $matchCodes = array('Cookie','IP','Fingerprint','FingerprintPro','UserTurnMissed','UserTurn');
		foreach($codes as $codeType)
		{
			$sql .= "
            /* Add any newly found codes to the count, and the updated sum to the total count */
			UPDATE wD_UserConnections uc
			INNER JOIN (
                SELECT a.userID, a.type, SUM(isNew) codes, SUM(count-previousCount) codesCount
                FROM wD_UserCodeConnections a
                WHERE a.type = '".$codeType."' AND a.isUpdated = 1
                GROUP BY a.userID, a.type
			) rec ON rec.userID = uc.userId
			SET count".$codeType." = count".$codeType." + rec.codes, count".$codeType."Total = count".$codeType."Total + rec.codesCount;
            ";
            
            if( in_array($codeType, $matchCodes) )
            {

                $sql .= "
                /* Find any new matches between users, and add them to the matched code table. */
                INSERT INTO wD_UserCodeConnectionMatches (type, userIDFrom, userIDTo, matches, matchCount)
                SELECT a.type, a.userID userIDFrom, b.userID userIDTo, SUM(a.isNew) matches, SUM(a.count-a.previousCount) matchCount
                FROM wD_UserCodeConnections a
                INNER JOIN wD_UserCodeConnections b ON a.type = b.type AND a.code = b.code AND a.userID <> b.userID
                WHERE a.type = '".$codeType."' AND a.isUpdated = 1
                GROUP BY a.userID, b.userID, a.type
                ON DUPLICATE KEY UPDATE matches = matches + VALUES(matches), matchCount = matchCount + VALUES(matchCount), isUpdated = 1;
                
                /* Also add the reverse matches; this will double the match counts but that's fine */
                INSERT INTO wD_UserCodeConnectionMatches (type, userIDFrom, userIDTo, matches, matchCount)
                SELECT b.type, b.userID userIDFrom, a.userID userIDTo, SUM(a.isNew) matches, SUM(a.count-a.previousCount) matchCount
                FROM wD_UserCodeConnections a
                INNER JOIN wD_UserCodeConnections b ON a.type = b.type AND a.code = b.code AND a.userID <> b.userID
                WHERE a.type = '".$codeType."' AND a.isUpdated = 1
                GROUP BY a.userID, b.userID, a.type
                ON DUPLICATE KEY UPDATE matches = matches + VALUES(matches), matchCount = matchCount + VALUES(matchCount), isUpdated = 1;
                ";
            }

			$sql .= "
            UPDATE wD_UserCodeConnectionMatches SET isUpdated = 0, isNew = 0, previousMatches = matches, previousMatchCount = matchCount WHERE isUpdated = 1 AND type = '".$codeType."';
            UPDATE wD_UserCodeConnections SET isUpdated = 0, isNew = 0, previousCount = count WHERE isUpdated = 1 AND type = '".$codeType."';

            COMMIT;
			";
		}
		
		return $sql;
	}

    private static $excludedUserIDCSVListCache = null;
    /**
     * A comma separated list of user IDs that shouldn't be included in the user connections, for performance and to reduce
     * the number of false positives for mods
     */
    static private function getExcludedUserIDCSVList()
    {
		global $DB;
        if( is_null(self::$excludedUserIDCSVListCache) )
        {
            $excludeUserIDs = [0,1];
            $tabl = $DB->sql_tabl("SELECT id FROM wD_Users WHERE type LIKE '%Bot%'");
            while(list($userID) = $DB->tabl_row($tabl))
            $excludeUserIDs[] = $userID;
            self::$excludedUserIDCSVListCache = implode(',',$excludeUserIDs);
        }
        return self::$excludedUserIDCSVListCache;
    }
	
	static public function updateUserConnections($lastUpdate = 0)
	{
		global $DB;

        $DB->sql_put("COMMIT");	
		if( $lastUpdate == 0 )
		{
			$DB->sql_put("TRUNCATE TABLE wD_UserConnections");	
			$DB->sql_put("TRUNCATE TABLE wD_UserCodeConnectionMatches");
			$DB->sql_put("TRUNCATE TABLE wD_UserCodeConnections");
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
        /* Generate data from wD_AccessLog */
		INSERT INTO wD_UserConnections (userID)
SELECT userID
FROM (
 SELECT userID, HOUR(lastRequest) h, SUM(hits) c
 FROM wD_AccessLog
 WHERE lastRequest >= FROM_UNIXTIME(".$lastUpdate.")
 GROUP BY userID, HOUR(lastRequest)
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
ON DUPLICATE KEY UPDATE isUpdated=1, earliest=least(earliestRequest, earliest), latest=greatest(latestRequest, latest), count=count+requestCount;

INSERT INTO wD_UserCodeConnections (userID, type, code, earliest, latest, count)
SELECT userID, type, code , earliestRequest, latestRequest, requestCount
FROM (
 SELECT userID, 'IP' type, ip code, MIN(lastRequest) earliestRequest, MAX(lastRequest) latestRequest, SUM(hits) requestCount
 FROM wD_AccessLog
 WHERE lastRequest >= FROM_UNIXTIME(".$lastUpdate.")
 GROUP BY userID, ip
) r
ON DUPLICATE KEY UPDATE isUpdated=1, earliest=least(earliestRequest, earliest), latest=greatest(latestRequest, latest), count=count+requestCount;

INSERT INTO wD_UserCodeConnections (userID, type, code, earliest, latest, count)
SELECT userID, type, code , earliestRequest, latestRequest, requestCount
FROM (
 SELECT a.userID, 'IPVPN' type, ip code, MIN(a.earliest) earliestRequest, MAX(a.latest) latestRequest, SUM(a.count) requestCount
 FROM wD_UserCodeConnections a
 INNER JOIN wD_IPLookups u ON a.code = u.ipCode
 WHERE a.type='IPVPN' AND u.timeLookedUp >= ".$lastUpdate."
 GROUP BY a.userID, u.region
) r
ON DUPLICATE KEY UPDATE isUpdated=1, earliest=least(earliestRequest, earliest), latest=greatest(latestRequest, latest), count=count+requestCount;

INSERT INTO wD_UserCodeConnections (userID, type, code, earliest, latest, count)
SELECT userID, type, code , earliestRequest, latestRequest, requestCount
FROM (
 SELECT userID, 'Fingerprint' type, browserFingerprint code, MIN(lastRequest) earliestRequest, MAX(lastRequest) latestRequest, SUM(hits) requestCount
 FROM wD_AccessLog
 WHERE lastRequest >= FROM_UNIXTIME(".$lastUpdate.") AND browserFingerprint IS NOT NULL AND browserFingerprint <> UNHEX('00000000000000000000000000000000')
 GROUP BY userID, browserFingerprint
) r
ON DUPLICATE KEY UPDATE isUpdated=1, earliest=least(earliestRequest, earliest), latest=greatest(latestRequest, latest), count=count+requestCount;

/* Done with AccessLog, start on IPLookups */
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
ON DUPLICATE KEY UPDATE isUpdated=1, earliest=least(earliestRequest, earliest), latest=greatest(latestRequest, latest), count=count+requestCount;

INSERT INTO wD_UserCodeConnections (userID, type, code, earliest, latest, count)
SELECT userID, type, code , earliestRequest, latestRequest, requestCount
FROM (
 SELECT a.userID, 'City' type, LEFT(u.city,16) code, MIN(a.earliest) earliestRequest, MAX(a.latest) latestRequest, SUM(a.count) requestCount
 FROM wD_UserCodeConnections a
 INNER JOIN wD_IPLookups u ON a.code = u.ipCode
 WHERE a.type='IP' AND u.timeLookedUp >= ".$lastUpdate."
 GROUP BY a.userID, u.city
) r
ON DUPLICATE KEY UPDATE isUpdated=1, earliest=least(earliestRequest, earliest), latest=greatest(latestRequest, latest), count=count+requestCount;

INSERT INTO wD_UserCodeConnections (userID, type, code, earliest, latest, count)
SELECT userID, type, code , earliestRequest, latestRequest, requestCount
FROM (
 SELECT a.userID, 'Network' type, UNHEX(LPAD(REPLACE(REPLACE(REPLACE(network,':',''),'/',''),'.',''),16,'0')) code, MIN(a.earliest) earliestRequest, MAX(a.latest) latestRequest, SUM(a.count) requestCount
 FROM wD_UserCodeConnections a
 INNER JOIN wD_IPLookups u ON a.code = u.ipCode
 WHERE a.type='IP' AND u.timeLookedUp >= ".$lastUpdate."
 GROUP BY a.userID, u.network
) r
ON DUPLICATE KEY UPDATE isUpdated=1, earliest=least(earliestRequest, earliest), latest=greatest(latestRequest, latest), count=count+requestCount;

/* Done with IPLookups, enter fingerprint pro lookups */
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
ON DUPLICATE KEY UPDATE isUpdated=1, earliest=least(earliestRequest, earliest), latest=greatest(latestRequest, latest), count=count+requestCount;

/* Enter turns shared */
DROP TABLE IF EXISTS wD_Tmp_TurnCount;
CREATE TABLE wD_Tmp_TurnCount
SELECT a.userID, a.gameID*1000 + a.turn gameIDTurn, b.userID otherUserID, MIN(a.turnDateTime) earliestT, MAX(a.turnDateTime) latestT, COUNT(*) tCount
  FROM wD_TurnDate a
  INNER JOIN wD_TurnDate b ON a.gameID = b.gameID AND a.userID <> b.userID AND a.turnDateTime = b.turnDateTime
  WHERE a.turnDateTime >= ".$lastUpdate." AND b.turnDateTime >= ".$lastUpdate."
  GROUP BY a.userID, a.gameID, a.turn, b.userID;

  DELETE FROM wD_Tmp_TurnCount WHERE userID IN (".self::getExcludedUserIDCSVList().") OR otherUserID IN (".self::getExcludedUserIDCSVList().");

  INSERT INTO wD_UserCodeConnections (userID, type, code, earliest, latest, count)
  SELECT userID, 'UserTurn' type, UNHEX(LPAD(CONV(gameIDTurn,10,16),16,'0')), FROM_UNIXTIME(earliestT), FROM_UNIXTIME(latestT), tCount
  FROM wD_Tmp_TurnCount r
  ON DUPLICATE KEY UPDATE isUpdated=1, earliest=least(FROM_UNIXTIME(earliestT), earliest), latest=greatest(FROM_UNIXTIME(latestT), latest), count=count+tCount;

  /* Enter missed turns shared */  
  DROP TABLE IF EXISTS wD_Tmp_MissedTurnCount;
  CREATE TABLE wD_Tmp_MissedTurnCount
  SELECT a.userID, a.gameID*1000 + a.turn gameIDTurn, b.userID otherUserID, MIN(a.turnDateTime) earliestT, MAX(a.turnDateTime) latestT, COUNT(*) tCount
	FROM  wD_MissedTurns  a
	INNER JOIN  wD_MissedTurns  b ON a.gameID = b.gameID AND a.userID <> b.userID AND a.turnDateTime = b.turnDateTime
	WHERE a.turnDateTime >= ".$lastUpdate." AND b.turnDateTime >= ".$lastUpdate."
	GROUP BY a.userID, a.gameID, a.turn, b.userID;

    DELETE FROM wD_Tmp_MissedTurnCount WHERE userID IN (".self::getExcludedUserIDCSVList().") OR otherUserID IN (".self::getExcludedUserIDCSVList().");
        
    INSERT INTO wD_UserCodeConnections (userID, type, code, earliest, latest, count)
    SELECT userID, 'UserTurnMissed' type, UNHEX(LPAD(CONV(gameIDTurn,10,16),16,'0')), FROM_UNIXTIME(earliestT), FROM_UNIXTIME(latestT), tCount
    FROM wD_Tmp_MissedTurnCount r
    ON DUPLICATE KEY UPDATE isUpdated=1, earliest=least(FROM_UNIXTIME(earliestT), earliest), latest=greatest(FROM_UNIXTIME(latestT), latest), count=count+tCount;

COMMIT;");

    // Filter out user IDs that shouldn't trigger connections before aggregating, as including e.g. bots will cause huge queries 
    // linking everyone with everyone  that take a very long time
    $DB->sql_put("DELETE FROM wD_UserCodeConnections WHERE isUpdated = 1 AND userID IN (".self::getExcludedUserIDCSVList().")");
    $DB->sql_put("COMMIT");	
    
    // Aggregate all the new UserCodeConnections into the summary tables UserConnections and UserCodeConnectionMatches
    $DB->sql_script(self::userConnectionAggregateSQL());
    $DB->sql_put("COMMIT");
	}

    /**
     * Update the UserCodeConnections records for messages counts/lengths between players (this is aggregated by )
     */
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
  SELECT m1.userID userID, m2.userID otherUserID, FROM_UNIXTIME(MIN(timeSent)) earliestM, FROM_UNIXTIME(MAX(timeSent))  latestM, SUM(CHAR_LENGTH(g.message) ) countMLen, COUNT(*) countM
  FROM wD_GameMessages g
  INNER JOIN wD_Members m1 ON m1.gameID = g.gameID AND m1.countryID = g.fromCountryID
  INNER JOIN wD_Members m2 ON m2.gameID = g.gameID AND m2.countryID = g.toCountryID
  WHERE g.id > ".$Misc->LastMessageID." AND g.id <= ".$lastMessageID."
  GROUP BY m1.userID, m2.userID;

  DELETE FROM wD_Tmp_MessageCount WHERE userID IN (".self::getExcludedUserIDCSVList().") OR otherUserID IN (".self::getExcludedUserIDCSVList().");

INSERT INTO wD_UserCodeConnections (userID, type, code, earliest, latest, count)
SELECT userID, 'MessageLength' type, UNHEX(LPAD(CONV(otherUserID,10,16),16,'0')) code , earliestM, latestM, countMLen
FROM wD_Tmp_MessageCount r
ON DUPLICATE KEY UPDATE isUpdated=1, earliest=least(r.earliestM, earliest), latest=greatest(r.latestM, latest), count=count+r.countM;

INSERT INTO wD_UserCodeConnections (userID, type, code, earliest, latest, count)
SELECT userID, 'MessageCount' type, UNHEX(LPAD(CONV(otherUserID,10,16),16,'0')) code , earliestM, latestM, countM
FROM wD_Tmp_MessageCount r
ON DUPLICATE KEY UPDATE isUpdated=1, earliest=least(r.earliestM, earliest), latest=greatest(r.latestM, latest), count=count+r.countM;
");
			$Misc->LastMessageID = $lastMessageID;
			$Misc->write();
			$DB->sql_put("COMMIT");
			$DB->sql_put("BEGIN");
		}
	}
}