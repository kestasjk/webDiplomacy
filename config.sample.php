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
 * The configuration object. This is the only file that will require modification by
 * end users.
 *
 * @package Base
 */
class Config
{
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
	public static $database_username='webdiplomacy';

	/**
	 * The password of the above user
	 *
	 * @var string
	 */
	public static $database_password='mypassword123';

	/**
	 * The database name
	 *
	 * @var string
	 */
	public static $database_name='webdiplomacy';

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
	 * The administrators e-mail; if a user experiences a problem they will be invited to contact this
	 * e-mail address. It's unlikely bots will experience the sort of problem resulting in your e-mail
	 * being displayed, but if your e-mail provider doesn't filter spam well you may want to be careful.
	 *
	 * @var string
	 */
	public static $adminEMail='webmaster@yourdiplomacyserver.com';

	/**
	 * The moderators e-mail; if users have been banned etc they will be directed to contact this e-mail 
	 * to contest it.
	 * 
	 * @var string
	 */
	public static $modEMail='moderators@yourdiplomacyserver.com';

	/**
	 * The moderator e-mail
	 * This mail-adress will be diplayed in the rules-section of the FAQ
	 *
	 * @var string
	 */
	public static $modEMail='webdipmod@gmail.com';
	
	/**
	 * An array of variants available on the server (for future releases, not yet enabled)
	 * @var array
	 */
	public static $variants=array(1=>'Classic', 2=>'World', 9=>'AncMed',19=>'Modern2',20=>'Empire4');//3=>'FleetRome', 4=>'CustomStart', 5=>'BuildAnywhere');
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
			'Notice'=>'Default server-wide notice message.',
			'Panic'=>'Game processing has been paused and user registration has been disabled while a problem is resolved.',
			'Maintenance'=>"Server is in maintenance mode; only admins can fully interact with the server.",
			'ServerOffline'=>''
		);

	/**
	 * An array of answers, indexed by the question, which are added to the FAQ page on this installation, adding it
	 * to the list of generic webDiplomacy FAQs.
	 *
	 * If false no server-specific FAQ section will be displayed.
	 *
	 * @var array
	 */
	public static $faq=array('Have any extra questions been added?'=>'No, not yet.');

	/**
	 * The directory in which error logs are stored. If this returns false errors will not be logged.
	 * *Must not be accessible to the web server, as sensitive info is stored in this folder.*
	 *
	 * @return string
	 */
	public static function errorlogDirectory()
	{
		return false;
		return '../errorlogs';
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
			"From"=> "webmaster@yourdiplomacyserver.com",
			/* The e-mail which mail is sent from. This should be a valid e-mail,
			or it may trip spam filters. */
			"FromName"=> "webDiplomacy gamemaster",
			/* The name being mailed from. */
			"UseMail"=>false,
			/* Use the php mail() function. Either UseMail, UseSendmail or UseSMTP has to be TRUE,
				if you're using e-mail. */
			"UseSendmail"=>false,
			/* Use the sendmail binary, if this is false the variable below is ignored */
			"SendmailSettings"=> array(
					"Location"=>"/usr/sbin/sendmail"
					/* Location of the sendmail binary */
				),
			"UseSMTP"=> true,
			/* Use SMTP, if this is FALSE the variable below is ignored. */
			"SMTPSettings"=> array(
					"Host"=>"yourdiplomacyserver.com",
					"Port"=>"25",
					"SMTPAuth"=>false,
					/* If this is FALSE the two variables below are ignored */
					"Username"=>"webmaster",
					"Password"=>"password123",
					/* Uncomment the line below to use SSL to connect (e.g. for gmail) */
					// , 'SMTPSecure'=>'ssl'
				),
			"UseDebug" => false // If this is set to true mail will be output to the browser instead of sent, useful for debugging
		);

	/**
	 * Something to add after everything else has been printed off (except '</body></html>'), useful for
	 * things like Google Analytics, or web-rings
	 */
	public static function customFooter()
	{
		return '';
		return 'Default custom server message / google analytics code.';
	}

	// ---
	// --- Now the vdiplomacy additions to the config-file:
	// ---
	
	/**
	 * Alter the top menue and add additional pages to your website.
	 */
	public static $top_menue=array(
		'admin'=> array(
			'edit.php'     => array('name'=>'Edit map', 'inmenu'=>TRUE,  'title'=>"Edit your maps"),
			'help.php'     => array('name'=>'Help',     'inmenu'=>FALSE, 'title'=>"Help")
		),
		'user' => array(
			'variants.php' => array('name'=>'Variants', 'inmenu'=>TRUE,  'title'=>"Variants")
		),
		'all'  => array(
			'impresum.php' => array('name'=>'Impresum', 'inmenu'=>FALSE, 'title'=>"Impresum"),
			'download.php' => array('name'=>'Download', 'inmenu'=>FALSE, 'title'=>"Download"),
			'stats.php'    => array('name'=>'Stats',    'inmenu'=>TRUE,  'title'=>"Statistics")
		)
	);
	
	/**
	 * If you use the piwik-webanalyser define his path here. If not comment this out.
	 */		
	// public static $piwik='piwik/';

	/**
	 * If set to any value > 0 the game will not progress for the given turns if a country misses it's orders.
	 * It will send the country in CD and extend the game for another phase for as many turns as in $specialCDturnsDefault.
	 * (These are the defaults that can be changed during gamecreate
	 * @var int.
	 */
	public static $specialCDcountDefault = 0;
	public static $specialCDturnsDefault = 0;

	/**
	 * If set to any value it wil display this mail adress in the rules section instead of the admin-mail.
	 */
	//public static $modEMail = 'admin@vDiplomacy.com';

	/*
	 * Make CD-takeovers cheaper (1/2 bet for example)
	 */
	// public static function adjustCD($bet)
	// {
	//	return ceil($bet / 2);
	// }
	
	/*
	 * Give edit access to these users for some variants without admin-access needed.
	 */
	// public static $devs=array(
	//		'Firdrak'           => array('Oceamania'),
	//		'Ninjanrd'          => array('FiveArmies'),
	//		'myLAAN'            => array('Hussite','TreatyOfVerdun'),
	// );
	
	/*
	 * Limit the maximum bet-size based on how many players can join
	 */
	// public static $limitBet = array (
	// 	2=>'1', 3=>'5', 4=>'10', 5=>'20', 6=>'30'
	// );
	
	/**
	 * EasyDevInstall
	 * If set to an install.php it will create the database and a adminaccount automatically
	 */
	// public static $easyDevInstall = 'install_dev.php';

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
	 * The locale for this site.
	 *
	 * @var string
	 */
	public static $locale = 'English';

	/**
	 * The number of minutes that gamemaster.php will detect that it hasn't been run for before it will
	 * mark itself in downtime mode.
	 */
	public static $downtimeTriggerMinutes=12;


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
