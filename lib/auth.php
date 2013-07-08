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
 * An class which groups authentication functions
 *
 * @package Base
 */
class libAuth
{
	public static function resourceLimiter($name, $seconds)
	{
		global $User;

		if( !$User->type['User'] )
			libHTML::notice(
				l_t('Denied'),
				l_t("Please <a href='register.php' class='light'>register</a> or ".
					"<a href='logon.php' class='light'>log in</a> to %s.",l_t($name))
			);

		if( !isset($_SESSION['resources']) )
			$_SESSION['resources']=array();

		if( isset($_SESSION['resources'][$name]) && (time()-$_SESSION['resources'][$name]) < $seconds )
			libHTML::notice(
				l_t('Denied'),l_t("One %s per %s seconds, please wait and try again.",$name,$seconds)
			);

		$_SESSION['resources'][$name]=time();
	}

	public static function gamemasterToken_Valid($gameMasterToken)
	{
		$token = explode('_',$gameMasterToken);
		if( count($token) != 3 )
			throw new Exception(l_t('Corrupt token %s',$gameMasterToken));

		list($gameID, $time, $hash) = $token;
		if ( self::gamemasterToken_Key($gameID,$time) != $hash )
			throw new Exception(l_t('Invalid token %s',$gameMasterToken));

		if ( (time()-$time)>5*60 )
			throw new Exception(l_t('Token %s expired (%s)',$gameMasterToken,time()));
	}

	private static function gamemasterToken_Key($gameID, $time)
	{
		return md5($gameID.$time.Config::$gameMasterSecret);
	}
	public static function likeToggleToken_Key($userID, $messageID) {
		
		return md5('likeToggle-'.$userID.'-'.$messageID.'-'.Config::$secret);
	}
	public static function likeToggleToken($userID, $messageID) {
		
		return $userID.'_'.$messageID.'_'.self::likeToggleToken_Key($userID, $messageID);
	}
	public static function likeToggleToken_Valid($token) {
		
		$token = explode('_',$token);
		
		if( count($token) != 3 )
			throw new Exception(l_t('Corrupt token %s',$token));
		
		$userID = (int)$token[0];
		$messageID = (int)$token[1];
		$key = $token[2];
		
		if( $key !== self::likeToggleToken_Key($userID, $messageID))
			throw new Exception(l_t('Invalid token %s',$token));
		
		return true;
	}

	public static function gamemasterToken($gameID)
	{
		$time=time();
		return $gameID.'_'.$time.'_'.self::gamemasterToken_Key($gameID,$time);
	}

	/**
	 * Return a URL allowing the user to validate a given e-mail.
	 * emailToken is the name used, and additional GET vars can be added
	 *
	 * @param $email
	 * @return string
	 */
	public static function email_validateURL($email)
	{
		$thisURL = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'];

		// %7C = | , but some webmail clients think that | is the end of the link
		$emailToken = substr(md5(Config::$secret.$email),0,5).'%7C'.urlencode($email);

		return $thisURL.'?emailToken='.$emailToken;
	}

	/**
	 * Either return false or a validated e-mail address
	 * *Does not filter the returned e-mail*
	 *
	 * @param $emailToken
	 * @return string(/false)
	 */
	public static function emailToken_email($emailToken)
	{
		$emailToken = explode('|',$emailToken,2);

		if ( count($emailToken) != 2 )
			return false;

		list($key, $email) = $emailToken;

		if ( $key !== substr(md5(Config::$secret.$email),0,5) )
			return false;
		else
			return $email;
	}

	/**
	 * This function logs a user on, or returns a guest account, and if it's an admin
	 * it'll change the admin's user if required
	 *
	 * @return User An authenticated user account
	 */
	public static function auth()
	{
		if( false )
		{
			if (!strpos($_SERVER['PHP_SELF'], 'register.php')
				and !strpos($_SERVER['PHP_SELF'], 'map.php')
				and !strpos($_SERVER['PHP_SELF'], 'gamemaster.php'))
			{
				$User = new User($facebook->require_login());
				$User->logon(); //key_User does  this if not on facebook
			}
			else
			{
				$User = new User(GUESTID);
			}
		}
		else
		{
			if(isset($_REQUEST['loginuser']) AND isset($_REQUEST['loginpass']))
				$key = self::userPass_Key($_REQUEST['loginuser'], $_REQUEST['loginpass'], isset($_REQUEST['loginsession']));
			elseif(isset($_COOKIE['wD-Key']) and $_COOKIE['wD-Key'])
				$key = $_COOKIE['wD-Key'];
			else
				$key = false;

			if ( $key )
				$User = self::key_User($key);
			else
				$User = new User(GUESTID);

			// Advanced UserLog
			if(isset($_REQUEST['loginuser']) AND isset($_REQUEST['loginpass']))
			{
				global $DB;
				$DB->sql_put("INSERT INTO wD_AccessLogAdvanced SET
								userID   = ".$User->id.",
								request  = CURRENT_TIMESTAMP,
								ip       = INET_ATON('".$_SERVER['REMOTE_ADDR']."'),
								action   = 'LogOn',
								memberID = '0'"
								);
			}	
		}

		return $User;
	}

	/**
	 * Let admin users log on as other users, for debugging
	 * @var User $User The admin user
	 * @return User The user being switched to
	 */
	static public function adminUserSwitch(User $User)
	{
		assert('$User->type["Admin"]');

		if ( isset($_REQUEST['auid']) )
		{
			$auid = intval($_REQUEST['auid']);
		}
		elseif ( isset($_SESSION['auid']) )
		{
			$auid = $_SESSION['auid'];
		}

		if ( isset($auid) )
		{
			if ( $User->id == $auid || $auid <= 0 )
			{
				if ( isset($_SESSION['auid']) )
					unset($_SESSION['auid']);
			}
			else
			{
				try
				{
					define('AdminUserSwitch',$User->id); // Used to display the switch-back button in libHTML::starthtml()
					$User = new User($auid);
				}
				catch( Exception $e )
				{
					libHTML::error("Bad auid given");
				}

				$_SESSION['auid'] = $auid;
			}
		}

		return $User;
	}

	static public function pass_Hash($password)
	{
		return md5(Config::$salt.md5($password));
	}

	/**
	 * Generate a key code from a username and password. If the username
	 * and password do not match a notice will be displayed and the script will
	 * be stopped.
	 *
	 * @param string $username A username
	 * @param string $password The corresponding password
	 *
	 * @return string A key
	 */
	static public function userPass_Key($username, $password)
	{
		global $DB;

		$username = $DB->escape($username);
		$password = $DB->escape($password);

		try
		{
			$TRYUser = new User(0,$username); // The user he's trying to become
		}
		catch(Exception $e)
		{
			libHTML::error(l_t("The username you entered doesn't seem to exist."));
		}

		if( 0==strcasecmp($TRYUser->password, self::pass_Hash($password)) )
		{
			return self::userID_Key($TRYUser->id);
		}
		else
		{
			libHTML::error(l_t('The password you entered is incorrect.'));
		}
	}

	/**
	 * Generate a session key for a given user ID using the config secret
	 * @param int $userID The user ID to generate for
	 * @return string A session key
	 */
	private static function userID_Key( $userID )
	{
		return $userID.'_'.md5(md5(Config::$secret).$userID.sha1(Config::$secret));
	}

	/**
	 * Check a given session key to see if it is valid
	 * @param string $key The key to check
	 * @return int|bool The user ID if valid, false if invalid
	 */
	public static function key_UserID( $key )
	{
		list($userID) = explode('_', $key);

		$correctKey = self::userID_Key($userID);

		if ( $correctKey == $key )
			return $userID;
		else
			return false;
	}

	/**
	 * Wipe the session keys
	 */
	public static function keyWipe()
	{
		// Don't change this line. Don't ask why it needs to be set to expire in a year to expire immidiately
		$success=setcookie('wD-Key', '', (time()-3600));
		libHTML::$footerScript[] = 'eraseCookie("wD-Key");';

		if ( isset($_COOKIE[session_name()]) )
		{
			libHTML::$footerScript[] = 'eraseCookie("'.session_name().'");';
			unset($_COOKIE[session_name()]);
			setcookie(session_name(), '', time()-3600);
			session_destroy();
		}

		return $success;
	}

	/**
	 * Generate and set an authentication cookie
	 * @param int $userID The authenticated user ID to provide a session key for
	 * @param bool $session True if the user should only log on for a session, false if the user should log on permeanently
	 */
	public static function keySet( $userID, $session, $path=false )
	{
		if( isset($_REQUEST['logoff']) )
			return;

		$key = self::userID_Key($userID);

		if ( $session )
			setcookie('wD-Key', $key );
		elseif ( $path )
			setcookie('wD-Key', $key, (time()+365*24*60*60), $path );
		else
			setcookie('wD-Key', $key, (time()+365*24*60*60));
	}

	/**
	 * Logon as a user with a key. Display a notice and terminate if there is
	 * a problem, otherwise return a $User object corresponding to the given
	 * key.
	 * Will also attempt to use legacy keys
	 *
	 * @param string $key The auth key (/legacy cookie)
	 * @param bool[optional] $session Should the user be logged on only for the session true/false
	 *
	 * @return User A user object
	 */
	static public function key_User( $key, $session = false )
	{
		global $DB;

		$userID = self::key_UserID($key);

		if ( ! $userID )
		{
			if( isset($_REQUEST['noRefresh']) )
			{
				// We have been sent back from the logoff script, and clearly not with a wiped key

				// Load some data that will give useful context in the trigger_error errorlog
				// which will occur below.
				if(isset($_COOKIE['wD-Key']) and $_COOKIE['wD-Key'])
					$cookieKey = $_COOKIE['wD-Key'];
				$user_agent = $_SERVER['HTTP_USER_AGENT'];
				$allCookies=print_r($_COOKIE,true);

				$success=self::keyWipe();

				// Make sure there's no refresh loop
				trigger_error(l_t("An invalid log-on cookie was given, but it seems an attempt to remove it has failed.")."<br /><br />".
					l_t("This error has been logged, please e-mail %s if the problem persists, or you can't log on.",Config::$adminEMail));
			}
			else
			{
				self::keyWipe();
				header('refresh: 3; url=logon.php?logoff=on');
				libHTML::error(l_t("An invalid log-on cookie was given, and it has been removed. ".
					"You are being redirected to the log-on page.")."<br /><br />".
					l_t("Inform an admin at %s if the problem persists, or you can't log on.",Config::$adminEMail));
			}

		}

		// This user ID is authenticated
		self::keySet($userID, $session);

		global $User;
		try
		{
			$User = new User($userID);
		}
		catch (Exception $e)
		{
			self::keyWipe();
			header('refresh: 3; url=logon.php?logoff=on');
			libHTML::error(l_t("You are using an invalid log on cookie, which has been wiped. Please try logging on again."));
		}

		$User->logon();

		return $User;
	}

	/**
	 * Validate an e-mail address by checking its MX records and its format.
	 *
	 * @param string $email The e-mail address to check
	 *
	 * @return bool True if valid, false if not valid.
	 */
	static public function validate_email($email) {
		return self::is_valid_email_address($email);
	}

	/**
	 * ##################################################################################
	 * # The following function was originally posted at
	 * # http://www.iamcal.com/publish/articles/php/parsing_email/
	 * #
	 * # RFC(2)822 Email Parser
	 * #
	 * # By Cal Henderson <cal@iamcal.com>
	 * # This code is licensed under a Creative Commons Attribution-ShareAlike 2.5 License
	 * # http://creativecommons.org/licenses/by-sa/2.5/
	 * #
	 * # Revision 4
	 * #
	 * ##################################################################################
	 *
	 * @param string $email The e-mail address to check
	 *
	 * @return bool True if valid, false if invalid
	 */
	static private function is_valid_email_address($email){


		####################################################################################
		#
		# NO-WS-CTL       =       %d1-8 /         ; US-ASCII control characters
		#                         %d11 /          ;  that do not include the
		#                         %d12 /          ;  carriage return, line feed,
		#                         %d14-31 /       ;  and white space characters
		#                         %d127
		# ALPHA          =  %x41-5A / %x61-7A   ; A-Z / a-z
		# DIGIT          =  %x30-39

		$no_ws_ctl    = "[\\x01-\\x08\\x0b\\x0c\\x0e-\\x1f\\x7f]";
		$alpha        = "[\\x41-\\x5a\\x61-\\x7a]";
		$digit        = "[\\x30-\\x39]";
		$cr        = "\\x0d";
		$lf        = "\\x0a";
		$crlf        = "($cr$lf)";


		####################################################################################
		#
		# obs-char        =       %d0-9 / %d11 /          ; %d0-127 except CR and
		#                         %d12 / %d14-127         ;  LF
		# obs-text        =       *LF *CR *(obs-char *LF *CR)
		# text            =       %d1-9 /         ; Characters excluding CR and LF
		#                         %d11 /
		#                         %d12 /
		#                         %d14-127 /
		#                         obs-text
		# obs-qp          =       "\" (%d0-127)
		# quoted-pair     =       ("\" text) / obs-qp

		$obs_char    = "[\\x00-\\x09\\x0b\\x0c\\x0e-\\x7f]";
		$obs_text    = "($lf*$cr*($obs_char$lf*$cr*)*)";
		$text        = "([\\x01-\\x09\\x0b\\x0c\\x0e-\\x7f]|$obs_text)";
		$obs_qp        = "(\\x5c[\\x00-\\x7f])";
		$quoted_pair    = "(\\x5c$text|$obs_qp)";


		####################################################################################
		#
		# obs-FWS         =       1*WSP *(CRLF 1*WSP)
		# FWS             =       ([*WSP CRLF] 1*WSP) /   ; Folding white space
		#                         obs-FWS
		# ctext           =       NO-WS-CTL /     ; Non white space controls
		#                         %d33-39 /       ; The rest of the US-ASCII
		#                         %d42-91 /       ;  characters not including "(",
		#                         %d93-126        ;  ")", or "\"
		# ccontent        =       ctext / quoted-pair / comment
		# comment         =       "(" *([FWS] ccontent) [FWS] ")"
		# CFWS            =       *([FWS] comment) (([FWS] comment) / FWS)

		#
		# note: we translate ccontent only partially to avoid an infinite loop
		# instead, we'll recursively strip comments before processing the input
		#

		$wsp        = "[\\x20\\x09]";
		$obs_fws    = "($wsp+($crlf$wsp+)*)";
		$fws        = "((($wsp*$crlf)?$wsp+)|$obs_fws)";
		$ctext        = "($no_ws_ctl|[\\x21-\\x27\\x2A-\\x5b\\x5d-\\x7e])";
		$ccontent    = "($ctext|$quoted_pair)";
		$comment    = "(\\x28($fws?$ccontent)*$fws?\\x29)";
		$cfws        = "(($fws?$comment)*($fws?$comment|$fws))";
		$cfws        = "$fws*";


		####################################################################################
		#
		# atext           =       ALPHA / DIGIT / ; Any character except controls,
		#                         "!" / "#" /     ;  SP, and specials.
		#                         "$" / "%" /     ;  Used for atoms
		#                         "&" / "'" /
		#                         "*" / "+" /
		#                         "-" / "/" /
		#                         "=" / "?" /
		#                         "^" / "_" /
		#                         "`" / "{" /
		#                         "|" / "}" /
		#                         "~"
		# atom            =       [CFWS] 1*atext [CFWS]

		$atext        = "($alpha|$digit|[\\x21\\x23-\\x27\\x2a\\x2b\\x2d\\x2e\\x3d\\x3f\\x5e\\x5f\\x60\\x7b-\\x7e])";
		$atom        = "($cfws?$atext+$cfws?)";


		####################################################################################
		#
		# qtext           =       NO-WS-CTL /     ; Non white space controls
		#                         %d33 /          ; The rest of the US-ASCII
		#                         %d35-91 /       ;  characters not including "\"
		#                         %d93-126        ;  or the quote character
		# qcontent        =       qtext / quoted-pair
		# quoted-string   =       [CFWS]
		#                         DQUOTE *([FWS] qcontent) [FWS] DQUOTE
		#                         [CFWS]
		# word            =       atom / quoted-string

		$qtext        = "($no_ws_ctl|[\\x21\\x23-\\x5b\\x5d-\\x7e])";
		$qcontent    = "($qtext|$quoted_pair)";
		$quoted_string    = "($cfws?\\x22($fws?$qcontent)*$fws?\\x22$cfws?)";
		$word        = "($atom|$quoted_string)";


		####################################################################################
		#
		# obs-local-part  =       word *("." word)
		# obs-domain      =       atom *("." atom)

		$obs_local_part    = "($word(\\x2e$word)*)";
		$obs_domain    = "($atom(\\x2e$atom)*)";


		####################################################################################
		#
		# dot-atom-text   =       1*atext *("." 1*atext)
		# dot-atom        =       [CFWS] dot-atom-text [CFWS]

		$dot_atom_text    = "($atext+(\\x2e$atext+)*)";
		$dot_atom    = "($cfws?$dot_atom_text$cfws?)";


		####################################################################################
		#
		# domain-literal  =       [CFWS] "[" *([FWS] dcontent) [FWS] "]" [CFWS]
		# dcontent        =       dtext / quoted-pair
		# dtext           =       NO-WS-CTL /     ; Non white space controls
		#
		#                         %d33-90 /       ; The rest of the US-ASCII
		#                         %d94-126        ;  characters not including "[",
		#                                         ;  "]", or "\"

		$dtext        = "($no_ws_ctl|[\\x21-\\x5a\\x5e-\\x7e])";
		$dcontent    = "($dtext|$quoted_pair)";
		$domain_literal    = "($cfws?\\x5b($fws?$dcontent)*$fws?\\x5d$cfws?)";


		####################################################################################
		#
		# local-part      =       dot-atom / quoted-string / obs-local-part
		# domain          =       dot-atom / domain-literal / obs-domain
		# addr-spec       =       local-part "@" domain

		$local_part    = "($dot_atom|$quoted_string|$obs_local_part)";
		$domain        = "($dot_atom|$domain_literal|$obs_domain)";
		$addr_spec    = "($local_part\\x40$domain)";


		#
		# we need to strip comments first (repeat until we can't find any more)
		#

		$done = 0;

		while(!$done){
			$new = preg_replace("!$comment!", '', $email);
			if (strlen($new) == strlen($email)){
				$done = 1;
			}
			$email = $new;
		}


		#
		# now match what's left
		#

		return preg_match("!^$addr_spec$!", $email) ? 1 : 0;
	}

}
?>
