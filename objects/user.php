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

require_once('objects/notice.php');

/**
 * Holds information on a user for display, or to manage certain user related functions such as logging
 * on, and preventing the same data being sent twice. Also processes user registration forms.
 *
 * TODO: I think much of this isn't used, or used rarely enough that it would be better placed elsewhere
 *
 * @package Base
 */
class User {
	public static function cacheDir($id)
	{
		return libCache::dirID('users',$id);
	}

	public static function wipeCache($id, $glob='*.*')
	{
		$dir=self::cacheDir($id);
		libCache::wipeDir($dir, $glob);
		file_put_contents($dir.'/index.html', '');
	}

	/**
	 * User ID
	 * @var int
	 */
	public $id;

	/**
	 * Username
	 * @var string
	 */
	public $username;

	/**
	 * MD5 (salted) password hex encoded
	 * @var string
	 */
	public $password;

	/**
	 * E-mail address
	 * @var string
	 */
	public $email;

	/**
	 * User type; an array of user-types, each set to true for is-a-member, false for is-not-a-member
	 * @var array
	 */
	public $type;

	/**
	 * The user-profile comment
	 * @var string
	 */
	public $comment;

	/**
	 * User-profile homepage
	 * @var string
	 */
	public $homepage;

	/**
	 * Hide-email? 'Yes'/'No'
	 *
	 * @var string
	 */
	public $hideEmail;

	/**
	 * UNIX timestamp of join-date
	 *
	 * @var int
	 */
	public $timeJoined;

	/**
	 * Locale
	 * @var string
	 */
	public $locale;

	/**
	 * UNIX timestamp from the time the last session ended
	 * @var int
	 */
	public $timeLastSessionEnded;

	/**
	 * Is this user online?
	 * @var bool
	 */
	public $online;

	/**
	 * Number of available points
	 * @var int
	 */
	public $points;

	/**
	 * Number of Missed moves and phases played by the user...
	 * @var int
	 */
	public $missedMoves;
	public $phasesPlayed;
	
	public $lastMessageIDViewed;

	/**
	 * 'No' if the player can submit mod reports, 'Yes' if they are muted
	 * @var string
	 */
	public $muteReports;

	/**
	 * Give this user a supplement of points
	 *
	 * @param $userID The user ID
	 * @param $pointsWon The number of points won, if any
	 * @param $bet The amount bet into the game
	 * @param $gameID The game ID
	 * @param $points The number of points the user has saved
	 * @return int The amount awarded back
	 */
	public static function pointsSupplement($userID, $pointsWon, $bet, $gameID, $points)
	{
		global $DB;
		//10,23,105
		// If the user is winning points, and there is a chance they are winning fewer than they bet,
		// this function is needed to make sure no-one runs out of points completely, by making sure
		// all players have at least 100 points, including active bets in active games.

		$pointsInPlay = self::pointsInPlay($userID, $gameID); // Points in 'Playing'/'Left' games except $gameID

		if ( 100 <= ($pointsInPlay + $pointsWon + $points))
			return 0; // This member is doing fine, doesn't need topping up

		$supplement = (100 - ($pointsInPlay + $pointsWon + $points)); // The maximum supplement
		//19 = 100 - (_ + 10 + 71)

		// You can't be supplemented back more than you bet in
		if( $supplement > $bet ) $supplement = $bet;

		self::pointsTransfer($userID, 'Supplement', $supplement, $gameID);

		return $supplement;
	}

	public static function pointsTransfer($userID, $transferType, $points, $gameID='NULL', $memberID='NULL')
	{
		global $DB;

		assert('$points >= 0');

		// 'Won','Bet','Cancel','Supplement'
		if($transferType == 'Won')
		{
			// Won doesn't mean they won, this could be 0, it's just the transaction type

			/*
			 * It is expected that if they won less than they bet they have already been topped up the
			 * 100-minimum-points-supplement, and are now only being paid what they won from the game.
			 * This figure doesn't include any supplements they've already received.
			 */

			$DB->sql_put("UPDATE wD_Members SET pointsWon = ".$points." WHERE userID = ".$userID." AND gameID = ".$gameID);

		}

		if ( $transferType == 'Cancel' )
			$DB->sql_put("DELETE FROM wD_PointsTransactions
				WHERE userID = ".$userID." AND gameID = ".$gameID);
		else
			$DB->sql_put("INSERT INTO wD_PointsTransactions ( userID, type, points, gameID, memberID )
				VALUES ( ".$userID.", '".$transferType."', ".$points.", ".$gameID.", ".$memberID." )");

		if ( $transferType == 'Bet' )
		{
			$DB->sql_put("UPDATE wD_Users SET points = points - ".$points." WHERE id = ".$userID);
			$DB->sql_put("UPDATE wD_Games SET pot = pot + ".$points." WHERE id = ".$gameID);
			$DB->sql_put("UPDATE wD_Members SET bet = ".$points." WHERE id = ".$memberID);
		}
		elseif ( $transferType == 'Cancel' )
		{
			$DB->sql_put("UPDATE wD_Users SET points = points + ".$points." WHERE id = ".$userID);
			$DB->sql_put("UPDATE wD_Games SET pot = IF(pot > ".$points.",(pot - ".$points."),0) WHERE id = ".$gameID);
		}
		else
			$DB->sql_put("UPDATE wD_Users SET points = points + ".$points." WHERE id = ".$userID);
	}

	/**
	 * Find the ID of the user which has the given e-mail, or return 0 if it doesn't exist
	 * *Does not filter input!*
	 *
	 * @param $email
	 * @return int
	 */
	public static function findEmail($email)
	{
		global $DB;

		list($id) = $DB->sql_row("SELECT id FROM wD_Users WHERE email='".$email."'");

		if ( isset($id) and $id )
			return $id;
		else
			return 0;
	}

	/**
	 * Find the ID of the user which has the given username, or return 0 if it doesn't exist
	 * *Does not filter input!*
	 *
	 * @param $username
	 * @return int
	 */
	public static function findUsername($username)
	{
		global $DB;

		list($id) = $DB->sql_row("SELECT id FROM wD_Users WHERE username='".$username."'");

		if ( isset($id) and $id )
			return $id;
		else
			return 0;
	}

	/**
	 * Filter a registration/user control panel form. An exception is thrown if
	 * data can't be filtered. An array of variables usable in SQL are returned.
	 *
	 * @param array $input An array of unfiltered data from a registration/control panel form
	 * @return array An array of filtered SQL insertable data
	 */
	public static function processForm($input, &$errors)
	{
		global $DB;

		$SQLVars = array();

		$available = array('username'=>'', 'password'=>'', 'passwordcheck'=>'', 'email'=>'',
					'hideEmail'=>'','showEmail'=>'', 'locale'=>'','homepage'=>'','comment'=>'');

		$userForm = array();

		foreach($available as $name=>$val)
		{
			if ( isset($input[$name]) and $input)
			{
				$userForm[$name] = $input[$name];
			}
		}

		if( isset($userForm['username']) )
		{
			$SQLVars['username'] = $DB->escape($userForm['username']);
		}

		if( isset($userForm['password']) and $userForm['password'] )
		{
			if ( isset($userForm['passwordcheck'])
				and $userForm['password'] == $userForm['passwordcheck'] )
			{
				$SQLVars['password'] = "UNHEX('".libAuth::pass_Hash($userForm['password'])."')";
			}
			else
			{
				$errors[] = "The two passwords do not match";
			}
		}

		if(isset($userForm['email']) and $userForm['email'] )
		{
			$userForm['email'] = $DB->escape($userForm['email']);
			if( !libAuth::validate_email($userForm['email']) )
			{
				$errors[] = "The e-mail address you entered isn't valid. Please enter a valid one";
			}
			else
			{
				$SQLVars['email'] = $userForm['email'];
			}
		}

		if( isset($userForm['hideEmail']) )
		{
			if ( $userForm['hideEmail'] == "Yes" )
			{
				$SQLVars['hideEmail'] = "Yes";
			}
			else
			{
				$SQLVars['hideEmail'] = "No";
			}
		}

		if( isset($userForm['homepage']) AND $userForm['homepage'] )
		{
			$userForm['homepage'] = $DB->escape($userForm['homepage']);

			$SQLVars['homepage'] = $userForm['homepage'];
		}

		if(isset($userForm['comment']) AND $userForm['comment'] )
		{
			$userForm['comment'] = $DB->msg_escape($userForm['comment']);

			$SQLVars['comment'] = $userForm['comment'];
		}

		if( isset($userForm['locale']) )
		{
			if( !in_array($userForm['locale'], Config::$availablelocales) )
			{
				$errors[] = "Specified locale not available";
			}
			else
			{
				$SQLVars['locale'] = $userForm['locale'];
			}
		}

		return $SQLVars;
	}

	/**
	 * Initialize a user object
	 *
	 * @param int $id User ID
	 * @param string|bool[optional] $username Look the user up based on username instead of user ID
	 */
	function __construct($id, $username=false)
	{
		if ( $username )
		{
			$this->load($username);
		}
		else
		{
			$this->id = intval($id);
			$this->load();
		}
	}

	/**
	 * Load the User object class fields. It is assumed that username is already escaped.
	 *
	 * @param string|bool[optional] If the username is given it is being used instead of ID to load the User *Not filtered*
	 */
	function load($username=false)
	{
		global $DB;

		$row = $DB->sql_hash("SELECT
			u.id,
			u.username,
			LOWER(HEX(u.password)) as password,
			u.email,
			u.type,
			u.comment,
			u.homepage,
			u.hideEmail,
			u.timeJoined,
			u.locale,
			u.timeLastSessionEnded,
			u.points,
			u.lastMessageIDViewed,
			u.muteReports,
			u.missedMoves,
			u.phasesPlayed,			
			IF(s.userID IS NULL,0,1) as online
			FROM wD_Users u
			LEFT JOIN wD_Sessions s ON ( u.id = s.userID )
			WHERE ".( $username ? "u.username='".$username."'" : "u.id=".$this->id ));

		if ( ! isset($row['id']) or ! $row['id'] )
		{
			throw new Exception("A user object has been created which doesn't represent a real user.");
		}

		foreach( $row as $name=>$value )
		{
			$this->{$name} = $value;
		}

		// Convert an array of types this user has into an array of true/false indexed by type
		$this->type = explode(',', $this->type);
		$validTypes = array('System','Banned','User','Moderator','Guest','Admin','Donator','DonatorBronze','DonatorSilver','DonatorGold','DonatorPlatinum');
		$types = array();
		foreach($validTypes as $type)
		{
			if ( in_array($type, $this->type) )
			{
				$types[$type] = true;
			}
			else
			{
				$types[$type] = false;
			}
		}
		$this->type = $types;

		$this->online = (bool) $this->online;
	}

	/**
	 * Return a profile link for this user
	 * @param bool[optional] $welcome If true this profile link is tweaked to be used as the Welcome link
	 * @return string Profile link HTML
	 */
	function profile_link($welcome = false)
	{
		$buffer = '';

		if ( $this->type['User'] )
		{
			$buffer .= '<a href="./profile.php?userID='.$this->id.'"';

			$buffer.='>'.$this->username;

			if ( !$welcome and $this->online )
				$buffer.= libHTML::loggedOn($this->id);

			$buffer.=' ('.$this->points.libHTML::points().$this->typeIcon($this->type).')</a>';
		}
		else
		{
			$buffer .= '<em>'.$this->username.'</em>';
		}

		return $buffer;
	}

	static function typeIcon($type) {
		// This must take either a list as it comes from a SQL query, or a built-in $this->type['Admin'] style array
		if( is_array($type) ) {
			$types=array();

			foreach($type as $n=>$v)
				if($v) $types[]=$n;

			$type = implode(',',$types);
		}

		$buf='';

		//if( strstr($type,'Moderator') )
		//	$buf .= ' <img src="images/icons/mod.png" alt="Mod" title="Moderator" />';
		//else
		if(strstr($type,'Banned') )
			$buf .= ' <img src="images/icons/cross.png" alt="X" title="Banned" />';

		if( strstr($type,'DonatorPlatinum') )
			$buf .= libHTML::platinum();
		elseif( strstr($type,'DonatorGold') )
			$buf .= libHTML::gold();
		elseif( strstr($type,'DonatorSilver') )
			$buf .= libHTML::silver();
		elseif( strstr($type,'DonatorBronze') )
			$buf .= libHTML::bronze();

		return $buf;
	}

	function sendNotice($keep, $private, $message)
	{
		global $DB;

		$message=$DB->escape($message,true);

		notice::send($this->id, 1, 'User', $keep,$private, $message, 'GameMaster');
	}

	function sendPM(User $FromUser, $message)
	{
		global $DB;

		$message = htmlentities( $message, ENT_NOQUOTES, 'UTF-8');
		require_once('lib/message.php');
		$message = message::linkify($message);

		if( $this->isUserMuted($FromUser->id) )
		{
			notice::send($FromUser->id, $this->id, 'PM', 'No', 'Yes',
				'Could not deliver message, user has muted you.', 'To: '.$this->username,
				$this->id);
		}
		else
		{
			notice::send($this->id, $FromUser->id, 'PM', 'Yes', 'Yes',
				$message, $FromUser->username, $FromUser->id);

			notice::send($FromUser->id, $this->id, 'PM', 'No', 'Yes',
				'You sent: <em>'.$message.'</em>', 'To: '.$this->username,
				$this->id);
		}
	}

	/**
	 * The time this user joined
	 * @return string Date joined
	 */
	function timeJoinedtxt()
	{
		return libTime::text($this->timeJoined);
	}

	/**
	 * Log-on, create/update a session record, and take information for user access logging for meta-gamers
	 */
	function logon()
	{
		global $DB;

		session_name('wD_Sess_User-'.$this->id);

		/*if( $this->type['User'] )
			session_cache_limiter('private_no_expire');
		else
			session_cache_limiter('public');*/

		session_start();

		// Non-users can't get banned
		if( $this->type['Guest'] ) return;

		if ( isset($_SERVER['HTTP_USER_AGENT']) )
			$userAgentHash = substr(md5($_SERVER['HTTP_USER_AGENT']),0,4);
		else
			$userAgentHash = '0000';

		if ( ! isset($_COOKIE['wD_Code']) or intval($_COOKIE['wD_Code']) == 0 or intval($_COOKIE['wD_Code']) == 1 )
		{
			// Making this larger than 2^31 makes it negative..
			$cookieCode = rand(2, 2000000000);
			setcookie('wD_Code', $cookieCode,time()+365*7*24*60*60);
		}
		else
		{
			$cookieCode = (int) $_COOKIE['wD_Code'];
		}

		if($this->type['Banned'])
			libHTML::notice('Banned', 'You have been banned from this server. If you think there has been
					a mistake contact '.Config::$adminEMail.' .');

		/*
		$bans=array();
		$tabl = $DB->sql_tabl("SELECT numberType, number, userID FROM wD_BannedNumbers
			WHERE ( number = INET_ATON('".$_SERVER['REMOTE_ADDR']."') AND numberType='IP')
				OR ( number = ".$cookieCode." AND numberType='CookieCode')
				OR ( userID=".$this->id.")");
		while(list($banType,$banNum)=$DB->tabl_row($tabl))
			$bans[$banType]=$banNum;

		if($this->type['Banned'])
		{
			//if( isset($bans['IP']) and $cookieCode!=$bans['CookieCode'] )
				//setcookie('wD_Code', $bans['CookieCode'],time()+365*7*24*60*60);

			if(!isset($bans['IP']) || ip2long($_SERVER['REMOTE_ADDR'])!=$bans['IP'])
				self::banIP(ip2long($_SERVER['REMOTE_ADDR']), $this->id);

			libHTML::notice('Banned', 'You have been banned from this server. If you think there has been
					a mistake contact '.Config::$adminEMail.' .');
		}
		elseif( isset($bans['IP']) )
		{
			self::banUser($this->id,"You share an IP with a banned user account.", $_SERVER['REMOTE_ADDR']);
			libHTML::notice('Banned', 'You have been banned from this server. If you think there has been
				a mistake contact '.Config::$adminEMail.' .');
		}*/

		$DB->sql_put("INSERT INTO wD_Sessions (userID, lastRequest, hits, ip, userAgent, cookieCode)
					VALUES (".$this->id.",CURRENT_TIMESTAMP,1, INET_ATON('".$_SERVER['REMOTE_ADDR']."'),
							UNHEX('".$userAgentHash."'), ".$cookieCode." )
					ON DUPLICATE KEY UPDATE hits=hits+1");

		$this->online = true;
	}

	public static function banIP($ip, $userID=-1)
	{
		global $DB;

		if($userID<=0) $userID="NULL";

		$DB->sql_put("INSERT IGNORE INTO wD_BannedNumbers (number,numberType,userID,hasResponded)
				VALUES (INET_ATON('".$ip."'),'IP',".$userID.",'No')");
	}

	public static function banUser($userID, $reason=null, $ip=0)
	{
		global $DB;

		if( $reason )
		{
			$reason=$DB->msg_escape($reason);
			$comment = "comment='".$reason."', ";
		}
		else
			$comment = '';

		$DB->sql_put("UPDATE wD_Users SET ".$comment." type='Banned', points=0 WHERE id = ".$userID);

		if($ip)
			self::banIP($ip, $userID);
	}

	public function rankingDetails()
	{
		global $DB, $Misc;

		$rankingDetails = array();

		list($rankingDetails['position']) = $DB->sql_row("SELECT COUNT(id)+1
			FROM wD_Users WHERE points > ".$this->points);

		list($rankingDetails['worth']) = $DB->sql_row(
			"SELECT SUM(bet) FROM wD_Members WHERE userID = ".$this->id." AND status = 'Playing'");

		$rankingDetails['worth'] += $this->points;

		$tabl = $DB->sql_tabl(
				"SELECT COUNT(id), status FROM wD_Members WHERE userID = ".$this->id." GROUP BY status"
			);

		$rankingDetails['stats'] = array();
		while ( list($number, $status) = $DB->tabl_row($tabl) )
		{
			$rankingDetails['stats'][$status] = $number;
		}

		$tabl = $DB->sql_tabl( "SELECT COUNT(m.id), m.status, SUM(m.bet) FROM wD_Members AS m
					INNER JOIN wD_Games AS g ON m.gameID = g.id
					WHERE m.userID = ".$this->id."
						AND g.phase != 'Finished'
						AND g.anon = 'Yes'
					GROUP BY status");
		$points=0;
		while ( list($number, $status, $bets) = $DB->tabl_row($tabl) )
		{
			$points += $bets;
			$rankingDetails['anon'][$status] = $number;
		}
		$rankingDetails['anon']['points'] = $points;

		list($rankingDetails['takenOver']) = $DB->sql_row(
			"SELECT COUNT(c.userID) FROM wD_CivilDisorders c
			INNER JOIN wD_Games g ON ( g.id = c.gameID )
			LEFT JOIN wD_Members m ON ( c.gameID = m.gameID and c.userID = ".$this->id." )
			WHERE c.userID = ".$this->id." AND m.userID IS NULL"
			);


		$rankingDetails['rankingPlayers'] = $Misc->RankingPlayers;

		// Prevent division by 0 when server is new
		$rankingPlayers = ( $rankingDetails['rankingPlayers'] == 0 ? 1 : $rankingDetails['rankingPlayers'] );

		// Calculate the percentile of the player. Smaller is better.
		$rankingDetails['percentile'] = ceil(100.0*$rankingDetails['position'] / $rankingPlayers);

		$rankingDetails['rank'] = 'Political puppet';

		$ratings = array('<strong>Diplomat</strong>' => 5,
						'Mastermind' => 10,
						'Pro' => 20,
						'Experienced' => 50,
						'Member' => 90,
						'Casual player' => 100 );

		foreach($ratings as $name=>$limit)
		{
			if ( $rankingDetails['percentile'] <= $limit )
			{
				$rankingDetails['rank'] = $name;
				break;
			}
		}

		return $rankingDetails;
	}

	static function pointsInPlay($userID, $excludeGameID=false)
	{
		global $DB;

		list($pointsInPlay) = $DB->sql_row(
			"SELECT SUM(m.bet) FROM wD_Members m ".
				($excludeGameID?"INNER JOIN wD_Games g ON ( m.gameID = g.id ) ":'')."
			WHERE (m.userID = ".$userID.") ".
				($excludeGameID?"AND ( NOT m.gameID = ".$excludeGameID." ) ":"")."
				AND ( m.status = 'Playing' OR m.status = 'Left' )
			GROUP BY m.userID");

		if ( !isset($pointsInPlay) || !$pointsInPlay )
			return 0;
		else
			return $pointsInPlay;
	}

	public function getMuteUsers() {
		global $DB;

		static $muteUsers;
		if( isset($muteUsers) ) return $muteUsers;
		$muteUsers = array();

		$tabl = $DB->sql_tabl("SELECT muteUserID FROM wD_MuteUser WHERE userID=".$this->id);
		while(list($muteUserID) = $DB->tabl_row($tabl))
			$muteUsers[] = $muteUserID;

		return $muteUsers;
	}
	public function isUserMuted($muteUserID) {
		return in_array($muteUserID,$this->getMuteUsers());
	}
	public function toggleUserMute($muteUserID) {
		global $DB;
		$muteUserID = (int)$muteUserID;
		if( $this->isUserMuted($muteUserID) )
			$DB->sql_put("DELETE FROM wD_MuteUser WHERE userID=".$this->id." AND muteUserID=".$muteUserID);
		else
			$DB->sql_put("INSERT INTO wD_MuteUser (userID, muteUserID) VALUES (".$this->id.",".$muteUserID.")");
	}
	public function getMuteCountries($gameID=-1) {
		global $DB;
		$gameID = (int) $gameID;

		static $muteCountries;
		if( !isset($muteCountries) ) $muteCountries = array();
		if( isset($muteCountries[$gameID]) ) return $muteCountries[$gameID];

		$muteCountries[$gameID] = array();
		$tabl = $DB->sql_tabl("SELECT gameID, muteCountryID FROM wD_MuteCountry WHERE userID=".$this->id.($gameID>0?" AND gameID=".$gameID:''));

		while(list($muteGameID,$muteCountryID) = $DB->tabl_row($tabl))
		{
			if( $gameID<0 ) // No game ID given, we are collecting all game IDs
				$muteCountries[$gameID][] = array($muteGameID, $muteCountryID);
			else // Game ID given, this is for just one game ID
				$muteCountries[$gameID][] = $muteCountryID;
		}

		return $muteCountries[$gameID];
	}
	public function getLikeMessages() {
		global $DB;

		static $likeMessages;
		if( !isset($likeMessages) ) $likeMessages = array();
		else return $likeMessages;

		$tabl = $DB->sql_tabl("SELECT likeMessageID FROM wD_LikePost WHERE userID=".$this->id);

		while(list($likeMessageID) = $DB->tabl_row($tabl))
			$likeMessages[] = $likeMessageID;

		return $likeMessages;
	}
	public function likeMessageToggleLink($messageID, $fromUserID=-1) {
		
		if( $this->type['User'] && $this->id != $fromUserID && !in_array($messageID, $this->getLikeMessages()))
			return '<a id="likeMessageToggleLink'.$messageID.'" 
			href="#" title="Give a mark of approval for this post" class="light likeMessageToggleLink" '.
			'onclick="likeMessageToggle('.$this->id.','.$messageID.',\''.libAuth::likeToggleToken($this->id, $messageID).'\'); '.
			'return false;">'.
			'+1</a>';
		else return '';
	}
	public function getMuteThreads($refresh=false) {
		global $DB;

		static $muteThreads;
		if( $refresh || !isset($muteThreads) ) $muteThreads = array();
		else return $muteThreads;

		$tabl = $DB->sql_tabl("SELECT muteThreadID FROM wD_MuteThread WHERE userID=".$this->id);

		while(list($muteThreadID) = $DB->tabl_row($tabl))
			$muteThreads[] = $muteThreadID;

		return $muteThreads;
	}
	
	public function isThreadMuted($threadID) {
		return in_array($threadID,$this->getMuteThreads($threadID));
	}
	public function toggleThreadMute($threadID) {
		global $DB;
		
		if( $this->isThreadMuted($threadID)) 
			$DB->sql_put("DELETE FROM wD_MuteThread WHERE userID = ".$this->id." AND muteThreadID=".$threadID);
		else
			$DB->sql_put("INSERT INTO wD_MuteThread (userID, muteThreadID) VALUES (".$this->id.", ".$threadID.")");
	
		$this->getMuteThreads(true);
	}
	public function isCountryMuted($gameID, $muteCountryID) {
		return in_array($muteCountryID,$this->getMuteCountries($gameID));
	}
	public function toggleCountryMute($gameID,$muteCountryID) {
		global $DB;
		$gameID = (int)$gameID;
		$muteCountryID = (int)$muteCountryID;

		if( $this->isCountryMuted($gameID,$muteCountryID) )
			$DB->sql_put("DELETE FROM wD_MuteCountry WHERE userID=".$this->id." AND gameID=".$gameID." AND muteCountryID=".$muteCountryID);
		else
			$DB->sql_put("INSERT INTO wD_MuteCountry (userID, gameID, muteCountryID) VALUES (".$this->id.",".$gameID.",".$muteCountryID.")");

	}
	
	/**
	 * Get a user's reliability rating.  Reliability rating is 100 minus phases missed / phases played * 200, not to be lower than 0
	 * Examples: If a user misses 5% of their games, rating would be 90, 15% would be 70, etc.  Certain features of the site (such as creating and joining games) will be restricted if the reliability rating is too low.
	 * @return reliability
	 */
	public function getReliability()
	{
		if ($this->phasesPlayed == 0) {
			$reliability = 100;
		} else {
			$reliability = ceil(100 - $this->missedMoves / $this->phasesPlayed * 200);
			if ($reliability < 0) $reliability = 0;
		}
		return $reliability;
	}
	
	/**
	 * Count how many uncompleted games a user has...
	 */
	function getUncompletedGames()
	{
		global $DB;		
		list($number) = $DB->sql_row("SELECT COUNT(*) FROM wD_Members m, wD_Games g WHERE m.userID=".$this->id." and m.gameID=g.id and g.phase!='Finished'");
		return $number;
	}
	
	/**
	 * Check if the users reliability is high enough to join/create more games
	 * @return true or error message	 
	 */
	function isReliable()
	{
		$reliability = $this->getReliability();
		$maxGames = ceil($reliability / 10);
		$totalGames = $this->getUncompletedGames();
		if ($maxGames < 10) { // If the rating is 90 or above, there is no game limit restriction
			if ( $reliability == 0 )
				return "<p>NOTICE: You are not allowed to join or create any games given your reliability rating of ZERO (meaning you have missed more than 50% of your orders across all of your games)</p><p>You can improve your reliability rating by not missing any orders, even if it's just saving the default 'Hold' for everything.</p><p>If you are not currently in a game and cannot join one because of this restriction, then you may contact an <a href=\"profile.php?userID=5\">admin</a> and briefly explain your extremely low rating.  The admin, at his or her discretion, may set your reliability rating high enough to allow you 1 game at a time.  By consistently putting in orders every turn in that new game, your reliability rating will improve enough to allow you more simultaneous games.  2-player variants are not affected by this restriction.</p>";
			elseif ( $totalGames >= $maxGames ) // Can't have more than reliability rating / 10 games up
				return "<p>NOTICE: You cannot join or create a new game, because you seem to be having trouble keeping up with the orders in the ones you already have</p><p>You can improve your reliability rating by not missing any orders, even if it's just saving the default 'Hold' for everything.</p><p>Please note that if you are marked as 'Left' for a game, your rating will continue to take hits until someone takes over for you.</p><p>Your current rating of <strong>".$reliability."</strong> allows you to have no more than <strong>".$maxGames."</strong> concurrent games before you see this message.  Every 10 reliability points will allow you an additional game. 2-player variants are not affected by this restriction.</p>";
		}
		elseif ( $totalGames > 1 && $this->phasesPlayed / $totalGames < 3 ) // This will prevent newbies from joining 10 games and then leaving right away.  Everyone can join 2 without any restrictions, then they can join more after they've played them for 3 phases.  
			return "<p>You're taking on too many games at once for a new member.  Please relax and enjoy the game or games that you are currently in before joining/creating a new one.  You need to play <strong>".($totalGames*3-$this->phasesPlayed)."</strong> more phases (across all your games) before you can take on another game.  The quickest way to do this is to leave any pre-games you might be in and take over a civil disorder power from another game. 2-player variants are not affected by this restriction.</p>";
	}
	
	public function ReliabilityAsString()
	{
		$reliability = $this->getReliability();
		if ($reliability >= 90)
			$relColor = 'blue';
		elseif ($reliability >= 80)
			$relColor = 'green';
		elseif ($reliability >= 50)
			$relColor = 'orange';
		else
			$relColor = 'red';			
		return '<span style="color: '.$relColor.'">'.$reliability.'% (missed '.$this->missedMoves.' of '.$this->phasesPlayed.' phases)</span>';
	}
	
}
?>
