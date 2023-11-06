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
require_once(l_r('objects/groupUserToUserLinks.php'));

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
	
	public function getSilences() 
	{
		global $DB;
		
		$tabl = $DB->sql_tabl("SELECT 
			s.id as silenceID,
			s.userID as silenceUserID,
			s.postID as silencePostID,
			s.moderatorUserID as silenceModeratorUserID,
			s.enabled as silenceEnabled,
			s.startTime as silenceStartTime,
			s.length as silenceLength,
			s.reason as silenceReason
		FROM wD_Silences s
		WHERE s.userID = ".$this->id."
		ORDER BY s.startTime DESC");
		
		$silences = array();
		while( $record = $DB->tabl_hash($tabl) )
			$silences[] = new Silence($record);
		
		return $silences;
	}
	
	private $ActiveSilence;
	
	public function isSilenced() 
	{
		if( !$this->silenceID ) 
			return false;
		
		$ActiveSilence = new Silence($this->silenceID);
		
		if( $ActiveSilence->isEnabled() ) 
		{
			$this->ActiveSilence = $ActiveSilence;
			return true;
		}
		else
			return false;
	}

	public function getActiveSilence() 
	{
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
	 * @var setUserNotifications
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
	 * Replaced with getOptions() which includes memcache caching and ensures options are only fetched when needed (e.g. they
	 * are needed for the user viewing the page, but not for user objects loaded for other users)
	 * The user's options
	 * @var UserOptions
	 * public $options;
	 */

	/*
	 * The user is blocked from joining or creating new games till the given time
	 * @var timestamp
	 */
	public $tempBan;

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
	 * @var int|float
	 */
	public $cdCount, $nmrCount, $cdTakenCount, $phaseCount, $gameCount, $reliabilityRating;
	
	/**
	 * The users identity score from 0 to 100
	 * 
	 * @var int
	 */
	public $identityScore;
	/**
	 * darkMode
	 * Choose css style theme
	 * @var 'yes' or 'no'
	 */
	public $darkMode;

	/**
	 * optInFeatures
	 * A integer bitset from that can be used to allow users to opt into various experimental features that are in development.
	 * @var int
	 */
	public $optInFeatures;

	/**
	 * Fetches options from the user options table in a lazy cached way
	 * @var UserOptions
	 */
	public function getOptions()
	{
		if( $this->userOptionsCache == null )
		{
			if( $this->id == 1 )
			{
				$this->userOptionsCache = new UserOptions();
			}

			if( ! ($this->userOptionsCache = UserOptions::fetchFromCache($this->id) ) )
			{
				// No cached data available, load from DB.
				$this->userOptionsCache = new UserOptions($this->id);
				$this->userOptionsCache->saveToCache();
			}
		}
		return $this->userOptionsCache;
	}
	/**
	 * Cache for user options to prevent multiple loads
	 * @var UserOptions
	 */
	private $userOptionsCache = null;
	
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

		if( $points == 0 ) return;

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
			if (($points < 0 ) && ($userPassed->points + $points) < 0 ) { $DB->sql_put("UPDATE wD_Users SET points = 0 WHERE id = ".$userID); }
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
					'homepage'=>'','comment'=>'', 'darkMode'=>'');

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
			if ( isset($userForm['passwordcheck']) and $userForm['password'] == $userForm['passwordcheck'] )
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
			0 as online,
			u.deletedCDs, 
			u.emergencyPauseDate, 
			u.yearlyPhaseCount,
			u.tempBanReason,
			u.optInFeatures,
			u.identityScore
			FROM wD_Users u
			WHERE ".( $username ? "u.username='".$username."'" : "u.id=".$this->id ));

		if ( ! isset($row['id']) or ! $row['id'] )
		{
			throw new Exception(l_t("A user object has been created which doesn't represent a real user."));
		}

		foreach( $row as $name=>$value )
		{
			$this->{$name} = $value;
		}

		// Ensure the only optional opt-in feature flags set are allowed in the config:
		if( !isset(Config::$enabledOptInFeatures) )
			$this->optInFeatures = 0;
		else
			$this->optInFeatures = $this->optInFeatures & Config::$enabledOptInFeatures;
		
		// For display, cdCount should include deletedCDs
		$this->{'cdCount'} = $this->{'cdCount'} + $this->{'deletedCDs'};

		// RR should be rounded
		$this->reliabilityRating = round($this->reliabilityRating);

		// Convert an array of types this user has into an array of true/false indexed by type
		$this->type = explode(',', $this->type);
		$validTypes = array('System','Banned','User','Moderator','Guest','Admin','Donator','DonatorBronze','DonatorSilver','DonatorGold','DonatorPlatinum','ForumModerator','Bot','SeniorMod');
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

	function isMapUIPointAndClick()
	{
		return $this->options->value['mapUI'] == 'Point and click';
	}

	private $watchedGameIDsCache = null;
	function getWatchedGameIDs()
	{
		global $DB;
		if( $this->watchedGameIDsCache == null )
		{
			$this->watchedGameIDsCache = array();
			$tabl = $DB->sql_tabl('SELECT gameID from wD_WatchedGames WHERE userID=' . $this->id);
			while(list($gameID) = $DB->tabl_row($tabl))
				$this->watchedGameIDsCache[] = $gameID;
		}
		return $this->watchedGameIDsCache;
	}

	function isWatchingGame($gameID)
	{
		return in_array($gameID, $this->getWatchedGameIDs());
	}

	/**
	 * Return a profile link for this user
	 * @param bool[optional] $welcome If true this profile link is tweaked to be used as the Welcome link
	 * @return string Profile link HTML
	 */
	function profile_link($welcome = false)
	{
		return self::profile_link_static($this->username, $this->id, $this->type, $this->points, $this->identityScore);
	}

	/**
	 * Generate a profile link using raw database values ($type can be a $User->type array or string ENUM field)
	 */
	static function profile_link_static($username, $id, $type, $points, $identityScore = -1)
	{
		global $User;

		$buffer = '';

		if ( (is_array($type) && $type['User']) || (!is_array($type) && strstr($type, 'User') !== false ) )
		{
			$buffer .= '<a href="./userprofile.php?userID='.$id.'"';

			// Allow javascript to use this ID link:
			$buffer.=' profileLinkUserID="'.$id.'">'.$username;

			$buffer.='</a> ('.trim($points).libHTML::points().self::typeIcon($type).libHTML::identityIcon($identityScore).libHTML::loggedOn($id);
			
			$buffer .= ')<span class="userRelationships" profileLinkUserID="'.$id.'"></span>';

			if( isset($User) && $User->type['Moderator'] )
			{
				$buffer .= ' (<a href="index.php?auid='.$id.'">+</a>)';
			}
		}
		else
		{
			$buffer .= '<em>'.$username.'</em>';
		}

		return $buffer;
	}

	static function typeIcon($type) 
	{
		// This must take either a list as it comes from a SQL query, or a built-in $this->type['Admin'] style array
		if( is_array($type) ) 
		{
			$types=array();

			foreach($type as $n=>$v)
				if($v) $types[]=$n;

			$type = implode(',',$types);
		}
		$buf='';

		global $User;

		if( strstr($type,'Moderator') )
		{
			if (!$User->isDarkMode())
			{
				$buf .= '<img src="'.l_s('images/icons/mod.png').'" alt="'.l_t('Mod').'" title="'.l_t('Moderator/Admin').'" />';
			}
			else
			{
				$buf .= '<img src="'.l_s('images/icons/mod3.png').'" alt="'.l_t('Mod').'" title="'.l_t('Moderator/Admin').'" />';
			}
		}
				
		elseif(strstr($type,'Banned') )
			$buf .= '<img src="'.l_s('images/icons/cross.png').'" alt="X" title="'.l_t('Banned').'" />';

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
	 * This will set a notification value in both the object and wD_Users table if not already set.
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
	 * This will clear a notification value in both the object and the wD_Users table if not already cleared.
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

	/**
	 * Log-on, create/update a session record, and take information for user access logging for meta-gamers
	 */
	function logon()
	{
		global $DB;

		session_name('wD_Sess_User-'.$this->id);

		session_start();

		// Non-users can't get banned
		if( $this->type['Guest'] ) return;

		if ( isset($_SERVER['HTTP_USER_AGENT']) )
			$userAgentHash = substr(md5($_SERVER['HTTP_USER_AGENT']),0,4);
		else
			$userAgentHash = '0000';

		if ( ! isset($_COOKIE['wD_Code']) or !( is_numeric($_COOKIE['wD_Code']) or ctype_xdigit($_COOKIE['wD_Code'])) )
		{
			// Cookie code used to be a 32 bit int, now a 128 bit hex string is generated, but old int based cookie codes
			// should still be collected if given
			$cookieCode = md5("".rand(1,pow(2,32)).rand(1,pow(2,32)).rand(1,pow(2,32)).rand(1,pow(2,32)).rand(1,pow(2,32)));//rand(2, 2000000000);
			setcookie('wD_Code', $cookieCode,['expires'=>time()+365*7*24*60*60,'samesite'=>'Lax']);
		}
		else
		{
			if( is_numeric($_COOKIE['wD_Code']) )
				$cookieCode = '000000000000000000000000'.dechex($_COOKIE['wD_Code']);
			else if( ctype_xdigit($_COOKIE['wD_Code']) )
				$cookieCode = $_COOKIE['wD_Code'];
			else
				$cookieCode = '00000000000000000000000000000000';
		}

        if( isset($_COOKIE['wD_FJT']) && ctype_xdigit($_COOKIE['wD_FJT']) )
        {
            $browserFingerprint = trim($_COOKIE['wD_FJT']); // ctype_xdigit is very strict, even the trim is likely unneeded
        }
        else
        {
            $browserFingerprint = '';
        }

		if($this->type['Banned'])
			libHTML::notice(l_t('Banned'), l_t('You have been banned from this server. If you think there has been a mistake contact the moderator team at %s , and if you still aren\'t satisfied contact the admin at %s (with details of what happened).',Config::$modEMail, Config::$adminEMail));

		$ip = $originalIP = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];

		$ip = (explode(',',$ip))[0]; // Somehow this seems to be of the form 1.2.3.4,1.2.3.5, as in a list of IPS
		// Don't know whyu but need a fix as causing ip is null errors on insert
		if( strstr($ip, ':') !== false )
		{
			// It's an IPv6; just take the last 0xffffffff
			// '2409:8a00:184f:70d0:652f:47b3:7ee1:5f50'
			$ip=str_replace(':','',$ip);
			// '24098a00184f70d0652f47b37ee15f50'
			/*
			Previous truncation of 128 bit address to 32 bit address no longer needed
			if( strlen($ip) >= 6)
			{
				$ip=substr($ip, min(strlen($ip)-6,0), 6);
				// 'e15f50'\
				$h='0x'.$ip;
				$hd=hexdec($h);
				$ip  = long2ip($hd);
				// '0.225.95.80'
				// first number is always 0 to indicate this is an ipv6 snippet; this is only a small part of the whole address so is just an indicator
			}
			else
			{
				$ip='1.1.1.1';
			}*/
		}
		else
		{
			$ip = ip2long($ip);
			if( !$ip ) $ip = 0;
			$ip = dechex($ip);
		}

		if ( isset($_COOKIE['wD_WP']) )
			$webPushrSID = (int)$_COOKIE['wD_WP'];
		else
			$webPushrSID = 0;

		if( !isset($_SESSION['auid']) && !defined('AdminUserSwitch')  && $this->id > 1 && !defined('PLAYNOW') && strstr($this->username,'diplonow_') === false )
		{
			// Only store a session hit if we are not impersonating a user
			$DB->sql_put("INSERT INTO wD_Sessions (userID, lastRequest, hits, ip, userAgent, cookieCode, browserFingerprint, webPushrSID)
			VALUES (".$this->id.",CURRENT_TIMESTAMP,1, UNHEX('".$ip."'),
					UNHEX('".$userAgentHash."'), UNHEX('".$cookieCode."'), UNHEX('".$browserFingerprint."'), ".$webPushrSID.")
			ON DUPLICATE KEY UPDATE hits=hits+1,ip=UNHEX('".$ip."'),userAgent=UNHEX('".$userAgentHash."'), cookieCode=UNHEX('".$cookieCode."'), browserFingerprint=UNHEX('".$browserFingerprint."'), webPushrSID=".$webPushrSID);
			
			$DB->sql_put("INSERT INTO wD_IPLookups (ipCode, ip, timeInserted, timeLastHit)
			VALUES (UNHEX('".$ip."'), '".$originalIP."', UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
			ON DUPLICATE KEY UPDATE hits=hits+1, timeLastHit=UNIX_TIMESTAMP()");
		}
		
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
		
			if( $tempBan > time() + ($days * 5*24*60*60) ) return;
		}
		
		$DB->sql_put("UPDATE wD_Users SET tempBanReason = '".$reason."', tempBan = ". ( time() + ($days * 5*24*60*60) )." WHERE id=".$userID);
	}

	public function rankingDetails()
	{
		global $DB, $Misc;

		$rankingDetails = array();

		list($rankingDetails['position']) = $DB->sql_row("SELECT COUNT(id)+1 FROM wD_Users WHERE points > ".$this->points);

		list($rankingDetails['worth']) = $DB->sql_row( "SELECT SUM(bet) FROM wD_Members WHERE userID = ".$this->id." AND status = 'Playing'");

		$rankingDetails['worth'] += $this->points;

		$tabl = $DB->sql_tabl( "SELECT COUNT(id), status FROM wD_Members WHERE userID = ".$this->id." GROUP BY status"	);

		$rankingDetails['stats'] = array();
		while ( list($number, $status) = $DB->tabl_row($tabl) )
		{
			$rankingDetails['stats'][$status] = $number;
		}

		// $rankingDetails['stats']['Civil disorder'] = $this->cdCount; // These don't get updated anywhere
		// $rankingDetails['stats']['Civil disorders taken over'] = $this->cdTakenCount;
		list($rankingDetails['stats']['Civil disorder']) = $DB->sql_row(
			"SELECT COUNT(c.userID) FROM wD_CivilDisorders c ".
				/*INNER JOIN wD_Games g ON ( g.id = c.gameID )
				LEFT JOIN wD_Members m ON ( c.gameID = m.gameID and c.userID = ".$this->id." )*/
				" WHERE c.userID = ".$this->id //." AND m.userID IS NULL"
			);

		list($rankingDetails['stats']['Civil disorders taken over']) = $DB->sql_row(
			"SELECT COUNT(c.userID) FROM wD_CivilDisorders c 
			INNER JOIN wD_Members m ON ( c.gameID = m.gameID and m.countryID = c.countryID )
			WHERE c.userID <> ".$this->id." AND m.userID = ".$this->id
			);

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
			// No game ID given, we are collecting all game IDs
			if( $gameID < 0 ) 
				$muteCountries[$gameID][] = array($muteGameID, $muteCountryID);

			// Game ID given, this is for just one game ID
			else 
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
		if( is_null($DB) ) return 0;
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
		if( is_null($DB) ) return 0;
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
		if( is_null($DB) ) return 0;
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

		if( is_null($DB) ) return 0;
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
		if( is_null($DB) ) return 0;
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
		if( is_null($DB) ) return 0;
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
	
		if( is_null($DB) ) return false; // Don't use the database if we are in an error page without the database handle

		list($tempBan) = $DB->sql_row("SELECT u.tempBan FROM wD_Users u  WHERE u.id = ".$this->id);

		return $tempBan > time();
	}

	/* 
	 * Returns true if in dark mode, false otherwise
	 */
	public function isDarkMode()
	{
		return $this->getOptions()->value['darkMode'] === 'Yes';
	}

	/*
	 * Get the number of total bot games the member is currently playing in. 
	 */
	public function getBotGameCount() 
	{
		global $DB;
		if( is_null($DB) ) return 0;
		list($totalBotGames) = $DB->sql_row("SELECT COUNT(1) FROM wD_Games g inner join wD_Members m on m.gameID = g.id  
			WHERE m.userID = ".$this->id." AND g.gameOver = 'No' and g.playerTypes = 'MemberVsBots' AND g.sandboxCreatedByUserID IS NULL");
		
		return $totalBotGames;
	}

	/*
	 * Get time the user was last checked by a mod
	 */
	public function modLastCheckedOn() 
	{
		global $DB;
		if( is_null($DB) ) return time();
		list($modLastCheckedOn) = $DB->sql_row("SELECT c.modLastCheckedOn FROM wD_UserConnections c WHERE c.userID = ".$this->id);
		
		return $modLastCheckedOn;
	}

	/*
	 * Get the mod who last checked the user
	 */
	public function modLastCheckedBy() 
	{
		global $DB;
		if( is_null($DB) ) return time();
		list($modLastCheckedBy) = $DB->sql_row("SELECT c.modLastCheckedBy FROM wD_UserConnections c WHERE c.userID = ".$this->id);
		
		return $modLastCheckedBy;
	}

	/*
	 * Get the GR category, rating, peak, and position for a given user for all categories.
	 */
	public function getCurrentGRByCategory() 
	{
		global $DB;		
		$ghostRatingCategories = array();

		$tabl = $DB->sql_tabl(
				"SELECT g.categoryID, g.rating, g.peakRating, 
				(select count(1)+1 from wD_GhostRatings g1 where g1.categoryID = g.categoryID and g1.rating > g.rating) as position 
				FROM wD_GhostRatings g WHERE g.userID = ".$this->id
			);

		while ( list($categoryID, $rating, $peakRating, $position) = $DB->tabl_row($tabl) )
		{
			$categoryName = Config::$grCategories[$categoryID]["name"];

			$ghostRatingCategories[$categoryName]['Rating'] = $rating; 
			$ghostRatingCategories[$categoryName]['Peak'] = $peakRating; 
			$ghostRatingCategories[$categoryName]['Position'] = $position; 
		}

		return $ghostRatingCategories;
	}

	/*
	 * Get the GR category, rating, peak, and position for a given user for all categories.
	 */
	public function getGRTrending($categoryID, $limit) 
	{
		global $DB;		
		$ghostRatingTrends = array();

		$tabl = $DB->sql_tabl(
				"SELECT concat(LEFT(g.yearMonth,4), '-', RIGHT(g.yearMonth,2)) as timePeriod, g.rating 
				FROM wD_GhostRatingsHistory g WHERE g.userID = ".$this->id. " and g.categoryID = ".(int)$categoryID." order by g.yearMonth desc limit ".(int)$limit
			);

		while ( list($timePeriod, $rating) = $DB->tabl_row($tabl) )
		{
			$ghostRatingTrends[$timePeriod] = $rating; 
		}

		$reversed = array_reverse($ghostRatingTrends);
		return $reversed;
	}

	public function summaryPanel()
	{
		/*
		
		*/
	}
}
?>
