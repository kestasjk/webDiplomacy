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
 * This script gives mods and admins the data needed to find multi-accounters, by parsing
 * wD_AccessLog, as well as other techniques.
 *
 * It uses a lot of resources for large data-sets, which the people who use it should be
 * aware of.
 *
 * @package Admin
 */

$DB->get_lock('gamemaster');

adminMultiCheck::form();

require_once('objects/groupUserToUserLinks.php');

/*
SELECT c.type, c.userIDTo, c.matches, c.matchCount, 
	f.code, 
	f.earliest fromEarliest, t.earliest toEarliest,
	f.latest fromLatest, t.latest toLatest, 
	f.count fromCount, t.count toCount,
	CASE c.type WHEN 'Cookie' THEN fU.countCookie WHEN 'IP' THEN fU.countIP WHEN 'Fingerprint' THEN fU.countFingerprint WHEN 'FingerprintPro' THEN fU.countFingerprintPro WHEN 'UserTurnMissed' THEN fU.countUserTurnMissed WHEN 'UserTurn' THEN fU.countUserTurn ELSE 0 END fromUserCount,
	CASE c.type WHEN 'Cookie' THEN tU.countCookie WHEN 'IP' THEN tU.countIP WHEN 'Fingerprint' THEN tU.countFingerprint WHEN 'FingerprintPro' THEN tU.countFingerprintPro WHEN 'UserTurnMissed' THEN tU.countUserTurnMissed WHEN 'UserTurn' THEN tU.countUserTurn ELSE 0 END toUserCount,
	CASE c.type WHEN 'Cookie' THEN fU.countCookieTotal WHEN 'IP' THEN fU.countIPTotal WHEN 'Fingerprint' THEN fU.countFingerprintTotal WHEN 'FingerprintPro' THEN fU.countFingerprintProTotal WHEN 'UserTurnMissed' THEN fU.countUserTurnMissedTotal WHEN 'UserTurn' THEN fU.countUserTurnTotal ELSE 0 END fromUserCountTotal,
	CASE c.type WHEN 'Cookie' THEN tU.countCookieTotal WHEN 'IP' THEN tU.countIPTotal WHEN 'Fingerprint' THEN tU.countFingerprintTotal WHEN 'FingerprintPro' THEN tU.countFingerprintProTotal WHEN 'UserTurnMissed' THEN tU.countUserTurnMissedTotal WHEN 'UserTurn' THEN tU.countUserTurnTotal ELSE 0 END toUserCountTotal,
	CASE c.type WHEN 'Cookie' THEN fU.matchedCookie WHEN 'IP' THEN fU.matchedIP WHEN 'Fingerprint' THEN fU.matchedFingerprint WHEN 'FingerprintPro' THEN fU.matchedFingerprintPro WHEN 'UserTurnMissed' THEN fU.matchedUserTurnMissed WHEN 'UserTurn' THEN fU.matchedUserTurn ELSE 0 END fromUserMatched,
	CASE c.type WHEN 'Cookie' THEN tU.matchedCookie WHEN 'IP' THEN tU.matchedIP WHEN 'Fingerprint' THEN tU.matchedFingerprint WHEN 'FingerprintPro' THEN tU.matchedFingerprintPro WHEN 'UserTurnMissed' THEN tU.matchedUserTurnMissed WHEN 'UserTurn' THEN tU.matchedUserTurn ELSE 0 END toUserMatched,
	CASE c.type WHEN 'Cookie' THEN fU.matchedCookieTotal WHEN 'IP' THEN fU.matchedIPTotal WHEN 'Fingerprint' THEN fU.matchedFingerprintTotal WHEN 'FingerprintPro' THEN fU.matchedFingerprintProTotal WHEN 'UserTurnMissed' THEN fU.matchedUserTurnMissedTotal WHEN 'UserTurn' THEN fU.matchedUserTurnTotal ELSE 0 END fromUserMatchedTotal,
	CASE c.type WHEN 'Cookie' THEN tU.matchedCookieTotal WHEN 'IP' THEN tU.matchedIPTotal WHEN 'Fingerprint' THEN tU.matchedFingerprintTotal WHEN 'FingerprintPro' THEN tU.matchedFingerprintProTotal WHEN 'UserTurnMissed' THEN tU.matchedUserTurnMissedTotal WHEN 'UserTurn' THEN tU.matchedUserTurnTotal ELSE 0 END toUserMatchedTotal,
	CASE c.type WHEN 'Cookie' THEN fU.matchedOtherCookieTotal WHEN 'IP' THEN fU.matchedOtherIPTotal WHEN 'Fingerprint' THEN fU.matchedOtherFingerprintTotal WHEN 'FingerprintPro' THEN fU.matchedOtherFingerprintProTotal WHEN 'UserTurnMissed' THEN fU.matchedOtherUserTurnMissedTotal WHEN 'UserTurn' THEN fU.matchedOtherUserTurnTotal ELSE 0 END fromUserOtherMatchedTotal,
	CASE c.type WHEN 'Cookie' THEN tU.matchedOtherCookieTotal WHEN 'IP' THEN tU.matchedOtherIPTotal WHEN 'Fingerprint' THEN tU.matchedOtherFingerprintTotal WHEN 'FingerprintPro' THEN tU.matchedOtherFingerprintProTotal WHEN 'UserTurnMissed' THEN tU.matchedOtherUserTurnMissedTotal WHEN 'UserTurn' THEN tU.matchedOtherUserTurnTotal ELSE 0 END toUserOtherMatchedTotal,
	fU.countLatLon, fU.countLatLonTotal, 
	tU.countLatLon, tU.countLatLonTotal, 
	fU.countNetwork, fU.countNetworkTotal, 
	tU.countNetwork, tU.countNetworkTotal, 
	fU.countCity, fU.countCityTotal, 
	tU.countCity, tU.countCityTotal, 
	fU.countIPVPN, fU.countIPVPNTotal, 
	tU.countIPVPN, tU.countIPVPNTotal, 
	fU.countMessageLength, fU.countMessageLengthTotal, 
	tU.countMessageLength, tU.countMessageLengthTotal, 
	fU.countMessageCount, fU.countMessageCountTotal, 
	tU.countMessageCount, tU.countMessageCountTotal
FROM wd_usercodeconnectionmatches c
INNER JOIN wd_usercodeconnections f ON f.userID = c.userIDFrom AND f.type = c.type
INNER JOIN wd_userconnections fU ON fU.userID = f.userID
INNER JOIN wd_usercodeconnections t ON t.userID = c.userIDTo AND t.type = c.type AND f.code = t.code
INNER JOIN wd_userconnections tU ON tU.userID = t.userID
WHERE userIDFrom = 138972
ORDER BY f.code, userIDFrom, userIDTo, c.type;

*/

// UserConnection - Aggregate stats for a user; what time of the day they play, how many codes of each type they matched etc
// UserCodeConnection - A link between a user and a code, e.g. a cookie/IP/turns with another user, and the stats for 
// how often the connection has occurred over what timespan.
// UserCodeConnectionMatches - A link between two users based on a code that they share, how many matches, and how often the match occurred
class UserCodeConnection {
	public static function loadForUserIDByType($userID) {
		global $DB;
		$tabl = $DB->sql_tabl("SELECT type, userID, HEX(code) code, earliest, latest, count FROM wD_UserCodeConnections WHERE userID = ".$userID);
		$res = array();
		while($row = $DB->tabl_hash($tabl))
		{
			if( !isset($res[$row['type']]) ) {
				$res[$row['type']] = array();
			}
			$res[$row['type']][$row['code']] = new UserCodeConnection($row);
		}
		return $res;
	}
	public function __construct($row) {
		$this->type = $row['type'];
		$this->userID = $row['userID'];
		$this->code = $row['code'];
		$this->earliest = $row['earliest'];
		$this->latest = $row['latest'];
		$this->count = $row['count'];
	}
	public $type;
	public $userID;
	public $code;
	public $earliest;
	public $latest;
	public $count;
}

if ( isset($_REQUEST['aUserID']) and $_REQUEST['aUserID'] )
{
	try
	{
		if ( isset($_REQUEST['bUserIDs']) and $_REQUEST['bUserIDs'] )
		{
			$m = new adminMultiCheck($_REQUEST['aUserID'], $_REQUEST['bUserIDs']);
		}
		else
		{
			$m = new adminMultiCheck($_REQUEST['aUserID']);
		}

		$m->printCheckSummary();
		
		$m->aLogsDataCollect();

		if ( !is_array($m->bUserIDs) )
		{
			$m->findbUserIDs();
		}
			
		
		if ( ! $m->bUserIDs )
		{
			print '<p>'.l_t('This account has no links with other accounts').'</p>';
		}
		else
		{
			// Fetch the user record data
			$bUsers = array();
			foreach($m->bUserIDs as $bUserID)
			{
				try {
					$bUsers[$bUserID] = new User($bUserID);
				} catch(Exception $e) {
					print '<p><strong>'.l_t('%s is an invalid user ID.',$bUserID).'</strong></p>';
					continue;
				}
			}

			// Output the group panel user to user links for this set of users:
			print '<div>';
			$uids = $m->bUserIDs;
			$uids[] = $m->aUserID;
			$relations = GroupUserToUserLinks::loadFromUserIDs($uids,$uids);
			$relations->applyUsers($bUsers, false); // Don't filter out peer suspicions
			
			print $relations->outputTable(-1000,-1000,-1000); // Be inclusive of suspicions
			print '</div>';
			
			$m->printUserTimeprint();
			
			foreach($bUsers as $bUser)
			{
				// Reliability rating
				// Games joined: password, no press, bot games
				// Messages / game Messages / user Messages / suspect stats
				// Social media links, SMS, paypal links, time joined, time last accessed
				// Forum messages
				$m->compare($bUser, $relations);
			}

			if( isset($_REQUEST['showHistory']) )
			{
				$m->timeData();
			}
		}
	}
	catch(Exception $e)
	{
		print '<p><strong>'.l_t('Error').':</strong> '.$e->getMessage().'</p>';
	}
}

// Independent suspicion score
// ---------------------------
// suspicionRelationshipsMod
// Moderator suspicion Weak * 10
// Moderator suspicion Mid * 20
// Moderator suspicion Strong * 50

// suspicionRelationshipsPeer
// Peer suspicion Weak * 1
// Peer suspicion Mid * 2
// Peer suspicion Strong * 5

// suspicionIPLookup
// Lots of IP addresses
// Lots of IP networks
// IP address overlap to other accounts
// IP address overlap from other accounts
// IP lookup vpn alerts

// suspicionLocationLookup
// Lots of regions
// Lots of cities
// Lots of Lat/Lon locations
// Lat/Lon locations wide spread
// Lat/Lon to daily sleep period mismatch
// Lat/Lon to declared location mismatch

// suspicionCookieCode
// Lots of cookie codes
// Cookie code length of life
// Cookie code / browser fingerprint ratio
// Cookie code / fingerprint pro ratio

// suspicionBrowserFingerprint
// Lots of browser fingerprints

// suspicionFingerprintPro
// Not enough fingerprint pro
// Fingerprint pro uncertainty
// Fingerprint pro VPN alert
// Fingerprint pro incognito alert
// Fingerprint pro length of life

// suspicionGameActivity
// Plays with few different people
// Repeatedly plays with certain people
// Messages a few people much more/less
// Missed turn overlap
// Supports a few people much more


// User code suspicion score
// -------------------------
// For each code how many user matches - average user matches over last period
// Top codes for each type - how many user matches - 

/* 
-- Get the main codes for a user:
SELECT a.userID, a.type, a.code, a.count, a.latest, c.oldest, c.newest, c.codeCount, c.countSum, c.countAvg, c.countMax, c.users, c.newestUser, c.oldestUser 
FROM wD_UserCodeConnections a 
INNER JOIN (
    SELECT type, code, MIN(latest) oldest, MAX(latest) newest, 
        COUNT(*) codeCount, SUM(count) countSum, AVG(count) countAvg, MAX(count) countMax, 
        COUNT(DISTINCT userID) users, MAX(userID) newestUser, MIN(userID) oldestUser 
    FROM wD_UserCodeConnections 
    WHERE userID <> 10 GROUP BY type, code
) c ON c.type = a.type AND c.code = a.code 
WHERE a.userID = 10 
ORDER BY c.type, a.count DESC;
*/

// User A-User B suspicion score
// -----------------------------
// Matches that trigger user - user evaluation
// 1000 * Fingerprint pro match * Certainty
// 500 * Cookie code match
// 100 * Browser fingerprint match
// 100 * IP match

// 500 * Moderator relationship rating
// 100 * Self relationship rating
// 50 * Peer relationship rating

// Code overlap
// time distance user A, time distance user B,
// time distance between user A user B,
// average code % with other users,
// average code % with other users that have a match

// LatLon match
// City match
// Network match
// Region match

// Daily 6 hour sleep period overlap
// Game missed turn overlap
// Game turn overlap

// LatLon match
// City match
// Region match

// Game message count; lower than normal / higher than normal

// User A - User B code match suspicion score
// ------------------------------------
// Individual code match ->
// For code matches in the past month and/or all code matches
// Matching code count% => What % of the total count does this matching code make up for the user
// Distinct codes => How many different codes does the user have
// Code # => How many 
// Code % user A, Code % user B, 
// average code %/user,
// average code %/user,
// Code # user A, Code # user B, 
// average code #/user,
// how many codes user A, how many codes user B,


/**
 * This class manages a certain user's often used multi-account comparison data, as well
 * as a list of users which are being compared to. $aUser is the first user, $bUser is the
 * second, of which there will likely be several
 *
 * @package Admin
 */
class adminMultiCheck
{
	/**
	 * Print a form for selecting which user to check, and which users to check against
	 */
	public static function form()
	{
		print '<form method="get" action="admincp.php#viewMultiFinder">';

		print '<p><strong>'.l_t('User ID:').'</strong><br />'.l_t('The user ID to check').'<br />
				<input type="text" name="aUserID" value="" length="50" /></p>';

		print '<p><strong>*'.l_t('Check against user IDs:').'</strong><br />'.l_t('An optional comma-separated list '.
				'of user-IDs to compare the above user ID to. If this is not specified the user ID '.
				'above will be checked against accounts which have matching IP/cookie-code data.').'<br />
				<input type="text" name="bUserIDs" value="" length="300" /></p>';

		print '<p><strong>'.l_t('Show complete history for the user and links found:').'</strong>
				<input type="checkbox" name="showHistory" /><br />
				'.l_t('With this checked the complete access log data for all the matching accounts will be displayed, '.
				'instead of displaying the list of linked accounts. '.
				'This makes it easy to check whether people are accessing the site during the same time periods, '.
				'and gives a more detailed picture of what is happening.').'
				</p>';

		print '<p><strong>'.l_t('Links between users share public games:').'</strong>
				<input type="checkbox" name="activeLinks" /><br />
				'.l_t('Ignores links if the users have not played in '.
				'the same public games. If there are no shared public game connections the normal multi finder results will be displayed instead.').'
				</p>';

		print '<input type="submit" name="Submit" class="form-submit" value="'.l_t('Check').'" />
				</form>';
	}

	private function printTimeDataRow($row, $lastRow=false)
	{
		static $alternate;
		if ( !isset($alternate) ) $alternate = false;
		$alternate = !$alternate;

		print '<tr class="replyalternate'.(2-$alternate).' replyborder'.(2-$alternate).'">';

		foreach($row as $name=>$part)
		{
			print '<td>';

			if ( $name == 'userID')
			{
				if ( $part == $this->aUserID )
					print '<strong>'.$part.'</strong>';
				else
					print $part;

				continue;
			}

			if ( $lastRow )
			{
				if ( $name == 'lastRequest' )
				{
					$isMaf = true;
					$timeComparison = l_t('(%s earlier)',libTime::remainingText($lastRow['lastRequest'], $part, $isMaf));

					if ( ( $lastRow['lastRequest'] - $part ) < 15*60 )
						print '<span class="redComparison" >'.$timeComparison.'</span>';
					elseif ( ( $lastRow['lastRequest'] - $part ) < 30*60 )
						print '<span class="Turkey">'.$timeComparison.'</span>';
					elseif ( ( $lastRow['lastRequest'] - $part ) < 45*60 )
						print '<span class="Italy">'.$timeComparison.'</span>';
					else
						print $timeComparison;
				}
				else
				{
					if ( $part == $lastRow[$name] )
						print '<span class="redComparison">'.$part.'</span>';
					else
						print $part;
				}
			}
			else
			{
				if ( $name == 'lastRequest' )
					print libTime::text($part);
				else
					print $part;
			}

			print '</td>';
		}

		print '</tr>';
	}

	public function timeData()
	{
		global $DB;

		$userIDs = $this->bUserIDs;
		array_push($userIDs, $this->aUserID);

		print '<p>'.l_t('Outputting access log history for the users being checked').'</p>';

		if ( isset($_REQUEST['activeLinks']) and count($this->aLogsData['activeGameIDs']) )
		{
			$tabl = $DB->sql_tabl(
				"SELECT UNIX_TIMESTAMP(a.lastRequest) as lastRequest, a.userID, u.username,
					a.hits, HEX(a.cookieCode) as cookieCode, concat('<a target=\"_blank\" href=\"https://whatismyipaddress.com/ip/',i.ip,'\">',i.ip,'</a>') as ip, HEX(a.userAgent) as userAgent
				FROM wD_AccessLog a
 				INNER JOIN wD_Users u ON ( u.id = a.userID )
				INNER JOIN wD_Members m ON ( a.userID = m.userID )
				LEFT JOIN wD_IPLookups i ON i.ipCode = a.ip
				WHERE
					m.gameID IN (".implode(',', $this->aLogsData['activeGameIDs']).")
					AND a.userID IN ( ".implode(',',$userIDs) .")
				ORDER BY a.lastRequest DESC LIMIT 1000"
			);
		}
		else
		{
			$tabl = $DB->sql_tabl(
				"SELECT UNIX_TIMESTAMP(a.lastRequest) as lastRequest, a.userID, u.username,
					a.hits, HEX(a.cookieCode) as cookieCode, concat('<a target=\"_blank\" href=\"https://whatismyipaddress.com/ip/',i.ip,'\">',i.ip,'</a>') as ip, HEX(a.userAgent) as userAgent
				FROM wD_AccessLog a 
				INNER JOIN wD_Users u ON ( u.id = a.userID )
				LEFT JOIN wD_IPLookups i ON i.ipCode = a.ip
				WHERE a.userID IN ( ".implode(',',$userIDs) .")
				ORDER BY a.lastRequest DESC LIMIT 1000"
			);
		}


		print '<table>';

		$headers = array('Time', 'User ID', 'Username', 'Pages', 'Cookie code', 'IP', 'User agent');
		foreach($headers as &$header) $header='<strong>'.$header.'<strong>';
		$this->printTimeDataRow($headers);

		$gap = 0;
		$lastRow = false;
		$lastUserID = 0;

		while( $row = $DB->tabl_hash($tabl) )
		{
			if ( $row['userID'] != $lastUserID )
			{
				$lastUserID = $row['userID'];

				if ( $gap > 0 )
				{
					//$this->printTimeDataRow(array($gap.' rows from the same user</tr>'));
					$this->printTimeDataRow($headers);
					$this->printTimeDataRow($lastRow);
				}

				$this->printTimeDataRow($row, $lastRow);

				$gap = 0;
			}
			else
			{
				$gap++;
			}

			$lastRow = $row;
		}

		if ( $gap > 0 )
		{
			print '<tr><td>'.l_t('%s rows from the same user.',$gap).'</td></tr>';
			$this->printTimeDataRow($lastRow);
		}

		print '</table>';
	}

	/**
	 * The user ID being checked
	 * @var int
	 */
	public $aUserID;

	/**
	 * The user being checked
	 * @var User
	 */
	public $aUser;

	/**
	 * Data from the user being checked which is used repeatedly
	 * @var mixed[]
	 */
	public $aLogsData=array();

	/**
	 * The user IDs which the aUser is being checked against
	 * @var int[]
	 */
	public $bUserIDs;

	/**
	 * Set the class up to check a certain user
	 *
	 * @param int $aUserID The ID of the user being checked
	 * @param int[] $bUserIDs=false [Optional]The IDs to check against; possible suspects will be selected if none are given
	 */
	public function __construct($aUserID, $bUserIDs=false)
	{
		$this->aUserID = (int)$aUserID;

		$this->aUser = new User($this->aUserID);

		if( $bUserIDs !== false )
		{
			$arr = explode(',',$bUserIDs);
			$this->bUserIDs = array();

			foreach($arr as $bUserID)
			{
				if ( $aUserID == $bUserID ) continue;

				$this->bUserIDs[] = (int)$bUserID;
			}
		}
	}

	/**
	 * If no bUserIDs were given on construction some users to be checked against
	 * have to be found. This is done by finding cookie-code and IP matches, resulting
	 * in bUserIDs being set.
	 */
	public function findbUserIDs()
	{
		global $DB;

		if ( isset($_REQUEST['activeLinks']) and count($this->aLogsData['PublicGameIDs']) )
		{
			$tabl = $DB->sql_tabl(
				"SELECT DISTINCT b.userID
				FROM wD_UserCodeConnections a
				INNER JOIN wD_UserCodeConnections b ON a.type = b.type AND a.code = b.code
				INNER JOIN wD_Members m ON ( b.userID = m.userID )
				WHERE
					m.gameID IN (".implode(',', $this->aLogsData['PublicGameIDs']).")
					AND a.userID = ".$this->aUserID."
					AND b.userID <> ".$this->aUserID."
					AND a.type IN ('IP','Cookie','Fingerprint','FingerprintPro')
				LIMIT 100"
				);
		}
		else
		{
			$tabl = $DB->sql_tabl(
				"SELECT DISTINCT b.userID
				FROM wD_UserCodeConnections a
				INNER JOIN wD_UserCodeConnections b ON a.type = b.type AND a.code = b.code
				WHERE
					a.userID = ".$this->aUserID."
					AND b.userID <> ".$this->aUserID."
					AND a.type IN ('IP','Cookie','Fingerprint','FingerprintPro')
					LIMIT 100"
				);
		}


		$arr=array();
		while( list($bUserID) = $DB->tabl_row($tabl) )
		{
			$arr[] = $bUserID;
		}

		// Add in any suspicions/relationships:
		$arr = array_merge($arr, GroupUserToUserLinks::loadFromUserID($this->aUserID, false)->getUserIDsOverThreshold(-1000,-1000,-1000));
		$arr = array_unique($arr, SORT_NUMERIC);

		$this->bUserIDs = $arr;
	}

	/**
	 * Print a summary of the check which is about to be performed
	 */
	public function printCheckSummary()
	{
			print '<p>'.l_t('Checking %s %s %s (userID=%s)','<a href="userprofile.php?userID='.$this->aUserID.'">'.$this->aUser->username.'</a>',
					'('.$this->aUser->points.' '.libHTML::points().')',
				'RR: '.$this->aUser->reliabilityRating,$this->aUserID)
				.($this->aUser->type['Banned'] ? '<img src="'.l_s('images/icons/cross.png').'" alt="X" title="'.l_t('Banned').'" />' : '').'
				<ul>
				<li><strong>email:</strong> ' .$this->aUser->email.'</li>
				</ul></p>';

		if( is_array($this->bUserIDs) )
		{
			print '<p>'.l_t('Checking against specified user accounts:').' '.implode(', ',$this->bUserIDs).'.</p>';
		}
		else
		{
			print '<p>'.l_t('Checking against IP/cookie-code linked users.').'</p>';
		}
	}

	/**
	 * Run a SQL query and return the first column as an array. If $tally is given
	 * the second column is stored too (and is used for tallys for the first column in practice).
	 *
	 * @param string $sql The 1/2 column SQL query which will return the list
	 * @param array $tally=false If provided the 2nd column will be stored in this array, indexed by the first.
	 *
	 * @return array The generated list from the first column
	 */
	private static function sql_list($sql, &$tally=false)
	{
		global $DB;

		$tabl = $DB->sql_tabl($sql);

		if ( $tally === false )
		{
			$list = array();
			while( list($row) = $DB->tabl_row($tabl) )
			{
				$list[] = $row;
			}
		}
		else
		{
			$list = array();
			while( list($row, $count) = $DB->tabl_row($tabl) )
			{
				if ( is_array($tally) ) $tally[$row] = $count;

				$list[] = $row;
			}
		}

		return $list;
	}

	public static $types = array('Cookie','IP','IPVPN','Fingerprint','FingerprintPro','UserTurn', 'UserTurnMissed', 'MessageCount', 'MessageLength');
	/**
	 * Collect data aboue aUser from the AccessLogs which is useful for checking
	 * for multi-accounts, so that it can be saved in aLogsData and re-used for
	 * each bUserID checked against.
	 *
	 * If enough data isn't found in the AccessLogs this will throw an exception.
	 */
	public function aLogsDataCollect()
	{
		global $DB;
		global $User;
		$this->aLogsData = array();

		foreach(self::$types as $type)
		{
			list($this->aLogsData[$type]) = self::sql_list(
				"SELECT COUNT(*) FROM wD_UserCodeConnections WHERE userID = ".$this->aUserID." AND type = '".$type."'"
			);
		}

		// Up until now all aLogsData arrays must be populated
		/*foreach($this->aLogsData as $name=>$data)
		{
			if ( ! is_array($data) or ! count($data) )
			{
				throw new Exception(l_t('%s does not have enough data; this account cannot be checked.',$name));
			}
		}*/

		// Insert or update the wD_UserConnections record here with the mod who checked it and when it was checked.  
        $DB->sql_put("INSERT INTO wD_UserConnections (userID, modLastCheckedBy, modLastCheckedOn) 
        VALUES (".$this->aUserID.", ".$User->id.", ".time().") ON DUPLICATE KEY UPDATE modLastCheckedBy=VALUES(modLastCheckedBy), 
        modLastCheckedOn=VALUES(modLastCheckedOn)");

		$this->aLogsData['fullGameIDs'] = self::sql_list(
			"SELECT DISTINCT gameID
			FROM wD_Members
			WHERE userID = ".$this->aUserID
		);

		list($this->aLogsData['total']) = $DB->sql_row(
				"SELECT COUNT(*) FROM wD_UserCodeConnections WHERE type='IP' AND userID = ".$this->aUserID
			);

		$this->aLogsData['activeGameIDs'] = self::sql_list(
			"SELECT DISTINCT m.gameID
			FROM wD_Members m INNER JOIN wD_Games g ON ( g.id = m.gameID )
			WHERE m.userID = ".$this->aUserID." AND NOT g.phase = 'Finished'"
		);

		$this->aLogsData['PublicGameIDs'] = self::sql_list(
			"SELECT DISTINCT m.gameID
			FROM wD_Members m INNER JOIN wD_Games g ON ( g.id = m.gameID )
			WHERE m.userID = ".$this->aUserID." and g.password is null"
		);
	}

	/**
	 * Check a single data-type from aUser with the same data-type from one of the bUsers.
	 * Various details are printed which help the viewer decide if there is a significant similariry.
	 * If the final tallys and counts are provided the main ratio will be determined from the tallys rather
	 * than the individual match types.
	 *
	 * @param string $name The name of the data-type being compared
	 * @param string[] $matches An array containing each of the individual match types in both aUser and bUser
	 * @param int $matchCount The number of individual match types which were in aUser and bUser (e.g. 3 shared distinct IPs)
	 * @param int $totalMatchCount The total number of individual match types possible (e.g. 5 distinct IPs)
	 * @param array $scale An array of ratio-cutoff points, indexed by the CSS class to set if the ratio is between the indexed cutoff
	 * @param array $aTally=false The tally for the number of occurrances of each match in $matches for aUser
	 * @param int $aTotalCount=false The total number of records in the AccessLog for aUser, to convert the tally to a percentage
	 * @param array $bTally=false The tally for the number of occurrances of each match in $matches for bUser
	 * @param int $bTotalCount=false The total number of records in the AccessLog for bUser, to convert the tally to a percentage
	 */
	private static function printDataComparison($name, array $matches, $matchCount, $totalMatchCount,
				array $scale, $aTally=false, $aTotalCount=false, $bTally=false, $bTotalCount=false)
	{
		if ( is_array($aTally) and is_array($bTally) )
		{
			/*
			 * Use the tallys to find the ratio. If each match type contains the same percentage of occurrances
			 * for each user it contributes towards a higher ratio more than if one is very large, and the other is
			 * small.
			 * i.e. Differences is respective match tallys will bring the ratio down, identical respective match
			 * tallys bring the ratio up.
			 */

			$ratio = 0.0;

			foreach($matches as $match)
			{
				$ratio += min(
						$aTally[$match]/$aTotalCount,
						$bTally[$match]/$bTotalCount
					);
			}

			$ratioText = $matchCount.'/'.$totalMatchCount.' ('.round($ratio*100).'%)';
		}
		else
		{
			// The ratio is simply the number of individual data-types found in both users divided by the total number
			// of individual data-types in aUser (i.e. the amount of overlap / the maximum possible overlap)
			$ratio = $matchCount/( $totalMatchCount==0 ? 1 : $totalMatchCount );
			$ratioText = $matchCount.'/'.$totalMatchCount;
		}

		// Determine the color based on the ratio which was just found
		$color = false;
		foreach($scale as $subColor=>$subLimit)
		{
			if ( $ratio > $subLimit )
				$color = $subColor;
			else
				break;
		}

		if ( $color )
		{
			$ratioText = '<span class="'.$color.'">'.$ratioText.'</span>';
		}

		print '<li><strong>'.$name.':</strong> '.$ratioText.'<br />';
		// Display the matches; in the case of tallys used provide a tallied match list, otherwise a plain match list
		if ( is_array($aTally) and is_array($bTally) )
		{
			$newMatches = array();
			foreach($matches as $match)
			{
				print '<i>match '.$match.'</i> zxcvb<br/>';
				print '<i>aTotalCount'.$aTotalCount.'</i>zxcvb<br/>';
				print '<i>aTally '.$aTally[$match].'</i> zxcvb<br/>';
				print '<i>bTotalCount '.$bTotalCount.'</i> zxcvb<br/>';
				print '<i>bTally '.$bTally[$match].'</i> zxcvb<br/>';
				$newMatches[] = $match.' ('.round(100*$aTally[$match]/$aTotalCount).'%-'.round(100*$bTally[$match]/$bTotalCount).'%)';
			}
			print implode(', ', $newMatches);
		}
		else
		{
			print implode(', ', $matches);
		}

		print '</li>';
	}

	private function compareIPData($bUserID, $bUserTotal)
	{
		$aUserTotal = $this->aLogsData['total'];
		$aUserData = $this->aLogsData['IPs'];

		$bTally=array();
		$matches = self::sql_list(
			"SELECT b.code, b.count 
			FROM wD_UserCodeConnections a 
			INNER JOIN wD_UserCodeConnections b 
			ON a.type = b.type AND a.code = b.code AND a.userID <> b.userID 
			WHERE a.userID = ".$this->aUserID." AND b.userID = ".$bUserID." AND a.type = 'IP'", $bTally
		);
		if( count($matches) )
		{
			$aTally=array();
			self::sql_list(
				"SELECT a.code, a.count 
				FROM wD_UserCodeConnections a 
				INNER JOIN wD_UserCodeConnections b 
				ON a.type = b.type AND a.code = b.code AND a.userID <> b.userID 
				WHERE a.userID = ".$this->aUserID." AND b.userID = ".$bUserID." AND a.type = 'IP'", $aTally
			);
			self::printDataComparison('IPs', $matches, count($matches), ($aUserTotal),
					array('Italy'=>0.1,'Turkey'=>0.2,'Austria'=>0.3), $aTally, $aUserData, $bTally, $bUserTotal);
		}
	}

	private function compareCookieCodeData($bUserID, $bUserTotal)
	{
		$aUserTotal = $this->aLogsData['total'];
		$aUserData = $this->aLogsData['cookieCodes'];

		$bTally=array();
		$matches = self::sql_list(
			"SELECT b.code, b.count 
			FROM wD_UserCodeConnections a 
			INNER JOIN wD_UserCodeConnections b 
			ON a.type = b.type AND a.code = b.code AND a.userID <> b.userID 
			WHERE a.userID = ".$this->aUserID." AND b.userID = ".$bUserID." AND a.type = 'Cookie'"
			, $bTally
			/*
			"SELECT cookieCode, COUNT(cookieCode)
			FROM wD_AccessLog
			WHERE userID = ".$bUserID." AND cookieCode IN ( ".implode(',',$aUserData)." )
			GROUP BY cookieCode", $bTally*/
		);
		if( count($matches) )
		{
			$aTally=array();
			self::sql_list(
				"SELECT a.code, a.count 
				FROM wD_UserCodeConnections a 
				INNER JOIN wD_UserCodeConnections b 
				ON a.type = b.type AND a.code = b.code AND a.userID <> b.userID 
				WHERE a.userID = ".$this->aUserID." AND b.userID = ".$bUserID." AND a.type = 'Cookie'", $aTally
			);
			self::printDataComparison('CookieCode', $matches, count($matches), ($aUserData),
					array('Italy'=>0.1,'Turkey'=>0.2,'Austria'=>0.3), $aTally, $aUserTotal, $bTally, $bUserTotal);
		}
	}

	private function compareFingerprintData($bUserID, $bUserTotal)
	{
		$aUserTotal = $this->aLogsData['total'];
		$aUserData = $this->aLogsData['browserFingerprints'];

		$bTally=array();
		$matches = self::sql_list(
			"SELECT b.code, b.count 
			FROM wD_UserCodeConnections a 
			INNER JOIN wD_UserCodeConnections b 
			ON a.type = b.type AND a.code = b.code AND a.userID <> b.userID 
			WHERE a.userID = ".$this->aUserID." AND b.userID = ".$bUserID." AND a.type = 'Fingerprint'", $bTally
		);
		if( count($matches) )
		{
			$aTally=array();
			self::sql_list(
				"SELECT a.code, a.count 
				FROM wD_UserCodeConnections a 
				INNER JOIN wD_UserCodeConnections b 
				ON a.type = b.type AND a.code = b.code AND a.userID <> b.userID 
				WHERE a.userID = ".$this->aUserID." AND b.userID = ".$bUserID." AND a.type = 'Fingerprint'", $aTally
			);
			self::printDataComparison('BrowserFingerprint', $matches, count($matches), ($aUserData),
					array('Italy'=>0.1,'Turkey'=>0.2,'Austria'=>0.3), $aTally, $aUserTotal, $bTally, $bUserTotal);
		}
	}
	private function compareFingerprintProData($bUserID, $bUserTotal)
	{
		$aUserTotal = $this->aLogsData['fpPro'];//['total'];
		$aUserData = $this->aLogsData['fpPro'];

		$bTally=array();
		$matches = self::sql_list(
			"SELECT b.code, b.count 
			FROM wD_UserCodeConnections a 
			INNER JOIN wD_UserCodeConnections b 
			ON a.type = b.type AND a.code = b.code AND a.userID <> b.userID 
			WHERE a.userID = ".$this->aUserID." AND b.userID = ".$bUserID." AND a.type = 'FingerprintPro'
			", $bTally
		);
		if( count($matches) )
		{
			$aTally=array();
			self::sql_list(
				"SELECT a.code, a.count 
				FROM wD_UserCodeConnections a 
				INNER JOIN wD_UserCodeConnections b 
				ON a.type = b.type AND a.code = b.code AND a.userID <> b.userID 
				WHERE a.userID = ".$this->aUserID." AND b.userID = ".$bUserID." AND a.type = 'FingerprintPro'
				", $aTally
			);
			self::printDataComparison('BrowserFingerprintPro', $matches, count($matches), ($aUserData),
					array('Italy'=>0.1,'Turkey'=>0.2,'Austria'=>0.3), $aTally, $aUserTotal, $bTally, $bUserTotal);
		}
	}
	private function compareType($bUserID, $bUserTotal, $type)
	{
		$aUserTotal = $this->aLogsData[$type];//['total'];
		$aUserData = $this->aLogsData[$type];

		$bTally=array();
		$matches = self::sql_list(
			"SELECT b.code, b.count 
			FROM wD_UserCodeConnections a 
			INNER JOIN wD_UserCodeConnections b 
			ON a.type = b.type AND a.code = b.code AND a.userID <> b.userID 
			WHERE a.userID = ".$this->aUserID." AND b.userID = ".$bUserID." AND a.type = '".$type."'
			", $bTally
		);
		$bSum = 0;
		foreach($bTally as $codeMatch=>$matchCount)
			$bSum += $matchCount;
		$bUserTotal = $bSum;
		if( count($matches) )
		{
			$aTally=array();
			self::sql_list(
				"SELECT a.code, a.count 
				FROM wD_UserCodeConnections a 
				INNER JOIN wD_UserCodeConnections b 
				ON a.type = b.type AND a.code = b.code AND a.userID <> b.userID 
				WHERE a.userID = ".$this->aUserID." AND b.userID = ".$bUserID." AND a.type = '".$type."'
				", $aTally
			);
			self::printDataComparison($type, $matches, count($matches), ($aUserData),
					array('Italy'=>0.1,'Turkey'=>0.2,'Austria'=>0.3), $aTally, $aUserTotal, $bTally, $bUserTotal);
		}
	}
	private function compareGames($name, $bUserID, $gameIDs)
	{
		global $DB;
		
		$matches = self::sql_list(
			"SELECT DISTINCT gameID
			FROM wD_Members
			WHERE userID = ".$bUserID." AND gameID IN ( ".implode(',',$gameIDs)." )"
		);
		
		$privateMatches = array();
		if(count($matches) > 0)
		{
			$tabl = $DB->sql_tabl("SELECT id FROM wD_Games WHERE NOT password IS NULL AND id IN (".implode(',',$matches).")");
			while(list($id)=$DB->tabl_row($tabl)) $privateMatches[] = $id;
		}
		
		$linkMatches = array();
		foreach($matches as $match)
			$linkMatches[] = '<a href="board.php?gameID='.$match.'" class="light">'.$match.(in_array($match,$privateMatches)?'':' (Public)').'</a>';
		$matches = $linkMatches;
		unset($linkMatches);

		self::printDataComparison($name, $matches, count($matches), count($gameIDs),
				array('Italy'=>1/4,'Turkey'=>1/2,'Austria'=>2/3) );
	}

	private static function getCodeTotalsByType($userID)
	{
		global $DB;
		$tabl = $DB->sql_tabl("SELECT type, SUM(count) total FROM wD_UserCodeConnections WHERE userID = ".$userID." GROUP BY type");
		$totals = array();
		while(list($type, $total) = $DB->tabl_row($tabl))
		$totals[$type] = $total;
		return $totals;
	}
	/**
	 * Compares this class' aUser with one of its bUsers, and the data returned from the comparison
	 * makes it easy to tell if the two users are being played by the same player.
	 *
	 * @param User $bUser The user to compare aUser with
	 */
	public function compare(User $bUser, GroupUserToUserLinks $relations=null)
	{
		global $DB;

		print '<ul>';
		print '<li><a href="userprofile.php?userID='.$bUser->id.'">'.$bUser->username.'</a> ('.$bUser->points.' '.libHTML::points().')
				'.($bUser->type['Banned'] ? '<img src="'.l_s('images/icons/cross.png').'" alt="X" title="'.l_t('Banned').'" />' : '').'
				RR: '.$bUser->reliabilityRating.'
			(<a href="?aUserID='.$bUser->id.'#viewMultiFinder" class="light">'.l_t('check userID=%s',$bUser->id).'</a>)
				<ul><li><strong>email:</strong> ' .$bUser->email.'</li>';

		$bUserTotals = self::getCodeTotalsByType($bUser->id);

		foreach(self::$types as $type)
			$this->compareType($bUser->id, 0, $type);
		
		if ( !is_null($relations) )
		{
			if( in_array($bUser->id, $relations->getUserIDsOverThreshold(-1000,-1000,-1000)) )
				print $relations->outputTable(array($bUser->id));
		}
		if ( count($this->aLogsData['fullGameIDs']) > 0 )
			$this->compareGames('All games', $bUser->id, $this->aLogsData['fullGameIDs']);

		if ( count($this->aLogsData['activeGameIDs']) > 0 )
			$this->compareGames('Active games', $bUser->id, $this->aLogsData['activeGameIDs']);

		print '</ul></li></ul>';
	}
	
	/**
	 * Get the time data for a userID
	 * 
	 * @param int $userID The userID to load data for
	 * @return array[int][int] $array[$day][$hour] = % of hits in that time period, from 0 to 1. $day is 1 to 7, $hour is 0 to 23
	 */
	private function timeprintLoad($userID) {
		global $DB;
	
		$userID = (int)$userID;
	
		$tabl = $DB->sql_tabl("SELECT period0, period1, period2, period3, period4, period5, period6, period7, period8, period9, period10, period11, period12, period13, period14, period15, period16, period17, period18, period19, period20, period21, period22, period23 FROM wD_UserConnections WHERE userID=".$userID);	
		
		$result = array();
		for($i=0; $i<24;$i++) $result[] = 0;
		
		while ( $row = $DB->tabl_row($tabl) )
			$result = $row;
	
		return $this->timeprintReduce($result);
	}
	
	private function timeprintSum(array $dayData) {
		
		// Sum it all up
		$sum=0;
		foreach($dayData as $hour=>$value)
			$sum += $value;
		
		return $sum;
	}
	
	/**
	 * For each cell in the timeprint array get the % of that cell that is the total, so that the whole contents of the array adds up to 1.
	 * 
	 * @return array[int][int] $array[$day][$hour] = An array of 0-1 values which adds up to 1, (or 0 if it was 0 before). $day is 1 to 7, $hour is 0 to 23
	 */
	private function timeprintReduce(array $dayData) {
		
		// Sum it all up
		$sum=$this->timeprintSum($dayData);
		
		$result = array();
		// Divide it all by the sum
		if( $sum > 0 )
			foreach($dayData as $hour=>$value)
				$result[$hour] = $value / $sum;
		
		return $result;
	}
	
	/**
	 * Multiply two timeData arrays together, getting a result which gives an indication of time overlaps.
	 * 
	 * If one of the two weeks has no data the one which does have data will be returned.
	 * 
	 * @param array $weekDataA
	 * @param array $weekDataB
	 * 
	 * @return array[int][int] $array[$day][$hour] = An array of 0-1 values which adds up to 1, (or 0 if it was 0 before). $day is 1 to 7, $hour is 0 to 23
	 */
	private function timeprintMerge(array $dayDataA, array $dayDataB) {
		$dayDataA = $this->timeprintReduce($dayDataA);
		$dayDataB = $this->timeprintReduce($dayDataB);
		
		if( $this->timeprintSum($dayDataA) == 0 )
			return $dayDataB;
		elseif( $this->timeprintSum($dayDataB) == 0 )
			return $dayDataA;
		else
		{
			$dayDataC = array();
			
			foreach($dayDataA as $hour=>$valueA)
				$dayDataC[$hour] = $valueA * $dayDataB[$hour];
			
			return $this->timeprintReduce($dayDataC);
		}
	}
	
	public function printUserTimeprint() {
		global $User;
		if (!$User->isDarkMode())
		{
			print '<style>
			.timeprintData table {
				border-top: 1px solid #aaa;
				border-left: 1px solid #aaa;
			}
			.timeprintData td {
				border-bottom: 1px solid #aaa;
				border-right: 1px solid #aaa;
				margin:0;
				font-size:90%;
				padding:0;
				text-align:center;
				background:#eee;
				font-weight:bold;
				color: #666;
			}
			.timeprintData th {
				font-weight:normal;
				border-bottom: 1px solid #aaa;
				border-right: 1px solid #aaa;
				background:#ddd;
				text-align:center;
			}
			</style>';
		}
		else
		{
			print '<style>
			.timeprintData table {
				border-top: 1px solid rgba(255, 255, 255, 0.8);
				border-left: 1px solid rgba(255, 255, 255, 0.8);
			}
			.timeprintData td {
				border-bottom: 1px solid rgba(255, 255, 255, 0.8);
				border-right: 1px solid rgba(255, 255, 255, 0.8);
				margin:0;
				font-size:90%;
				padding:0;
				text-align:center;
				background:#777;
				font-weight:bold;
				color: rgba(255, 255, 255, 0.8);
			}
			.timeprintData th {
				font-weight:normal;
				border-bottom: 1px solid rgba(255, 255, 255, 0.8);
				border-right: 1px solid rgba(255, 255, 255, 0.8);
				background:#555;
				text-align:center;
			}
			</style>';
		}
		
		print '<div class="timeprintData" style="font-size:80%"><h3>'.l_t('Timeprint data:').'</h3>';
		
		$timeprints = array();
		
		$timeprint = $this->timeprintLoad($this->aUserID);
		$timeprints[] = $timeprint;
		
		print '<h4>'.l_t('User # '.$this->aUserID.':').'</h4>'.$this->printTimeprint($timeprint);
		
		if(count($this->bUserIDs) > 0 ) {
			foreach($this->bUserIDs as $bUserID) {
				$timeprint = $this->timeprintLoad($bUserID);
				$timeprints[] = $timeprint;
				print '<h4>'.l_t('User # '.$bUserID.':').'</h4>'.$this->printTimeprint($timeprint);
			}
			
			print '<h4>'.l_t('Comparison timeprint:').'</h4>';
			$timeprintComparison = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
			foreach($timeprints as $timeprint)
				$timeprintComparison = $this->timeprintMerge($timeprintComparison, $timeprint);
			
			print $this->printTimeprint($timeprintComparison);
		}
		
		print '<div class="hr"></div>';
		
		print '</div>';
	}
	
	private function printTimeprint(array $hourData) {
		$buf = '<table>';
		
		$buf .= '<tr>';
		for($i=0;$i<24;$i++)
			$buf .= '<th>'.$i.'</th>';
		$buf .= '</tr>';
		
		foreach($hourData as $hour=>$value) {
			$value = round($value * 100).'%';
			if( $value == 0 )
				$value = '&nbsp;';
			
			$buf .= '<td>'.$value.'</td>';
		}
		$buf .= '</tr>';
			
		$buf .= '</table>';
		
		return $buf;
	}
}

?>
