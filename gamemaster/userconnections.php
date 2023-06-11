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
	 * Judging and Matching Optimizer
     * ------------------------------
     * Judging and Matching Optimizer
 * ==============================
 * 
 * Data-structures and processing
 * ==============================
 * This section gives an example going from the collected data to the aggregated data. This is stored across three tables:
 * wD_UserCodeConnections - For each user, code type, and code it has the earliest and latest use of that code, and a count of times it was used
 * wD_UserCodeConnectionMatches - For each code type and pair of users which both used the same code contains how many codes matched, and how many times that code was used
 * wD_UserConnections - For each user has a summary of the counts and matches for each code type
 * 
 * This data is aggregated and collected by running gamemaster.php from the commandline with the argument CONNECTIONUPDATE. 
 * It uses wD_Misc.LastConnectionUpdate to know what time it should start collecting new codes from (if 0 it will start from scratch),
 * and uses wD_Misc.LastMessageID to know what game message it should start collecting new message data from.
 * The script takes 10-20 minutes to run from scratch against a large webDiplomacy.net sized system, but every time it runs subsequently
 * it only processes new records and runs very quickly. The datasets generated can be searched and analyzed without locking any live 
 * datasets.
 * 
 * 
 * wD_UserCodeConnections
 * ----------------------
 * The count of hits per user per code, with the earliest and latest instance of the code use. Data is collected from the
 * access logs, game membership records, NMR records, game messages, and aggregated into this table.
 * 
 * SELECT type, userID, HEX(code) code, earliest, latest, count, previousCount FROM wD_UserCodeConnections WHERE userID IN (141625, 141626) AND type = 'Fingerprint';
 * +-------------+--------+----------------------------------+---------------------+---------------------+-------+
 * | type        | userID | code                             | earliest            | latest              | count |
 * +-------------+--------+----------------------------------+---------------------+---------------------+-------+
 * | Fingerprint | 141625 | 15DAC94746E0EC9886271752D045E40C | 2022-11-26 05:32:02 | 2022-12-03 16:47:52 |   213 |
 * | Fingerprint | 141625 | 163FD103DFE295000000000000000000 | 2022-10-15 14:05:11 | 2022-10-15 20:12:16 |   192 |
 * | Fingerprint | 141625 | BBAD353EEBF5E85DDFC8328287F9B0AE | 2022-10-16 02:18:43 | 2023-01-21 03:19:06 |  6686 | <--
 * | Fingerprint | 141625 | EC1E85BAC2B2DD09A6603C3740D59A1E | 2023-02-01 04:06:53 | 2023-05-14 17:32:15 |   746 |
 * | Fingerprint | 141626 | 0F924B5564881E9449A7C57BAF636821 | 2023-01-26 03:56:36 | 2023-04-24 03:43:12 |    91 |
 * | Fingerprint | 141626 | 16383A07CA5DFE2434CD2E348E23C9AD | 2023-01-24 04:27:30 | 2023-05-07 03:02:18 |  6912 |
 * | Fingerprint | 141626 | 30DA2EAAE5D9937A83B40ADA8AA0DE2E | 2022-10-16 02:11:13 | 2022-11-24 00:40:16 |  3512 |
 * | Fingerprint | 141626 | 35730C91E446B0059219B4C0527617E7 | 2022-11-24 12:40:29 | 2023-01-22 17:55:52 |  4419 |
 * | Fingerprint | 141626 | 3FE9C75487E342EC3159ED16F6C59376 | 2023-02-22 09:02:54 | 2023-02-22 10:34:26 |    10 |
 * | Fingerprint | 141626 | 4897729AF60B799F1ACD0B9ED3FF8E05 | 2023-01-24 18:26:21 | 2023-04-23 06:40:30 |   227 |
 * | Fingerprint | 141626 | 53607E32881C437EACCAB498C4690CAE | 2022-10-21 07:31:03 | 2022-11-20 10:29:39 |    32 |
 * | Fingerprint | 141626 | 631B03E8ED587B243D5BD5D75BA78543 | 2022-10-19 10:54:33 | 2022-11-23 01:31:44 |   300 |
 * | Fingerprint | 141626 | 68B779897B73BFF455198297777A0404 | 2022-11-26 09:33:08 | 2023-01-23 13:56:08 |  2128 |
 * | Fingerprint | 141626 | 70468D0F4AF75F2BE3A8620D5ECFAD94 | 2023-01-23 16:37:49 | 2023-04-28 15:39:56 |  1939 |
 * | Fingerprint | 141626 | 9F4A8B574EED39CBD3730855F3549F9E | 2022-12-06 17:41:48 | 2023-01-23 14:33:07 |   186 |
 * | Fingerprint | 141626 | A5D1D12AE0A8BBFC3641FD6402E1F1EB | 2022-12-02 15:55:29 | 2023-01-21 08:59:10 |   313 |
 * | Fingerprint | 141626 | BBAD353EEBF5E85DDFC8328287F9B0AE | 2022-11-14 11:56:22 | 2022-12-15 08:31:55 |    27 | <--
 * | Fingerprint | 141626 | BD5347B9C08210000000000000000000 | 2022-10-15 15:45:04 | 2022-10-15 22:02:49 |    63 |
 * +-------------+--------+----------------------------------+---------------------+---------------------+-------+
 * 
 * From the wD_UserCodeConnections table matches are found where other users have used the same code (for certain types, some types like LatLon would
 * generate too many matches to be useful).
 * SELECT a.type, a.userID userIDFrom, b.userID userIDTo, HEX(a.code) code, a.earliest earliestFrom, a.latest latestFrom, a.count countFrom, b.earliest earliestTo, b.latest latestTo, b.count countTo
 * FROM wD_UserCodeConnections a
 * INNER JOIN wD_UserCodeConnections b ON a.type = b.type AND a.code = b.code AND a.userID <> b.userID
 * WHERE a.type = 'Fingerprint' AND a.userID = 141625 AND b.userID = 141626
 * +-------------+------------+----------+----------------------------------+---------------------+---------------------+-----------+---------------------+---------------------+---------+
 * | type        | userIDFrom | userIDTo | code                             | earliestFrom        | latestFrom          | countFrom | earliestTo          | latestTo            | countTo |
 * +-------------+------------+----------+----------------------------------+---------------------+---------------------+-----------+---------------------+---------------------+---------+
 * | Fingerprint |     141625 |   141626 | BBAD353EEBF5E85DDFC8328287F9B0AE | 2022-10-16 02:18:43 | 2023-01-21 03:19:06 |      6686 | 2022-11-14 11:56:22 | 2022-12-15 08:31:55 |      27 |
 * +-------------+------------+----------+----------------------------------+---------------------+---------------------+-----------+---------------------+---------------------+---------+
 * 
 * Here matches are searched for other users, where 141625 matches codes from other users. This is important as a match between two users
 * may be significant if they match each other but no-one else, but not significant if a user has been around for 20 years and has hundreds of 
 * cookie codes, and matches a new user with one of those codes but matches many other users as well.
 * SELECT a.type, a.userID userIDFrom, b.userID userIDTo, HEX(a.code) code, a.earliest earliestFrom, a.latest latestFrom, a.count countFrom, b.earliest earliestTo, b.latest latestTo, b.count countTo
 * FROM wD_UserCodeConnections a
 * INNER JOIN wD_UserCodeConnections b ON a.type = b.type AND a.code = b.code AND a.userID <> b.userID
 * WHERE a.type = 'Fingerprint' AND a.userID = 141625 AND NOT b.userID = 141626;
 * Empty set (0.000 sec)
 * 
 * 
 * wD_UserCodeConnectionMatches
 * ----------------------------
 * These are aggregated into UserCodeConnectionMatches. The matches are the number of users found that used the same code, and the matchCount is the 
 * number of times the user used that matching code.
 * 
 * SELECT type, userIDFrom, userIDTo, matches, matchCount, earliestFrom, latestFrom, earliestTo, latestTo FROM wD_UserCodeConnectionMatches WHERE type = 'Fingerprint' AND ((userIDFrom = 141625 AND
 * userIDTo = 141626) OR (userIDFrom = 141626 AND userIDTo = 141625));
 * +-------------+------------+----------+---------+------------+---------------------+---------------------+---------------------+---------------------+
 * | type        | userIDFrom | userIDTo | matches | matchCount | earliestFrom        | latestFrom          | earliestTo          | latestTo            |
 * +-------------+------------+----------+---------+------------+---------------------+---------------------+---------------------+---------------------+
 * | Fingerprint |     141625 |   141626 |       1 |       6686 | 2022-10-16 02:18:43 | 2023-01-21 03:19:06 | 2022-11-14 11:56:22 | 2022-12-15 08:31:55 |
 * | Fingerprint |     141626 |   141625 |       1 |         27 | 2022-11-14 11:56:22 | 2022-12-15 08:31:55 | 2022-10-16 02:18:43 | 2023-01-21 03:19:06 |
 * +-------------+------------+----------+---------+------------+---------------------+---------------------+---------------------+---------------------+
 * 
 * 
 * wD_UserConnections
 * ------------------
 * 
 * These matches are then aggregated into a per-user table, where the count of the number of codes and code uses is aggregated, and the 
 * number of users with matching codes is aggregated, the number of codes matched with other users is aggregated, and the number of codes
 * other users matched with this user is aggregated.
 * 
 * SELECT userID, matchedFingerprint, matchedFingerprintTotal, matchedOtherFingerprintTotal, matchedFingerprintCount, matchedOtherFingerprintCount, countFingerprint, countFingerprintTotal FROM wD_UserConnections WHERE userID IN (141625, 141626);
 * +--------+--------------------+-------------------------+------------------------------+-------------------------+------------------------------+------------------+-----------------------+
 * | userID | matchedFingerprint | matchedFingerprintTotal | matchedOtherFingerprintTotal | matchedFingerprintCount | matchedOtherFingerprintCount | countFingerprint | countFingerprintTotal |
 * +--------+--------------------+-------------------------+------------------------------+-------------------------+------------------------------+------------------+-----------------------+
 * | 141625 |                  1 |                       1 |                            1 |                    6686 |                           27 |                4 |                  7837 |
 * | 141626 |                  1 |                       1 |                            1 |                      27 |                         6686 |               14 |                 20159 |
 * +--------+--------------------+-------------------------+------------------------------+-------------------------+------------------------------+------------------+-----------------------+
 * 
 * 
 * Code types
 * ==========
 * Codes are various bits of data relating to an account that tend to overlap when accounts are related. e.g. Cookie codes are from randomly generated 
 * cookies that get stored in users browsers; if two accounts share the same cookie codes it indicates they were using the same browser.
 * 
 * However these days everyone is aware of cookies, there are lots of tools for managing them etc, so they have limited use. Similarly IP addresses 
 * are less useful than they once were with VPNs cheap and easy to use.
 * This means many methods have to be used together to get an indication of whether accounts are related. If a user changes cookie codes / IPs constantly, 
 * that raises the suspicion about that indicator.
 * 
 * fingerprint.js and fingerprint-pro.js are used to generate fingerprint codes that match against a certain browser, like a cookie code but much harder 
 * to hide and mask.
 * 
 * Then there are codes like UserTurn, which just track gameIDs. This will generate many matches that aren't suspicious, but can be used to indicate 
 * whether user accounts share an unusually large number of games. UserTurnMissed matches a gameID and turn that the player missed, which if two players
 * miss at the same time can be a good indicator. At the same time if an account misses turns when the other doesn't and has no matches that can be a
 * negative indicator (against multi-accounting) as it implies the multi-accounter intentionally missed an order submission with one account but not 
 * another, which would take a lot of care.
 * 
 * Similarly the MessageCount and MessageLength codes match the number of messages and number of message characters that users have shared within a game.
 * If this is unusually little for a pair that indicates they are communicating outside the game, or don't need to communicate. If the average message 
 * lengths are different overall that is a negative indicator (for multi-accounting), as it implies the multi-accounter has a different messaging style
 * across two accounts.
 * 
 * IPs are looked up with a VPN lookup service which gives IP location info and whether the IP runs a VPN. This can be used to tell if a user is appearing
 * from all over the globe, if two IPs come from the same area, and if IPs being used are associated with a VPN.
 * 
 * Some codes like LatLon/Network/City aren't matched as it would generate too many matches, but are just provided as extra info.
 * 
 * => Matched:
 * Cookie - Randomly generated number that gets stored in a cookie
 * IP - IP address, IPv4/IPv6
 * IPv6 => '2409:8a00:184f:70d0:652f:47b3:7ee1:5f50' $ip=str_replace(':','',$ip); '24098a00184f70d0652f47b37ee15f50'
 * IPv4 => $ip = ip2long($ip); if( !$ip ) $ip = 0; $ip = dechex($ip);
 * Fingerprint - fingerprint.js free library that generates a fingerprint for the user's browser and sends it back to the site
 * FingerprintPro - fingerprint pro 3rd party API that generates a fingerprint externally with some secret sauce etc, and sends it from the server to the site
 * UserTurn - The game ID the user was in for a turn UNHEX(LPAD(CONV(gameID,10,16),16,'0'))
 * UserTurnMissed - The game ID * 1000 + the game turn that the user missed a.gameID*1000 + a.turn UNHEX(LPAD(CONV(gameIDTurn,10,16),16,'0'))
 * 
 * => Not matched
 * MessageCount - The user ID a game message was sent to, for every message UNHEX(LPAD(CONV(otherUserID,10,16),16,'0'))
 * MessageLength - The user ID a game message was sent to, for every character UNHEX(LPAD(CONV(otherUserID,10,16),16,'0'))
 * IPVPN - A UTF-8 string indicating an IP is associated with a vpn, relay, or proxy from the IP lookup
 * LatLon - Lat/Lon from the IP lookup UNHEX(LPAD(CONV(ROUND((u.latitude+90.0)*10,0)*10000+ROUND((u.longitude+180.0)*10,0),10,16),16,'0'))
 * Network - Network subnet from the IP lookup UNHEX(LPAD(REPLACE(REPLACE(REPLACE(network,':',''),'/',''),'.',''),16,'0'))
 * City - A UTF-8 string containing the city from the IP lookup
 * 
 * 
 * Match parameters
 * ================
 * For each code matched there are various things to take into account:
 * - What type of match is it? A UserTurn match is much less significant than a FingerprintPro match.
 * - How many other users does the user share this code with? If a code is common to lots of users it is less of a strong indicator.
 * - How many non-matching codes does the user not share? If there are lots of unmatched codes and only a few matched it is less strong.
 * - How many times was the matching code used? If very rarely that is less of an indicator, if it is used most of the time that is a stronger indicator.
 * - For users that have a matching code how many *other* users does that user have a matching code with? If a matching user also matches with hundreds of 
 * other users that is less of a strong indicator than if a matching user doesn't match with anyone else.
 * - For users that have a matching code how many times did that user use the code? If the other user rarely used that code it is less of an indicator.
 * 
 * Within a certain code match there are other details:
 * - What is the overlap time for the matching code? Has it matched over a long period of time, or just for a short period?
 * - What is the overlap time for all the matching codes? Does it make up a fairly continuous sequence of matches across different codes, or do the codes
 * used not match up in terms of when they were used? A cookie code match where one user used the code today and the other used it 10 years ago is less of
 * an indicator than a match where both users matched at the same time.
 * 
 * 
 * Other information
 * =================
 * Also feeding into the system are the daily usage pattern. The times and frequency that the site accessed over the day is tracked and given in 24 hour
 * slices. This can be used to indicate whether users are likely in the same timezone, and whether they access the site during the same periods with the
 * same frequency. To work around this indicator a multi-accounter would have to log on to their alternate account during early hours of the morning, and
 * never use their other account during certain hours, as the early morning period from 2-5am is usually very conspicuous for an account.
 * 
 * Other indicators that help correlate users are the e-mail address; is the e-mail a regular looking one, or does it look like a throwaway. Do the join 
 * times match.
 * @package GameMaster
 */
class libUserConnections
{
    private static $excludedUserIDCSVListCache = null;
    /**
     * A comma separated list of user IDs that shouldn't be included in the user connections, for performance and to reduce
     * the number of false positives for mods, e.g. bots
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

        // Collect message data first
        self::updateGameMessageStats();

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
 SELECT userID, 'Fingerprint' type, browserFingerprint code, MIN(lastRequest) earliestRequest, MAX(lastRequest) latestRequest, SUM(hits) requestCount
 FROM wD_AccessLog
 WHERE lastRequest >= FROM_UNIXTIME(".$lastUpdate.") AND browserFingerprint IS NOT NULL AND browserFingerprint <> UNHEX('00000000000000000000000000000000')
 GROUP BY userID, browserFingerprint
) r
ON DUPLICATE KEY UPDATE isUpdated=1, earliest=least(earliestRequest, earliest), latest=greatest(latestRequest, latest), count=count+requestCount;

INSERT INTO wD_UserCodeConnections (userID, type, code, earliest, latest, count)
SELECT userID, type, code , earliestRequest, latestRequest, requestCount
FROM (
 SELECT a.userID, 'IPVPN' type, LEFT(u.security,16) code, MIN(a.earliest) earliestRequest, MAX(a.latest) latestRequest, SUM(a.count) requestCount
 FROM wD_UserCodeConnections a
 INNER JOIN wD_IPLookups u ON a.code = u.ipCode
 WHERE a.type='IP' AND u.timeLookedUp >= ".$lastUpdate."
 GROUP BY a.userID, u.security
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
 GROUP BY a.userID, ROUND((u.latitude+90.0)*10,0), ROUND((u.longitude+180.0)*10,0)
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
SELECT a.userID, a.gameID, MIN(a.turnDateTime) earliestT, MAX(a.turnDateTime) latestT, COUNT(*) tCount
  FROM wD_TurnDate a
  WHERE a.turnDateTime >= ".$lastUpdate." 
  GROUP BY a.userID, a.gameID;

  DELETE FROM wD_Tmp_TurnCount WHERE userID IN (".self::getExcludedUserIDCSVList().");

  INSERT INTO wD_UserCodeConnections (userID, type, code, earliest, latest, count)
  SELECT userID, 'UserTurn' type, UNHEX(LPAD(CONV(gameID,10,16),16,'0')), FROM_UNIXTIME(earliestT), FROM_UNIXTIME(latestT), tCount
  FROM wD_Tmp_TurnCount r
  ON DUPLICATE KEY UPDATE isUpdated=1, earliest=least(FROM_UNIXTIME(earliestT), earliest), latest=greatest(FROM_UNIXTIME(latestT), latest), count=count+tCount;

  /* Enter missed turns shared */  
  DROP TABLE IF EXISTS wD_Tmp_MissedTurnCount;
  CREATE TABLE wD_Tmp_MissedTurnCount
  SELECT a.userID, a.gameID*1000 + a.turn gameIDTurn, MIN(a.turnDateTime) earliestT, MAX(a.turnDateTime) latestT, COUNT(*) tCount
	FROM  wD_MissedTurns  a
	WHERE a.turnDateTime >= ".$lastUpdate." 
	GROUP BY a.userID, a.gameID;

    DELETE FROM wD_Tmp_MissedTurnCount WHERE userID IN (".self::getExcludedUserIDCSVList().");
        
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
ON DUPLICATE KEY UPDATE isUpdated=1, earliest=least(r.earliestM, earliest), latest=greatest(r.latestM, latest), count=count+r.countMLen;

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
                INSERT INTO wD_UserCodeConnectionMatches (type, userIDFrom, userIDTo, matches, matchCount, earliestFrom, latestFrom, earliestTo, latestTo)
                SELECT a.type, a.userID userIDFrom, b.userID userIDTo, SUM(a.isNew) matches, SUM(a.count-a.previousCount) matchCount, 
                    MIN(a.earliest) earliestFrom, MAX(a.latest) latestFrom, MIN(b.earliest) earliestTo, MAX(b.latest) latestTo
                FROM wD_UserCodeConnections a
                INNER JOIN wD_UserCodeConnections b ON a.type = b.type AND a.code = b.code AND a.userID <> b.userID
                WHERE a.type = '".$codeType."' AND a.isUpdated = 1
                GROUP BY a.userID, b.userID, a.type
                ON DUPLICATE KEY UPDATE matches = matches + VALUES(matches), matchCount = matchCount + VALUES(matchCount), isUpdated = 1,
                    earliestFrom = LEAST(earliestFrom, VALUES(earliestFrom)), latestFrom = GREATEST(latestFrom, VALUES(latestFrom)),
                    earliestTo = LEAST(earliestTo, VALUES(earliestTo)), latestTo = GREATEST(latestTo, VALUES(latestTo));
                
                ";

                $sql .= "
                /* Add any newly found matches to the count, and the updated sum to the total matches.
                Note the matches / matched___ is the number of users where there is at least one code match,
                matchedCodes / matched____Total is the number of times a code has matched another user (this is
                not the same as the number of times a code has been used, which would be matchCount - previousMatchCount) */
                UPDATE wD_UserConnections uc
                INNER JOIN (
                    SELECT a.userIDFrom userID, a.type, SUM(isNew) matches, SUM(matches-previousMatches) matchedCodes, SUM(matchCount-previousMatchCount) matchedCodeCount
                    FROM wD_UserCodeConnectionMatches a
                    WHERE a.type = '".$codeType."' AND a.isUpdated = 1
                    GROUP BY a.userIDFrom, a.type
                ) rec ON rec.userID = uc.userId
                SET matched".$codeType." = matched".$codeType." + rec.matches, matched".$codeType."Total = matched".$codeType."Total + rec.matchedCodes, matched".$codeType."Count = matched".$codeType."Count + rec.matchedCodeCount;
                ";
    
                $sql .= "
                /* Add in the matchedOther___Total, which gives an indication of the matches other users have to this user, indicating whether the matches are symmetrical
                (i.e. is this a user that matched 1 cookie code a user with 100000 cookie code matches, or is this a user that matched 1 cookie code with another user
                that has 1 cookie code match, a lot more suspicious) */
                UPDATE wD_UserConnections uc
                INNER JOIN (
                    SELECT a.userIDTo userID, a.type, SUM(isNew) matches, SUM(matches-previousMatches) matchedCodes, SUM(matchCount-previousMatchCount) matchedCodeCount
                    FROM wD_UserCodeConnectionMatches a
                    WHERE a.type = '".$codeType."' AND a.isUpdated = 1
                    GROUP BY a.userIDTo, a.type
                ) rec ON rec.userID = uc.userId
                SET matchedOther".$codeType."Total = matchedOther".$codeType."Total + rec.matchedCodes, matchedOther".$codeType."Count = matchedOther".$codeType."Count + rec.matchedCodeCount;
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

}