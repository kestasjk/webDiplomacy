<?php
/*
    Copyright (C) 2004-2022 Kestas J. Kuliukas

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

/**
 * This isn't about rules, accounts, moderation, scoring systems, reliability ratings.
 * 
 * We've got good bots and a good UI, giving a challenging, dynamic, very strategic, 
 * but simple game that you can start the moment you want without entering enything,
 * read nothing but the rules if you choose, and you're playing Class diplomacy as a 
 * random country against 6 tough bots all competing.
 * 
 * - If a game doesn't process for 5 miunutes when unpaused it is deleted.
 * - If a game doesn't process for 30 minutes when paused it is deleted
 * 
 * In the spirit of just getting it going this DiploNow has 2 components:
 * 
 * - 1 Game creation
 * 
 * - 2 Game deletion
 * 
 * 1: Game creation occurs diplonow.php gets a request.
 * - The number of active diplonow games are counted. If over the limit exception thrown
 * - New user created diplonow_XXXXXXX where XXXXXX is unique, with standard user permissions
 * - New PlayerVsBots game created diplonow_XXXXXXX  with typical params.
 * - User added to game. - 6 bots added to game
 * - Browser is redirected to the newly created game
 * 
 * 2: Game deleteion is a process done every 30 seconds.
 * - For each game starting with 'diplonow_'
 * - If it is paused and the last process time is over 30 minutes ago delete the game and user data
 * - If it is unpaused and the last process time is over 5 minutes ago delete the game and user data 
 * 
 * @package Base
 * @subpackage Game
 */

require_once('header.php');

require_once('gamemaster/game.php');
require_once('gamemaster/members.php');
require_once('gamemaster/member.php');


// no account? no problem
//if( !isset($User) || $User->type['Guest'] || !$User->type['User'] )
{
	// Make a User
	// Save their key, if present
	// Until no key
	// Set their new key their key
	
	// Save a cookie for a proper user account so it can be restored
	if( isset($_COOKIE['wD-Key']) )
	{
		setcookie('wD-Key_Orig', $_COOKIE['wD-Key'],time()+24*24*60); 
	}
	
	//libAuth::keyWipe();
	// Generate user key
	$acct = 'diplonow_'.round(rand(0,100000));
	$pass = (string)(rand(0,1000000000)); 
	//$DB->sql_put("INSERT INTO wd_Users (username,type,email,points,comment,homepage,timejoined,timeLastSessionEnded,password) VALUES ('".$acct."', 'User', '".$acct."', 0, '', '', ".time().", ".time().", UNHEX('".$passHash."'));");
	$DB->sql_put("INSERT INTO wd_users(
		`username`,`email`,`points`,`comment`,`homepage`,`hideEmail`,`timeJoined`,`locale`,`timeLastSessionEnded`,`lastMessageIDViewed`,`password`,`type`,`notifications`,`muteReports`,`silenceID`,`cdCount`,`nmrCount`,`cdTakenCount`,`phaseCount`,`gameCount`,`reliabilityRating`,`deletedCDs`,`tempBan`,`emergencyPauseDate`,`yearlyPhaseCount`,`tempBanReason`,`optInFeatures`
		)
		SELECT '".$acct."' `username`,'".$acct."' `email`, 100 `points`,`comment`,`homepage`,`hideEmail`,`timeJoined`,`locale`,".time()." `timeLastSessionEnded`,".time()."`lastMessageIDViewed`,UNHEX('".libAuth::pass_Hash($pass)."'),'User' `type`,`notifications`,`muteReports`,`silenceID`,`cdCount`,`nmrCount`,`cdTakenCount`,`phaseCount`,`gameCount`,`reliabilityRating`,`deletedCDs`,`tempBan`,`emergencyPauseDate`,`yearlyPhaseCount`,`tempBanReason`,1
		FROM wd_users
		WHERE id = 1");
	list($newUserID) = $DB->sql_row("SELECT LAST_INSERT_ID()");
	
	//$NewUser = new User($newUserID);
	$key = libAuth::userPass_Key($acct, $pass); // Password is never uysed
	

	$cookieKey = $key;//libAuth::generateKey($newUserID, $pass);
	setcookie('wD-Key',$cookieKey,time()+24*60*60);
	//die(print_r(array($pass, $key, $cookieKey),true));
	//setcookie('wD-Key_Orig', $_COOKIE['wD-Key'],time()+24*24*60); 
	// Create new user
	//$NewUser = libAuth::key_User($key);
	// Keep user logged on by setting 

	// Now the user can go to the bot create page and start a game:
	header('refresh: 1; url=botgamecreate.php?diplonow=on&acct='.$acct);

	libHTML::notice("Creating game", "Going to game creation options in a moment.");
	libHTML::footer();
	die();
}
die('asdf');

libHTML::starthtml();

print libHTML::pageTitle(l_t('Start a bot game'),l_t('"Play Diplomacy right now, stop whien you like".'));

print '<p class="notice">
<img src="images/logo.,oh
Step 1: Diplomacy Now. Step 2: Make it look nice</p>';
print 'Diplomacy Now:';
print '<form action="botgameceate.php" method="post">
<input type="hidden" name="newGame" value="newGame"><input type="Submit" name="Submit" /></form>';

print '<div class="hr"></div>';

print 'Diplomacy Cheat scheet:';
print '<div><div>';

libHTML::footer();
?>
