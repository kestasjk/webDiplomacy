<?php
/*
    Copyright (C) 2004-2009 Kestas J. Kuliukas

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
 * The configuration object. This is the only file that will require_once modification by
 * end users.
 *
 * @package Base
 */

class Config
{
	public static $top_menue=array(
		'admin'=> array(
			'help.php'     => array('name'=>'Help',        'inmenu'=>FALSE,'title'=>"Help"),
			'edit.php'     => array('name'=>'Edit',        'inmenu'=>TRUE, 'title'=>"Edit"),
			'startgame.php'=> array('name'=>'Fill',        'inmenu'=>TRUE, 'title'=>"Fill")
		),
		'user' => array(
			'variants.php'   => array('name'=>'Variants',  'inmenu'=>TRUE, 'title'=>"Variants"),
			'mapresize.php'  => array('name'=>'Mapresize', 'inmenu'=>TRUE, 'title'=>"Mapresize")
		),
		'all'  => array(
			'modforum.php'   => array('name'=>'Mods',      'inmenu'=>FALSE, 'title'=>"Mods"),
			'impresum.php'   => array('name'=>'Impresum',  'inmenu'=>FALSE,'title'=>"Impresum"),
			'files.php'      => array('name'=>'Files',     'inmenu'=>FALSE,'title'=>"Files"),
			'stats.php'      => array('name'=>'Stats',     'inmenu'=>FALSE,'title'=>"Statistics"),
			'features.php'   => array('name'=>'Features',  'inmenu'=>FALSE,'title'=>"Features"),
			'reliability.php'=>array('name'=>'Reliability','inmenu'=>FALSE,'title'=>"Reliability"),
		)
	);

	public static $specialCDcountDefault = 0;
	public static $specialCDturnsDefault = 0;

	/**
	 * EasyDevInstall
	 * If set to an install.sql it will create the database and a adminaccount automatically
	 */
	public static $easyDevInstall = 'install_dev.php';

	/**
	 * This is the MySQL socket. It could be a network socket or a UNIX socket.
	 *
	 * eg '127.0.0.1:3306'
	 * or 'localhost'
	 * or 'mysql.myhost.com'
	 * or '/tmp/mysql.sock'
	 *
	 * @var string
	 */
	public static $database_socket='localhost';

	/**
	 * The user who will perform all database actions. You should
	 * tweak the user's permissions so they can only do the bare
	 * minimum they need to be able to do to update the webDiplomacy
	 * tables. Read the administrator documentation for more info.
	 *
	 * @var string
	 */
	public static $database_username='root';

	/**
	 * The password of the above user
	 *
	 * @var string
	 */
	public static $database_password='';

	/**
	 * The database name
	 *
	 * @var string
	 */
	public static $database_name='vDiplomacy';

	/**
	 * This is used to salt hashes for passwords, if it gets out it's not the end of the world.
	 *
	 * *This should be long ( ~30 charecters), random, contain lots of weird charecters, etc*
	 * If this isn't changed or is predictable it is a serious security risk!
	 *
	 * @var string
	 */
	public static $salt='';

	/**
	 * This is used for session keys and the captcha code, and can be changed from time
	 * to time without too much difficulty, but it's even more important that it isn't known!
	 *
	 * @var string
	 */
	public static $secret='';

	/**
	 * This is used to authenticate the cron process which will run the gamemaster script.
	 * If anyone can run the gamemaster script there may be problems (despite the locking),
	 * and it can increase load. Whatever this string is it means gamemaster needs to be run
	 * either by an admin, or by gamemaster.php?secret=[thissecret]
	 *
	 * @var string
	 */
	public static $gameMasterSecret='';

	/**
	 * This is used to authenticate the cron process which will run the gamemaster script.
	 * If anyone can run the gamemaster script there may be problems (despite the locking),
	 * and it can increase load. Whatever this string is it means gamemaster needs to be run
	 * either by an admin, or by gamemaster.php?secret=[thissecret]
	 *
	 * @var string
	 */
	public static $jsonSecret='';

	
	/**
	 * If you use the piwik-webanalyser define his path here. If not comment this out.
	 */		
	// public static $piwik='piwik/';
	// public static $piwik_auth = 'e8f4abf886161725d418df237a82cd8d';

	/**
	 * The administrators e-mail; if a user experiences a problem they will be invited to contact this
	 * e-mail address. It's unlikely bots will experience the sort of problem resulting in your e-mail
	 * being displayed, but if your e-mail provider doesn't filter spam well you may want to be careful.
	 *
	 * @var string
	 */
	public static $adminEMail='admin@localhost';
    
	/**
	 * An array of variants available on the server (for future releases, not yet enabled)
	 * @var array
	 */
	public static $variants=array(
		 1=>'Classic',
//		 2=>'World',
//		 3=>'FleetRome',
//		 4=>'CustomStart',
//		 5=>'BuildAnywhere',
//		 6=>'SouthAmerica5',
//		 7=>'SouthAmerica4',
//		 8=>'Hundred',
//		 9=>'AncMed',
//		10=>'ClassicMilan',
//		11=>'Pure',
//		12=>'Colonial',
//		13=>'Imperium',
//		14=>'ClassicCrowded',
//		15=>'ClassicFvA',
//		16=>'SailHo2',
//		17=>'ClassicChaos',
//		18=>'ClassicSevenIslands',
//		19=>'Modern2',
//		20=>'Empire4',
//		21=>'Migraine',
//		22=>'Duo',
//		23=>'ClassicGvI',
//		24=>'SouthAmerica8',
//		25=>'ClassicGvR',
//		26=>'ClassicFGvsRT',
//		27=>'Sengoku5',
//		28=>'Classic1897',
//		29=>'Rinascimento',
//		30=>'ClassicFog',
//		31=>'Alacavre',
//		32=>'DutchRevolt',
//		33=>'Empire1on1',
//		34=>'Classic1880',
//		35=>'GreekDip',
//		36=>'Germany1648',
//		37=>'MateAgainstMate',
//		38=>'ClassicNoNeutrals',
//		39=>'Fubar',
//		40=>'ClassicOctopus',
//		41=>'Lepanto',
//		42=>'ClassicVS',
//		43=>'WhoControlsAmerica',
//		44=>'FantasyWorld',
//		45=>'Karibik',
//		46=>'BalkanWarsVI',
//		47=>'Hussite',
//		48=>'ClassicFGA',
//		49=>'ClassicIER',
//		50=>'ClassicGreyPress',
//		51=>'Haven',
//		52=>'WWIV',
//		53=>'ClassicEconomic',
//		54=>'ClassicChaoctopi',
//		55=>'TenSixtySix',
//		56=>'USofA',
//		57=>'KnownWorld_901',
//		58=>'TreatyOfVerdun',
//		59=>'YoungstownRedux',
//		60=>'ClassicPilot',
//		61=>'War2020',
//		62=>'ClassicEvT',
//		63=>'Viking',
//		64=>'ClassicTouchy',
//		65=>'RatWars',
//		66=>'Pirates',
//		67=>'Abstraction3',
//		68=>'Habelya',
//		69=>'AmericanConflict',
//		70=>'Zeus5',
//		72=>'Europe1939',
//		73=>'NorthSeaWars',
//		74=>'Maharajah',
//		77=>'GreatLakes',
	);

	/**
	 * Messages to display when different flags are set via the admin control panel.
	 *
	 * If ServerOffline is set it will be displayed and the script will not start.
	 *
	 * @var array
	 */
	public static $serverMessages=array(
//			'Notice'=>'By public request we launch the <a href="variants.php#Chaoctopi">Chaoctopi</a> variant.<br>Combine Chaos with Octopus for pure madness.<br>All work done by <a href="profile.php?userID=114">Tadar Es Darden</a>.',
//			'Notice'=>"Four great clans have arisen, which rat-tribe will rule supreme? Find out @<a href='variants.php?variantID=65'>Rat Wars</a><br>Most work done by <a href='profile.php?userID=32'>Kaner406</a>. Have fun. - Oli",
//			'Notice'=>"Update done. Every game got 12 hours added. Thanks for your patience.",
//			'Notice'=>'Congrats to <a href="profile.php?userID=379">papabearbrodie</a>. The first winner of a <a href="variants.php#WWIV">WWIV-game</a>.<br>Watch his great performance here: <a href="http://www.vdiplomacy.com/board.php?gameID=795">GameID:795</a> ',
//			'Notice'=>"Europe in the Viking age: <a href='variants.php?variantID=63'>Viking IV</a>.<br>A really great variant for 8 players developed (again) by <a href='profile.php?userID=32'>Kaner406</a>.<br>Have fun - Oli",
//			'Notice'=>"WW2 from a really different perspective: <a href='variants.php?variantID=70'>Zeus 5</a>.<br>A new variant for 7 players developed by <a href='profile.php?userID=32'>Kaner406</a>.<br>Have fun - Oli",
//			'Notice'=>"The lands of <a href='variants.php?variantID=68'>Habelya</a> and its mighty empires have been struck by a great earthquake. Rise now in this new world to regain power for your nation. This is the first map from <a href='profile.php?userID=1215'>King Atom</a>.<br> Have fun - Oli",
//			'Notice'=>"A new take on the North-American map: <a href='variants.php?variantID=69'>American Conflict</a>.<br>From our very active variant-developer Gavin Atkinson <a href='profile.php?userID=60'>(The Ambassador)</a>.<br> Have fun - Oli",
			'Notice'=>"Sorry everybody, my computer broke.<br>Will try to recover the next days, but it might take some time till I can take care of the site again....<br>- Oli",
//			'Notice'=>"Whow, Whow Whow... One of the most complex variants arrived... Enter the pirate-age: <a href='variants.php?variantID=66'>Pirates</a>.<br>From our very active variant-developer Gavin Atkinson <a href='profile.php?userID=60'>(The Ambassador)</a>.<br>This variant made my brain hurt.. :-). Have fun - Oli",
//			'Notice'=>"The first variant from <a href='profile.php?userID=705'>Hellenic Riot</a> (aka Mikalis Kamaritis): <a href='variants.php?variantID=72'>Europe 1939</a>.<br>Have fun - Oli",
//			'Notice'=>"A new variant with a really big map arrived today: <a href='variants.php?variantID=57'>Known World 901</a>.<br>The 2nd variant from <a href='profile.php?userID=32'>Kaner406</a>.<br>Have fun. - Oli",
//			'Notice'=>"Finally <a href='variants.php?variantID=59'>Youngstown</a> here on vDip. With some new twists added by <a href='profile.php?userID=69'>airborne</a>.<br>Have fun. - Oli",
//			'Notice'=>"To solve the issues with GMail, Yahoo, Comcast and some web-blacklists we moved to a new IP-adress.<br>I've added 24 hours to every game so the nameservers can fetch the new IP. Make a forum post if you have problems with your games.",
//			'Notice'=>"Small variant for 2 players. <a href='variants.php?variantID=62'>Classic - England vs Turkey</a>.<br>To offset the imbalance of Englands start, his fleets now start in open seas.<br>Developed by <a href='profile.php?userID=14'>orathaic</a>",
//			'Notice'=>"New option for gamecreation: You can define a target SC count to end the game...<br>Minimum is double the initial SCs of the country with the most SCs.",
//			'Notice'=>"New feature. You can vote in a game to extend the current phase (for 4 days).<br>This will delay the processing of a game for this one phase only and might better work than a pause/unpause.<br>You can vote more than one time for a extend and push the time back once more each time.<br>Beware, if all players click on 'ready' the turn will process as usual.",
//			'Panic'=>'Game processing has been paused and user registration has been disabled while a problem is resolved.',
			'Maintenance'=>"30 minutes downtime. Have a large update to do (and my internet connection is really slow)",
			'ServerOffline'=>''
		);

	/**
	 * The directory in which error logs are stored. If this returns false errors will not be logged.
	 * *Must not be accessible to the web server, as sensitive info is stored in this folder.*
	 *
	 * @return string
	 */
	public static function errorlogDirectory()
	{
//		return false;
		return '../webdip_logfiles';
	}

	/**
	 * Should every piece of every order entered be logged as it comes in? This helps solve
	 * problems when people think they submitted correct orders but may not have, but it
	 * can use up lots of disk space and waste resources every time orders are submitted.
	 *
	 * Every complaint about incorrect orders have been found via the order logs to be
	 * correctly received, so it's probably not worth enabling this unless you get many
	 * complaints.
	 *
	 * If this is set to false orders will not be logged, if it returns a writable directory
	 * orders will be logged there.
	 * *Must not be accessible to the web server, as sensitive info is stored in this folder.*
	 *
	 * @return string
	 */
	public static function orderlogDirectory()
	{
		return false;
		return '../orderlogs';
	}

	/**
	 * Where to log points before/after logs to, which log the points before/after games have ended.
	 * If false points are not logged.
	 *
	 * @var string
	 */
	public static $pointsLogFile=false;//'../pointslog.txt';

	/**
	 * An array of e-mail settings, to validate e-mails etc.
	 *
	 * @var array
	 */
	public static $mailerConfig = array(
			"From"=> "admin@vDiplomacy.com",
			/* The e-mail which mail is sent from. This should be a valid e-mail,
			or it may trip spam filters. */
			"FromName"=> "vDiplomacy",
			/* The name being mailed from. */
			"UseMail"=>false,
			/* Use the php mail() function. Either UseMail, UseSendmail or UseSMTP has to be TRUE,
				if you're using e-mail. */
			"UseSendmail"=>true,
			/* Use the sendmail binary, if this is false the variable below is ignored */
			"SendmailSettings"=> array(
					"Location"=>"/usr/sbin/sendmail"
					/* Location of the sendmail binary */
				),
			"UseSMTP"=> false,
			/* Use SMTP, if this is FALSE the variable below is ignored. */
			"SMTPSettings"=> array(
					"Host"=>"yourdiplomacyserver.com",
					"Port"=>"25",
					"SMTPAuth"=>false,
					/* If this is FALSE the two variables below are ignored */
					"Username"=>"webmaster",
					"Password"=>"password123"
				),
			"UseDebug" => true // If this is set to true mail will be output to the browser instead of sent, useful for debugging
		);

	/**
	 * Something to add after everything else has been printed off (except '</body></html>'), useful for
	 * things like Google Analytics, or web-rings
	 */
	public static function customFooter()
	{
		return '<br><a href="http://www.vDiplomacy.com" class="light" target="_self">vDiplomacy.com - Diplomacy variants</a> - by <a href="http://www.vDiplomacy.com/impresum.php" class="light" target="_blank">Oliver Auth</a><br />';
	}

	// ---
	// --- From here on down the default settings will probably be fine.
	// ---

	/**
	 * Enables full error and profiler output even when not viewing as admin. (This
	 * is set to true if viewing as admin)
	 * @var boolean
	 */
	public static $debug=false;

	/**
	 * The default locale for guest users.
	 *
	 * @var string
	 */
	public static $locale = 'English';

	/**
	 * Array of available locales
	 *
	 * @var string[]
	 */
	public static $availablelocales = array(
			'English' => 'English'
			);

	/**
	 * Different names given to the same locales, to allow automatic
	 * recognition of which locale to use.
	 *
	 * @var string[][]
	 */
	public static $localealiases = array(
		'English' => array('eng',
			'en_us',
			'en_US',
			'English',
			'en_US.ISO8859-1',
			'en_US.ISO8859-15',
			'en_US.US-ASCII',
			'en_US.UTF-8')
		);

	/**
	 * The number of minutes that gamemaster.php will detect that it hasn't been run for before it will
	 * mark itself in downtime mode.
	 */
	public static $downtimeTriggerMinutes=99999999;


	// ---
	// --- The following settings are typically for Facebook webmasters only
	// ---

	/**
	 * The URL which static data, such as images, are stored at (usually only for Facebook or advanced users)
	 *
	 * eg http://static.webdiplomacy.net/
	 *
	 * @var string
	 */
	public static $facebookStaticURL='';

	/**
	 * The URL of the front end of the server (usually only for Facebook or advanced users)
	 *
	 * eg http://webdiplomacy.net/
	 *
	 * @var string
	 */
	public static $facebookServerURL='';

	/**
	 * The Facebook API key. If you're not on Facebook this will be ignored
	 *
	 * @var string
	 */
	public static $facebookAPIKey='';

	/**
	 * The Facebook secret. If you're not on Facebook this will be ignored
	 *
	 * @var string
	 */
	public static $facebookSecret='';

	/**
	 * The path to the Facebook API script (facebook.php)
	 *
	 * eg ../../facebook-client/
	 *
	 * @var string
	 */
	public static $facebookAPIPath='';

	/**
	 * The user ID of the Facebook user to send game notification messages from.
	 *
	 * This is provided to $facebook->set_user(user_id,secret)
	 *
	 * @var int
	 */
	public static $facebookNotificationFromUserID='';

	/**
	 * The authentication secret of the above Facebook user
	 *
	 * @var string
	 */
	public static $facebookNotificationFromUserSecret='';

	/**
	 * The Facebook debug value
	 *
	 * @var bool
	 */
	public static $facebookDebug=false;
}

?>