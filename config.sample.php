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
	public static $database_socket='webdiplomacy-db';

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
	 * Memcached hostname
	 *
	 * @var string
	 */
	public static $memcachedHost='webdiplomacy-memcached';

	/**
	 * Memcached port number
	 *
	 * @var int
	 */
	public static $memcachedPort=11211;

	/**
	 * An array of variants available on the server (for future releases, not yet enabled)
	 * @var array
	 */
	public static $variants=array(1=>'Classic',2=>'World',9=>'AncMed',15=>'ClassicFvA',17=>'ClassicChaos',19=>'Modern2',20=>'Empire4',23=>'ClassicGvI',91=>'ColdWar');

	/**
	 * A boolean controlling whether automatic gr calculations are enabled. Set to true for auto-GR calculation and false to require manual calculations via the modtool. Note that $grCategories must exist to work.
	 * @var boolean
	 */
	public static $grActive = false;

	/**
	 * An array of variants where concede votes are allowed. If empty then all variants will allow concede voting.
	 * @var array
	 */
	public static $concedeVariants=array(15,23);

	/**
	 * Play now domain; if not null the system will check whether it is being viewed as this
	 * subdomain, e.g. play.webdiplomacy.net , and if it is the system will run in play-now
	 * mode where no user account is needed and player vs bot games will be created and started
	 * from any page.
	 * @var string|null
	 */
	public static $playNowDomain = null;
	
	/**
	 * An array of categories to use when calculating GhostRatings
	 * @var array
	 */
	public static $grCategories=array(
		/* A Category ID maps to an array of settings */
		0 => array(
			/*gives the name of the category*/
			"name" => "Overall",
			/*Different scoring systems are used for 1v1 and non-1v1*/
			"1v1" => "No",
			/*variantMods sets which variants to include*/
			"variants" => array(1,2,9,19,20,17),
			/*pressMods sets which press type to include*/
			"presses" => array('Regular','PublicPressOnly','NoPress','RulebookPress'),
			/*phases sets whether you want to include live games, non-live games, or both. The cutoff is at 1 hour phase lengths. 1 hour phases are considered non-live*/
			"phases" => array('Live', 'Nonlive'),
			/*scoring lets you choose what type of scoring sytems to include - only these three types are supported in the current code*/
			"scoring" => array('Winner-takes-all','Points-per-supply-center','Sum-of-squares')
		),
		1 => array(
			"name" => "Gunboat",
			"1v1" => "No",
			"variants" => array(1,2,9,19,20,17),
			"presses" => array('NoPress'),
			"phases" => array('Live', 'Nonlive'),
			"scoring" => array('Winner-takes-all','Points-per-supply-center','Sum-of-squares')
		),
		2 => array(
			"name" => "Live",
			"1v1" => "No",
			"variants" => array(1,2,9,19,20,17),
			"presses" => array('Regular','PublicPressOnly','NoPress','RulebookPress'),
			"phases" => array('Live'),
			"scoring" => array('Winner-takes-all','Points-per-supply-center','Sum-of-squares')
		),
		3 => array(
			"name" => "Full Press",
			"1v1" => "No",
			"variants" => array(1),
			"presses" => array('Regular'),
			"phases" => array('Nonlive'),
			"scoring" => array('Winner-takes-all','Sum-of-squares')
		),
		4 => array (
			"name" => "1v1 Overall",
			"1v1" => "Yes",
			"variants" => array(15,23)
		),
		5 => array (
			"name" => "FvA",
			"1v1" => "Yes",
			"variants" => array(15)
		),
		6 => array (
			"name" => "GvI",
			"1v1" => "Yes",
			"variants" => array(23)
		)
	);
	
	/**
	 * An array of modvalues to use when calculating GhostRatings. The lower the number the more weight it carries
	 * @var array
	 */
	public static $grVariantMods = array(1=>1, 2=>4, 9=>2, 19=>4, 20=>4, 15=>1, 23=>1, 17=>8);
	
	/**
	 * An array of modvalues to use when calculating GhostRatings. The lower the number the more weight it carries
	 * @var array
	 */
	public static $grPressMods = array('Regular'=>1,'PublicPressOnly'=>2,'NoPress'=>4,'RulebookPress'=>1);

	/**
	 * The API configuration. Whether to enable it or not, and restrict it to some variants or some gameIDs.
	 *
	 * @var array
	 */
	public static $apiConfig = array(
		/* Whether the API is enabled or not */
		"enabled" => true,

		/* Only replace players in CD if they are in a NoPress game */
		"noPressOnly" => true,

		/* If the API should only be enabled for some game ids, set the list of game ids here */
		"restrictToGameIDs" => array(),

		/* List of variant IDs supported */
		/* 1 = Classic, 15 = ClassicFvA, 23 = ClassicGvI */
		"variantIDs" => array(1, 15, 23)
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
	 * A bit-mask that masks an int stored against wD_Users to allow users to opt-in to various experimental features
	 * in a way that doesn't need any database changes to add/remove new features.
	 *
	 * If this is non-zero the user will see a list of options as defined in locales/[locale]/user.php
	 *
	 * @var int
	 */
	public static $enabledOptInFeatures = 0;
	// Enable up to 24 opt-in features:
	//public static $enabledOptInFeatures = 0b111111111111111111111111;

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
	 * This is the folder that game backup JSON files will be written to when gamemaster.php is called with BACKUPGAMES.
	 * Note that this will contain message data so should be somewhere private, and there should be code that will compress
	 * and clean this folder up regularly.
	 * It should be reset every time a full site backup is taken; this dataset is so if something happens to the site the
	 * game data, which is the most important thing, can still be restored (without requiring constant downtime to take backups)
	 */
	public static $gameBackupDirectory = false;

	/**
	 * Where to log points before/after logs to, which log the points before/after games have ended.
	 * If false points are not logged.
	 *
	 * @var string
	 */
	public static $pointsLogFile=false;//'../pointslog.txt';

	/**
	 * Where to log bot requests, for troubleshooting the bot API
	 *
	 * @var string
	 */
	public static $botsLogFile=false;//'botslog.txt';

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
					"Host"=>"mailhog",
					"Port"=>"1025",
					"SMTPAuth"=>false,
					/* If this is FALSE the two variables below are ignored */
					"Username"=>"webmaster",
					"Password"=>"password123"
					/* Uncomment the line below to use SSL to connect (e.g. for gmail) */
					// , 'SMTPSecure'=>'ssl'
				),
			"UseDebug" => false // If this is set to true mail will be output to the browser instead of sent, useful for debugging
		);
	
	/**
	 * The configuration for sending SMS messages, currently only set up to use Twilio
	 */
	public static $smsConfig = array(
		"isEnabled"				=> false,
		"isValidationEnabled" 	=> false,
		"isNotificationEnabled" => false,
        "twilioSID"    			=> "",
        "twilioToken"  			=> "",
        "twilioServiceSID" 		=> ""
	);

	/**
	 * Something to add into the header, within <head></head>, as analytics now needs to be embedded there.
	 */
	public static function customHeader()
	{
		return '';
	}

	/**
	 * Something to add after everything else has been printed off (except '</body></html>'), useful for
	 * things like Google Analytics, or web-rings
	 */
	public static function customFooter()
	{
		return '';
		return 'Default custom server message / google analytics code.';
	}

	/**
	 * The username that the web hook with authenticate against
	 * @var string
	 */
	public static $fingerPrintWebHookUsername = null;
	/**
	 * The password that the web hook will authenticate against
	 * @var string
	 */
	public static $fingerPrintWebHookPassword = null;

	/**
	 * If using reCaptcha v3 instead of the built-in easyCaptcha (which can be unreliable) enter the site key here:
	 * @var string
	 */
	public static $recaptchaSiteKey = null;

	/**
	 * Public Site key for a web pusher account, to allow users to subscribe to notifications
	 * @var string
	 */
	public static $webpushrSiteKey = null;

	/**
	 * Private Auth key for a web pusher account, to allow pushing notifications to users
	 * @var string
	 */
	public static $webpushrAuthKey = null;

	/**
	 * Private The auth token for a web pusher account to allow users to subscribe to notifications
	 * @var string
	 */
	public static $webpushrAuthToken = null;

	/**
	 * Read /contrib/phpBB3-files/README.txt for instructions on enabling the phpBB3 integration support. The final step
	 * is uncommenting the line below (assuming this is where it was installed to.)
	 */
	//public static $customForumURL='/contrib/phpBB3/';

    /**
    * Settings needed for auth0 to function
    public static $auth0conf = array(
		'domain' => '',
		'client_id' => '',
		'client_secret' => '',
		'redirect_url' => '',
	);
    */
	
	// ---
	// --- From here on down the default settings will probably be fine.
	// ---

	/**
	 * Enables full error and profiler output even when not viewing as admin. (This
	 * is set to true if viewing as admin)
	 * @var boolean
	 */
	public static $debug=true;

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

	/**
	 * Returns true if this request is happening on a play-now server.
	 * @return bool
	 */
	public static function isOnPlayNowDomain()
	{
		if( isset(self::$playNowDomain) && self::$playNowDomain != null ) 
		{
			if( isset($_SERVER['HTTP_HOST']) && strstr(strtolower($_SERVER['HTTP_HOST']), strtolower(Config::$playNowDomain)) !== false )
				return true;
		}
		return false;	
	}

	// ---
	// --- The following settings are for WebSockets using Pusher or Soketi
	// --- https://docs.soketi.app/getting-started/backend-configuration/pusher-sdk
	// --- Note that the client-side pusher config needs to be in sync with this
	// --- config; see beta-src/.env.production
	// ---

	/**
	 * The default app id for the pusher/soketi array driver.
	 *
	 * @var string
	 */
	public static $pusherAppKey = 'app-key';

	/**
	 * The default app key for the pusher/soketi array driver.
	 *
	 * @var string
	 */
	public static $pusherAppSecret = 'app-secret';

	/**
	 * The default app secret for the pusher/soketi array driver.
	 *
	 * @var string
	 */
	public static $pusherAppId = 'app-id';

	/**
	 * The default host for the pusher/soketi array driver.
	 * By default it's the defined name of the docker 
	 * container defined in docker-compose.yml
	 *
	 * @var string
	 */
	public static $pusherHost = 'webdiplomacy-websocket';

	/**
	 * The default port for the pusher/soketi array driver.
	 *
	 * @var int
	 */
	public static $pusherPort = 6001;

	/**
	 * The scheme to use for pusher
	 *
	 * @var int
	 */
	public static $pusherScheme = 'http';

	/**
	 * Force pusher to use TLS
	 *
	 * @var int
	 */
	public static $pusherForceTLS = false;

	/**
	 * If set to true bots are allowed to get messages directly from the unredacted messages table, for use
	 * with testing bots in a development environment without needing a separate redaction process running.
	 */
	public static $allowBotsAccessToUnredactedMessages = true;
}
?>