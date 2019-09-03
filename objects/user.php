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

require_once(l_r('objects/notice.php'));
require_once(l_r('objects/useroptions.php'));
require_once(l_r('objects/basic/set.php'));

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
	
	public function getSilences() {
		global $DB;
		
		$tabl = $DB->sql_tabl("SELECT 
			silence.id as silenceID,
			silence.userID as silenceUserID,
			silence.postID as silencePostID,
			silence.moderatorUserID as silenceModeratorUserID,
			silence.enabled as silenceEnabled,
			silence.startTime as silenceStartTime,
			silence.length as silenceLength,
			silence.reason as silenceReason
		FROM wD_Silences silence
		WHERE silence.userID = ".$this->id."
		ORDER BY silence.startTime DESC");
		
		$silences = array();
		while( $record = $DB->tabl_hash($tabl) )
			$silences[] = new Silence($record);
		
		return $silences;
	}
	
	private $ActiveSilence;
	
	public function isSilenced() {
		if( !$this->silenceID ) 
			return false;
		
		$ActiveSilence = new Silence($this->silenceID);
		
		if( $ActiveSilence->isEnabled() ) {
			$this->ActiveSilence = $ActiveSilence;
			return true;
		}
		else
			return false;
	}
	public function getActiveSilence() {
		
		if( !$this->isSilenced() ) return null;
		else return $this->ActiveSilence;
		
	}
	
	/**
	* Silence ID; the ID of the last silence set to this user (may be expired / disabled since)
	* @var int/null
	*/
	public $silenceID;
	
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
	 * Notification flags; an array of notification flags, each set to true if notification should be done.
	 * @var array
	 */
	public $notifications;

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
	 * The reason a user was temp banned
	 * @var string
	 */
	public $tempBanReason;

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
	 * The user's options
	 * @var UserOptions
	 */
	public $options;

	/*
	 * The user is blocked from joining or creating new games till the given time
	 * @var timestamp
	 */
	public $tempBan;

	/**
	 * UNIX timestamp of when a mod last checked this user
	 * @var int
	 */
	public $modLastCheckedOn;

	/**
	 * userID of the last mod to check this user
	 * @var int
	 */
	public $modLastCheckedBy;

	/**
	 * date the user last used an emergency pause
	 * @var int
	 */
	public $emergencyPauseDate;

	/**
	 * number of phases the user has played this year
	 * @var int
	 */
	public $yearlyPhaseCount;
	
	/**
	 * Number of available points
	 * @var int
	 */
	public $points;

	public $lastMessageIDViewed;

	/**
	 * 'No' if the player can submit mod reports, 'Yes' if they are muted
	 * @var string
	 */
	public $muteReports;
	
	/**
	 * The users reliability stats; civil disorders, nmrs, civil disorders taken over, phases where moves could have been submitted, games, reliability rating.
	 * 
	 * Generated in libGameMaster
	 * 
	 * @var int/double
	 */
	public $cdCount, $nmrCount, $cdTakenCount, $phaseCount, $gameCount, $reliabilityRating;

	/**
	 * darkMode
	 * Choose css style theme
	 * @var 'yes' or 'no'
	 */
	public $darkMode;

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

		$userPassed = new User($userID);

		// If the user is winning points, and there is a chance they are winning fewer than they bet,
		// this function is needed to make sure no-one runs out of points completely, by making sure
		// all players have at least 100 points, including active bets in active games.

		$pointsInPlay = self::pointsInPlay($userID, $gameID); // Points in 'Playing'/'Left' games except $gameID

		if ( 100 <= ($pointsInPlay + $pointsWon + $points)) return 0;
		
		// Bot's don't need points.
		if ($userPassed->type['Bot']) return 0;

		$supplement = (100 - ($pointsInPlay + $pointsWon + $points)); // The maximum supplement, 19 = 100 - (_ + 10 + 71)

		// You can't be supplemented back more than you bet in
		if( $supplement > $bet ) $supplement = $bet;

		self::pointsTransfer($userID, 'Supplement', $supplement, $gameID);

		return $supplement;
	}

	public static function pointsTransfer($userID, $transferType, $points, $gameID='NULL', $memberID='NULL')
	{
		global $DB;

		assert('$points >= 0');

		$userPassed = new User($userID);

		// Always adjust banned members to 0 points. 
		if ($userPassed->type['Banned']) 
		{
			$DB->sql_put("UPDATE wD_Users SET points = 0 WHERE id = ".$userID);
			return;
		}

		// Bot's don't need points.
		if ($userPassed->type['Bot']) 
		{
			if ( $transferType == 'Bet' )
			{
				$DB->sql_put("UPDATE wD_Games SET pot = pot + 5 WHERE id = ".$gameID);
				$DB->sql_put("UPDATE wD_Members SET bet = 5 WHERE id = ".$memberID);
			}

			elseif ( $transferType == 'Cancel' )
			{
				$DB->sql_put("UPDATE wD_Games SET pot = IF(pot > 5,(pot - 5),0) WHERE id = ".$gameID);
			}
			return;
		}
		
		// 'Won','Bet','Cancel','Supplement', Won doesn't mean they won, this could be 0, it's just the transaction type
		if($transferType == 'Won')
		{
			/*
			 * It is expected that if they won less than they bet they have already been topped up the
			 * 100-minimum-points-supplement, and are now only being paid what they won from the game.
			 * This figure doesn't include any supplements they've already received.
			 */

			$DB->sql_put("UPDATE wD_Members SET pointsWon = ".$points." WHERE userID = ".$userID." AND gameID = ".$gameID);
		}

		if ( $transferType == 'Cancel' ) $DB->sql_put("DELETE FROM wD_PointsTransactions WHERE userID = ".$userID." AND gameID = ".$gameID);

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
		{
			// Prevent mods from trying to dock more points than a user has, throwing an exception. Just dock the user to 0.
			if (($points < 0 ) && ($this->points + $points) < 0 ) { $DB->sql_put("UPDATE wD_Users SET points = 0 WHERE id = ".$userID); }
			else { $DB->sql_put("UPDATE wD_Users SET points = points + ".$points." WHERE id = ".$userID); }
		}
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

		if ( isset($id) and $id ) return $id;
		else return 0;
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

		if ( isset($id) and $id ) return $id;
		else return 0;
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
					'hideEmail'=>'','showEmail'=>'', 'homepage'=>'','comment'=>'', 'darkMode'=>'');

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
			$SQLVars['username'] = trim($DB->escape($userForm['username']));
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
				$errors[] = l_t("The two passwords do not match");
			}
		}

		if(isset($userForm['email']) and $userForm['email'] )
		{
			$userForm['email'] = trim($DB->escape($userForm['email']));
			if( !libAuth::validate_email($userForm['email']) )
			{
				$errors[] = l_t("The e-mail address you entered isn't valid. Please enter a valid one");
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

		if(isset($userForm['darkMode']))
		{
			if ($userForm['darkMode'] == "Yes")
				$SQLVars['darkMode'] = "Yes";
			else
				$SQLVars['darkMode'] = "No";
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
			u.timeLastSessionEnded,
			u.points,
			u.lastMessageIDViewed,
			u.muteReports,
			u.silenceID,
			u.notifications,
			u.cdCount,
			u.nmrCount,
			u.cdTakenCount,
			u.phaseCount,
			u.gameCount,
			u.reliabilityRating,
			u.tempBan,
			IF(s.userID IS NULL,0,1) as online,
			u.deletedCDs, 
			c.modLastCheckedOn,
			c.modLastCheckedBy,
			u.emergencyPauseDate, 
			u.yearlyPhaseCount,
			u.tempBanReason
			FROM wD_Users u
			LEFT JOIN wD_Sessions s ON ( u.id = s.userID )
			LEFT JOIN wD_UserConnections c on ( u.id = c.userID )
			WHERE ".( $username ? "u.username='".$username."'" : "u.id=".$this->id ));

		if ( ! isset($row['id']) or ! $row['id'] )
		{
			throw new Exception(l_t("A user object has been created which doesn't represent a real user."));
		}

		foreach( $row as $name=>$value )
		{
			$this->{$name} = $value;
		}
		// For display, cdCount should include deletedCDs
		$this->{'cdCount'} = $this->{'cdCount'} + $this->{'deletedCDs'};
		// RR should be rounded
		$this->reliabilityRating = round($this->reliabilityRating);

		// Convert an array of types this user has into an array of true/false indexed by type
		$this->type = explode(',', $this->type);
		$validTypes = array('System','Banned','User','Moderator','Guest','Admin','Donator','DonatorBronze','DonatorSilver','DonatorGold','DonatorPlatinum','ForumModerator','Bot');
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

		$this->notifications=new setUserNotifications($this->notifications);

		$this->online = (bool) $this->online;

		$this->options = new UserOptions($this->id);
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

		global $User;

		if( strstr($type,'Moderator') )
		{
			if ($User->getTheme() == 'No' || $User->getTheme() == null)
			{
				$buf .= ' <img src="'.l_s('images/icons/mod.png').'" alt="'.l_t('Mod').'" title="'.l_t('Moderator/Admin').'" />';
			}
			else
			{
				$buf .= ' <img src="'.l_s('images/icons/mod3.png').'" alt="'.l_t('Mod').'" title="'.l_t('Moderator/Admin').'" />';
			}
		}
				
		elseif(strstr($type,'Banned') )
			$buf .= ' <img src="'.l_s('images/icons/cross.png').'" alt="X" title="'.l_t('Banned').'" />';

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
		$message = htmlentities( $message, ENT_NOQUOTES, 'UTF-8');
		require_once(l_r('lib/message.php'));
		$message = message::linkify($message);

		if( $FromUser->isSilenced() )
        {
			notice::send($FromUser->id, $this->id, 'PM', 'No', 'Yes',
                l_t('Could not deliver message, you are currently silenced.') .'('. $FromUser->getActiveSilence()->reason .')', l_t('To:') .' '. $this->username,
                $this->id);
            return false;
        }
		else if( $this->isUserMuted($FromUser->id) )
		{
			notice::send($FromUser->id, $this->id, 'PM', 'No', 'Yes',
				l_t('Could not deliver message, user has muted you.'), l_t('To:').' '.$this->username,
				$this->id);
			return false;
		}
		else
		{
			notice::send($this->id, $FromUser->id, 'PM', 'Yes', 'Yes',
				$message, $FromUser->username, $FromUser->id);

			$this->setNotification('PrivateMessage');

			notice::send($FromUser->id, $this->id, 'PM', 'No', 'Yes',
				l_t('You sent:').' <em>'.$message.'</em>', l_t('To:').' '.$this->username,
				$this->id);
			return true;
		}
	}

	/**
	 * This will set a notification value in both the object and wd_users table if not already set.
	 * @param notification notification value to set, must be 'PrivateMessage', 'GameMessage', 'Unfinalized', or 'GameUpdate'.
	 **/
	function setNotification($notification)
	{
		global $DB;

		$this->notifications->$notification = true;
		if ($this->notifications->updated)
		{
			$DB->sql_put("UPDATE wD_Users SET notifications = CONCAT_WS(',',notifications,'".$notification."') WHERE id = ".$this->id);
			$this->notifications->updated = false;
		}
	}

        /**
	 * This will clear a notification value in both the object and the wd_users table if not already cleared.
	 * @param notification notification value to clear, must be 'PrivateMessage', 'GameMessage', 'Unfinalized', or 'GameUpdate'.
	 **/
	function clearNotification($notification)
	{
		global $DB;

		$this->notifications->$notification = false;
		if ($this->notifications->updated)
		{
			$DB->sql_put("UPDATE wD_Users SET notifications = REPLACE(notifications,'".$notification."','') WHERE id = ".$this->id);
			$this->notifications->updated = false;
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

	function timeModLastCheckedtxt()
	{
		return libTime::text($this->modLastCheckedOn);
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
			libHTML::notice(l_t('Banned'), l_t('You have been banned from this server. If you think there has been a mistake contact the moderator team at %s , and if you still aren\'t satisfied contact the admin at %s (with details of what happened).',Config::$modEMail, Config::$adminEMail));

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

	/**
	 * Temporary prevent a user from joining games.
	 * 
	 * @param int $userID The id of the user to be temp banned.
	 * @param int $days The time of the ban in days.
	 * @param text $reason The reason for the temp ban.
	 * @param boolean $overwrite True, if the temp ban value should be overwritten
	 *		in any case. If false, an existing temp ban might be only extended (for
	 *		automated temp bans).
	 */
	public static function tempBanUser($userID, $days, $reason, $overwrite = true)
	{
		global $DB;
		
		$banUser = new User($userID);
		
		if( $banUser->type['Bot'] )
			return;
		
		/*
		 * If the temp ban value should only be extended (no overwrite), check
		 * if the given time span would extend the ban. If not, do nothing.
		 */
		if(!$overwrite)
		{
			list($tempBan) = $DB->sql_row("SELECT tempBan FROM wD_Users WHERE id = ".$userID);
		
			if( $tempBan > time() + ($days * 86400) ) return;
		}
		
		$DB->sql_put("UPDATE wD_Users SET tempBanReason = '".$reason."', tempBan = ". ( time() + ($days * 86400) )." WHERE id=".$userID);
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
		$rankingDetails['stats']['Civil disorder'] = $this->cdCount;
		$rankingDetails['stats']['Civil disorders taken over'] = $this->cdTakenCount;

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
				$rankingDetails['rank'] = l_t($name);
				break;
			}
		}

		return $rankingDetails;
	}

	/*
	 * A lighter version of rankingDetails with just the game % stats for variant games. 
	 */
	public function rankingDetailsVariants()
	{
		global $DB;

		$rankingDetailsVariants = array();

		$tabl = $DB->sql_tabl(
			"SELECT COUNT(m.id), m.status FROM wD_Members m 
			 inner join wD_Games g on g.id = m.gameID WHERE m.userID = ".$this->id." AND g.variantID <> 1 and g.gameOver <> 'No' and g.playerTypes = 'Members'
			 GROUP BY m.status"
		);

		$rankingDetailsVariants['stats'] = array();
		while ( list($number, $status) = $DB->tabl_row($tabl) )
		{
			if ($status != "Playing") {	$rankingDetailsVariants['stats'][$status] = $number; }
		}

		return $rankingDetailsVariants;
	}

	/*
	 * A lighter version of rankingDetails with just the game % stats for classic games. 
	 */
	public function rankingDetailsClassic()
	{
		global $DB;

		$rankingDetailsClassic = array();

		$tabl = $DB->sql_tabl(
				"SELECT COUNT(m.id), m.status FROM wD_Members m 
				 inner join wD_Games g on g.id = m.gameID WHERE m.userID = ".$this->id." AND g.variantID = 1 and g.gameOver <> 'No' and g.playerTypes = 'Members'
				 GROUP BY m.status"
			);

		$rankingDetailsClassic['stats'] = array();
		while ( list($number, $status) = $DB->tabl_row($tabl) )
		{
			if ($status != "Playing") {	$rankingDetailsClassic['stats'][$status] = $number; }
		}

		return $rankingDetailsClassic;
	}

	/*
	 * A lighter version of rankingDetails with just the game % stats for classic gunboat games. 
	 */
	public function rankingDetailsClassicGunboat()
	{
		global $DB;

		$rankingDetailsClassicGunboat = array();

		$tabl = $DB->sql_tabl(
				"SELECT COUNT(m.id), m.status FROM wD_Members m 
				 inner join wD_Games g on g.id = m.gameID 
				 WHERE m.userID = ".$this->id." AND g.variantID = 1 and g.gameOver <> 'No' and g.pressType = 'NoPress' and g.playerTypes = 'Members'
				 GROUP BY m.status"
			);

		$rankingDetailsClassicGunboat['stats'] = array();
		while ( list($number, $status) = $DB->tabl_row($tabl) )
		{
			if ($status != "Playing") {	$rankingDetailsClassicGunboat['stats'][$status] = $number; }
		}

		return $rankingDetailsClassicGunboat;
	}

	/*
	 * A lighter version of rankingDetails with just the game % stats for classic press games. 
	 */
	public function rankingDetailsClassicPress()
	{
		global $DB;

		$rankingDetailsClassicPress = array();

		$tabl = $DB->sql_tabl(
				"SELECT COUNT(m.id), m.status FROM wD_Members m 
				 inner join wD_Games g on g.id = m.gameID 
				 WHERE m.userID = ".$this->id." AND g.variantID = 1 and g.gameOver <> 'No' and g.pressType in ('Regular', 'RulebookPress') and g.playerTypes = 'Members'
				 GROUP BY m.status"
			);

		$rankingDetailsClassicPress['stats'] = array();
		while ( list($number, $status) = $DB->tabl_row($tabl) )
		{
			if ($status != "Playing") {	$rankingDetailsClassicPress['stats'][$status] = $number; }
		}

		return $rankingDetailsClassicPress;
	}

	/*
	 * A lighter version of rankingDetails with just the game % stats for classic ranked games. 
	 */
	public function rankingDetailsClassicRanked()
	{
		global $DB;

		$rankingDetailsClassicRanked = array();

		$tabl = $DB->sql_tabl(
				"SELECT COUNT(m.id), m.status FROM wD_Members m 
				 inner join wD_Games g on g.id = m.gameID 
				 WHERE m.userID = ".$this->id." AND g.variantID = 1 and g.gameOver <> 'No' and g.potType <> 'Unranked' and g.playerTypes = 'Members'
				 GROUP BY m.status"
			);

		$rankingDetailsClassicRanked['stats'] = array();
		while ( list($number, $status) = $DB->tabl_row($tabl) )
		{
			if ($status != "Playing") {	$rankingDetailsClassicRanked['stats'][$status] = $number; }
		}

		return $rankingDetailsClassicRanked;
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

	public function getMuteUsers() 
	{
		global $DB;

		static $muteUsers;
		if( isset($muteUsers) ) return $muteUsers;
		$muteUsers = array();

		$tabl = $DB->sql_tabl("SELECT muteUserID FROM wD_MuteUser WHERE userID=".$this->id);
		while(list($muteUserID) = $DB->tabl_row($tabl))
			$muteUsers[] = $muteUserID;

		return $muteUsers;
	}

	public function isUserMuted($muteUserID) 
	{
		return in_array($muteUserID,$this->getMuteUsers());
	}

	public function toggleUserMute($muteUserID) 
	{
		global $DB;
		$muteUserID = (int)$muteUserID;
		if( $this->isUserMuted($muteUserID) )
			$DB->sql_put("DELETE FROM wD_MuteUser WHERE userID=".$this->id." AND muteUserID=".$muteUserID);
		else
			$DB->sql_put("INSERT INTO wD_MuteUser (userID, muteUserID) VALUES (".$this->id.",".$muteUserID.")");
	}

	public function getMuteCountries($gameID=-1) 
	{
		global $DB;
		$gameID = (int) $gameID;

		static $muteCountries;
		if( !isset($muteCountries) ) $muteCountries = array();
		if( isset($muteCountries[$gameID]) ) return $muteCountries[$gameID];

		$muteCountries[$gameID] = array();
		$tabl = $DB->sql_tabl("SELECT m.gameID, m.muteCountryID 
			FROM wD_MuteCountry m INNER JOIN wD_Games g ON g.id = m.gameID
			WHERE m.userID=".$this->id.($gameID>0?" AND m.gameID=".$gameID:''));

		while(list($muteGameID,$muteCountryID) = $DB->tabl_row($tabl))
		{
			if( $gameID<0 ) // No game ID given, we are collecting all game IDs
				$muteCountries[$gameID][] = array($muteGameID, $muteCountryID);
			else // Game ID given, this is for just one game ID
				$muteCountries[$gameID][] = $muteCountryID;
		}

		return $muteCountries[$gameID];
	}

	public function getLikeMessages() 
	{
		global $DB;

		static $likeMessages;
		if( !isset($likeMessages) ) $likeMessages = array();
		else return $likeMessages;

		$tabl = $DB->sql_tabl("SELECT likeMessageID FROM wD_LikePost WHERE userID=".$this->id);

		while(list($likeMessageID) = $DB->tabl_row($tabl))
			$likeMessages[] = $likeMessageID;

		return $likeMessages;
	}

	public function likeMessageToggleLink($messageID, $fromUserID=-1) 
	{
		
		if( $this->type['User'] && $this->id != $fromUserID && !in_array($messageID, $this->getLikeMessages()))
			return '<a id="likeMessageToggleLink'.$messageID.'" 
			href="#" title="'.l_t('Give a mark of approval for this post').'" class="light likeMessageToggleLink" '.
			'onclick="likeMessageToggle('.$this->id.','.$messageID.',\''.libAuth::likeToggleToken($this->id, $messageID).'\'); '.
			'return false;">'.
			'+1</a>';
		else return '';
	}

	public function getMuteThreads($refresh=false) 
	{
		global $DB;

		static $muteThreads;
		if( $refresh || !isset($muteThreads) ) $muteThreads = array();
		else return $muteThreads;

		$tabl = $DB->sql_tabl("SELECT muteThreadID FROM wD_MuteThread WHERE userID=".$this->id);

		while(list($muteThreadID) = $DB->tabl_row($tabl))
			$muteThreads[] = $muteThreadID;

		return $muteThreads;
	}
	
	public function isThreadMuted($threadID) 
	{
		return in_array($threadID,$this->getMuteThreads($threadID));
	}

	public function toggleThreadMute($threadID) 
	{
		global $DB;
		
		if( $this->isThreadMuted($threadID)) 
			$DB->sql_put("DELETE FROM wD_MuteThread WHERE userID = ".$this->id." AND muteThreadID=".$threadID);
		else
			$DB->sql_put("INSERT INTO wD_MuteThread (userID, muteThreadID) VALUES (".$this->id.", ".$threadID.")");
	
		$this->getMuteThreads(true);
	}

	public function isCountryMuted($gameID, $muteCountryID) 
	{
		return in_array($muteCountryID,$this->getMuteCountries($gameID));
	}

	public function toggleCountryMute($gameID,$muteCountryID) 
	{
		global $DB;
		$gameID = (int)$gameID;
		$muteCountryID = (int)$muteCountryID;

		if( $this->isCountryMuted($gameID,$muteCountryID) )
			$DB->sql_put("DELETE FROM wD_MuteCountry WHERE userID=".$this->id." AND gameID=".$gameID." AND muteCountryID=".$muteCountryID);
		else
			$DB->sql_put("INSERT INTO wD_MuteCountry (userID, gameID, muteCountryID) VALUES (".$this->id.",".$gameID.",".$muteCountryID.")");
	}

	/*
	 * Check if the user has used an emergency pause on their games in the last 6 months, and if they have completed at least 10 games. 
	 */
	public function qualifiesForEmergency() 
	{
		global $DB;
		// If a mod has set this field to 1 this user is banned from emergency pauses. 
		if ($this->emergencyPauseDate == 1) { return false; }

		// Get count of users finished games that they did not resign or leave. 
		list($finishedGames) = $DB->sql_row("
			SELECT COUNT(1) FROM wD_Games g inner join wD_Members m on g.id = m.gameID 
			WHERE m.userID = ".$this->id." AND g.gameOver in ('Won', 'Drawn') and m.status in ('Won','Drawn','Survived','Defeated')");

		// If the user has not used an emergency pause in 6 months and has finished at least 10 games they qualify for a pause
		if ( ($this->emergencyPauseDate + 86400*30*6 < time()) && $finishedGames > 9) { return true; }
		else { return false; }
	}

	/*
	 * Update the emergencyPauseDate for a user.
	 */
	public function updateEmergencyPauseDate($updateDate) 
	{
		global $DB;
		$DB->sql_put("update wD_Users set emergencyPauseDate = ".$updateDate." where id =".$this->id);
	}

	/*
	 * Get the number of total non excused missed turns this year. 
	 */
	public function getYearlyUnExcusedMissedTurns() 
	{
		global $DB;
		list($totalNonLiveMissedTurns) = $DB->sql_row("SELECT COUNT(1) FROM wD_MissedTurns t  
		WHERE t.userID = ".$this->id." AND t.modExcused = 0 and t.liveGame = 0 and t.samePeriodExcused = 0 and t.systemExcused = 0 and t.turnDateTime > ".(time() - 31536000));
		
		return $totalNonLiveMissedTurns;
	}

	/*
	 * Get the number of total non excused missed turns from non live in the past 4 weeks. 
	 */
	public function getRecentUnExcusedMissedTurns() 
	{
		global $DB;
		list($totalMissedTurns) = $DB->sql_row("SELECT COUNT(1) FROM wD_MissedTurns t  
			WHERE t.userID = ".$this->id." AND t.modExcused = 0 and t.liveGame = 0 and t.samePeriodExcused = 0 and t.systemExcused = 0 and t.turnDateTime > ".(time() - 2419200));
		
		return $totalMissedTurns;
	}

	/*
	 * Get the number of non live missed turns in the past year. 
	 */
	public function getMissedTurns() 
	{
		global $DB;
		list($totalMissedTurns) = $DB->sql_row("SELECT COUNT(1) FROM wD_MissedTurns t  
			WHERE t.userID = ".$this->id." AND t.liveGame = 0 and t.modExcused = 0 and t.turnDateTime > ".(time() - 31536000));
		
		return $totalMissedTurns;
	}

	/*
	 * Get the number of non excused live missed turns in the last month. 
	 */
	public function getLiveUnExcusedMissedTurns() 
	{
		global $DB;

		list($totalLiveMissedTurns) = $DB->sql_row("SELECT COUNT(1) FROM wD_MissedTurns t  
		WHERE t.userID = ".$this->id." AND t.modExcused = 0 and t.liveGame = 1 and t.samePeriodExcused = 0 and t.systemExcused = 0 and t.turnDateTime > ".(time() - 2419200));
		
		return $totalLiveMissedTurns;
	}

	/*
	 * Get the number of total non excused missed turns from live in the past week. 
	 */
	public function getLiveRecentUnExcusedMissedTurns() 
	{
		global $DB;
		list($totalMissedTurns) = $DB->sql_row("SELECT COUNT(1) FROM wD_MissedTurns t  
			WHERE t.userID = ".$this->id." AND t.modExcused = 0 and t.liveGame = 1 and t.samePeriodExcused = 0 and t.systemExcused = 0 and t.turnDateTime > ".(time() - (86400 * 7)));
		
		return $totalMissedTurns;
	}

	/*
	 * Get the number of live missed turns in the past month. For live games missed turns are completely forgiven after 1 month
	 */
	public function getLiveMissedTurns() 
	{
		global $DB;
		list($totalMissedTurns) = $DB->sql_row("SELECT COUNT(1) FROM wD_MissedTurns t  
			WHERE t.userID = ".$this->id." AND t.liveGame = 1 and t.modExcused = 0 and t.turnDateTime > ".(time() - 2419200));
		
		return $totalMissedTurns;
	}

	/*
	 * Return if the user is temp banned or not. 
	 */
	public function userIsTempBanned() 
	{
		global $DB;
		list($tempBan) = $DB->sql_row("SELECT u.tempBan FROM wD_Users u  WHERE u.id = ".$this->id);

		return $tempBan > time();
	}

	/* 
	 * Get style theme user is using, 'No' = light mode; 'Yes' = dark mode. If the user has not accessed their user settings, this will default to light mode.
	 */
	public function getTheme()
	{
		global $DB;

		list($variable) = $DB->sql_row("SELECT darkMode FROM wD_UserOptions WHERE userID=".$this->id);
		if ($variable == null) 
		{
			return 'No';
		}
		else
		{
			return $variable;
		}
	}
}
?>
