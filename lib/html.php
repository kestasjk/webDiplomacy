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

// No defined() check here; this may be called before header.php

/**
 * A collection of functions which output HTML and manage the main body and layout of the menu,
 * notification bar, and content.
 *
 * @package Base
 */
class libHTML
{
	public static function pageTitle($title, $description=false) {
		return '<div class="content-bare content-board-header content-title-header">
<div class="pageTitle barAlt1">
	'.$title.'
</div>
<div class="pageDescription barAlt2">
	'.$description.'
</div>
</div>
<div class="content content-follow-on">';
	}

	/**
	 * The style which prevents an element from displaying (usually cached HTML to be displayed via JS)
	 * @var string
	 */
	public static $hideStyle='display:none;';

	/**
	 * Print a webDiplomacy page break, where the content block ends and
	 * starts again leaving a gap.
	 */
	static public function pagebreak()
	{
		print '</div><div class="content">';
	}

	/**
	 * The logged-on icon
	 * @return string
	 */
	static function loggedOn($userID)
	{
		return '<img style="'.self::$hideStyle.'" class="userOnlineImg" userID="'.$userID.'" src="images/icons/online.png" alt="Online" title="User currently logged on" />';
	}

	static function platinum()
	{
		return ' <img src="images/icons/platinum.png" alt="(S)" title="Donator - platinum" />';
	}

	static function gold()
	{
		return ' <img src="images/icons/gold.png" alt="(S)" title="Donator - gold" />';
	}

	static function silver()
	{
		return ' <img src="images/icons/silver.png" alt="(S)" title="Donator - silver" />';
	}

	static function bronze()
	{
		return ' <img src="images/icons/bronze.png" alt="(B)" title="Donator - bronze" />';
	}

	/**
	 * The points icon
	 * @return string
	 */
	static function points()
	{
		return ' <img src="images/icons/points.png" alt="D" title="webDiplomacy points" />';
	}

	static function forumMessage($threadID, $messageID)
	{
		return '<a style="'.self::$hideStyle.'" class="messageIconForum" threadID="'.$threadID.'" messageID="'.$messageID.'" href="forum.php?threadID='.$threadID.'#'.$messageID.'">'.
		'<img src="images/icons/mail.png" alt="New" title="Unread messages!" />'.
		'</a> ';

	}

	static function forumParticipated($threadID)
	{
		return '<a style="'.self::$hideStyle.'" class="participatedIconForum" threadID="'.$threadID.'" href="forum.php?threadID='.$threadID.'#'.$threadID.'">'.
			'<img src="images/icons/star.png" alt="Participated" title="You have participated in this thread." />'.
			'</a> ';
	}

	/**
	 * The icon to mute an unmuted player, optionally with link
	 * @param $url URL to link to
	 * @return string
	 */
	static function unmuted($url=false)
	{
		$buf = '';
		if($url) $buf .= '<a href="'.$url.'">';
		$buf .= '<img src="images/icons/unmute.png" alt="Mute player" title="Mute player" />';
		if($url) $buf .= '</a>';
		return $buf;
	}

	/**
	 * The icon to unmute an muted player, optionally with link
	 * @param $url URL to link to
	 * @return string
	 */
	static function muted($url=false)
	{
		$buf = '';
		if($url) $buf .= '<a href="'.$url.'">';
		$buf .= '<img src="images/icons/mute.png" alt="Muted. Click to un-mute." title="Muted. Click to un-mute." />';
		if($url) $buf .= '</a>';
		return $buf;
	}

	/**
	 * The unread messages icon, optionally with link
	 * @param $url URL to link to
	 * @return string
	 */
	static function unreadMessages($url=false)
	{
		$buf = '';
		if($url) $buf .= '<a href="'.$url.'">';
		$buf .= '<img src="images/icons/mail.png" alt="Unread message" title="Unread message" />';
		if($url) $buf .= '</a>';
		return $buf;
	}

	/**
	 * The maybe read messages icon, optionally with link
	 * @param $url URL to link to
	 * @return string
	 */
	static function maybeReadMessages($url=false)
	{
		$buf = '';
		if($url) $buf .= '<a href="'.$url.'">';
		$buf .= '<img src="images/icons/mail_faded.png" alt="Recent message" title="Recent message" />';
		if($url) $buf .= '</a>';
		return $buf;
	}

	public static function serveImage($filename, $contentType='image/png')
	{
		if ( ob_get_contents() != "" )
			die();

		header('Content-Length: '.filesize($filename));
		header('Content-Type: '.$contentType);

		print file_get_contents($filename);

		if( DELETECACHE )
			unlink($filename);

		die();
	}

	/**
	 * An external link icon
	 * @return string
	 */
	static function link()
	{
		return '<img src="images/historyicons/external.png" alt="Link" title="Click this to follow the link" />';
	}

	/**
	 * Var to alternate back and forth 1,2,1,2 to make things clearer
	 * @var int
	 */
	static $alternate=1;

	/**
	 * Alternates $alternate, and returns it
	 * @return int
	 */
	static function alternate()
	{
		self::$alternate = 3-self::$alternate;
		return self::$alternate;
	}

	/**
	 * Keeps track of whether first() has been called
	 * @var boolean
	 */
	static $first=true;

	/**
	 * Returns 'first' the first time it's called, and nothing from then on until $first is set to true.
	 * @return string
	 */
	static public function first()
	{
		if ( self::$first )
		{
			self::$first = false;
			return 'first';
		}
	}

	/**
	 * Creates a form ticket in the user's session, to make sure that a form is submitted only once.
	 * Embed the returned ticket into an <input type="hidden" name="formTicket" />.
	 *
	 * @return int
	 */
	static public function formTicket()
	{
		if ( !isset($_SESSION['formTickets']) )
			$_SESSION['formTickets'] = array();

		do {
			$ticket = rand(1,999999);
		} while ( isset($_SESSION['formTickets'][$ticket]) );

		$_SESSION['formTickets'][$ticket] = true;

		return $ticket;
	}

	/**
	 * Checks the submitted formTicket, that it exists, and that it is valid.
	 *
	 * @return boolean True if valid, false otherwise
	 */
	static public function checkTicket()
	{
		if( isset($_SESSION['formTickets']) && isset($_REQUEST['formTicket'])
			&& isset($_SESSION['formTickets'][$_REQUEST['formTicket']]) )
		{
			unset($_SESSION['formTickets'][$_REQUEST['formTicket']]);
			return true;
		}
		else
			return false;
	}

	/**
	 * A link to an admin control panel action
	 *
	 * @param $actionName The name of the action
	 * @param array $args The args in a $name=>$value array
	 * @param $linkName The name to give the link, the URL is returned if no linkName is given
	 * @return string A link URL or an <a href>
	 */
	static function admincp($actionName, $args=null, $linkName=null)
	{
		$output = 'admincp.php?tab=Control%20Panel&amp;actionName='.$actionName;

		if( is_array($args) )
			foreach($args as $name=>$val)
			{
				if ( $name == 'gameID' )
					$output .= '&amp;globalGameID='.$val;
				elseif ( $name == 'userID' )
					$output .= '&amp;globalUserID='.$val;

				$output .= '&amp;'.$name.'='.$val;
			}

		$output .= '#'.$actionName;

		if($linkName)
			return '<a href="'.$output.'" class="light">'.$linkName.'</a>';
		else
			return $output;
	}

	static function admincpType($actionType, $id)
	{
		return '<a href="admincp.php?tab=Control%20Panel&amp;global'.$actionType.'ID='.$id.'#'.strtolower($actionType).'Actions">
			View '.strtolower($actionType).' admin-actions</a>';
	}

	/**
	 * Wipe everything done so far, output a notice and end the script. Can be run in
	 * the event of errors.
	 *
	 * @param string $title The title to display
	 * @param string $message The message/notice to show
	 */
	static public function notice($title, $message)
	{
		ob_clean();

		libHTML::starthtml($title);

		print '<div class="content-notice"><p>'.$message.'</p></div>';

		print '</div>';
		libHTML::footer();
	}

	/**
	 * Print an error message and end the script
	 *
	 * @param string $message
	 */
	static public function error($message)
	{
		global $Misc;

		if ( !isset($Misc) )
		{
			die('<html><head><title>webDiplomacy fatal error</title></head>
				<body><p>Error occurred during script startup, usually a result of inability to connect to the database:</p>
				<p>'.$message.'</p></body></html>');
		}

		if(!defined('ERROR'))
			define('ERROR',true);

		self::notice('Error', $message);
	}

	/**
	 * Name of the script filename which was requested e.g. ajax.php, set in starthtml()
	 * @var unknown_type
	 */
	private static $scriptname;

	/**
	 * The first HTML to be output; the various header tags, before anything is visible
	 *
	 * @param string $title The title of the page
	 * @return string The pre-body HTML
	 */
	static public function prebody ( $title )
	{
		$variantCSS=array();
		foreach(Config::$variants as $variantName)
			$variantCSS[] = '<link rel="stylesheet" href="'.STATICSRV.'variants/'.$variantName.'/resources/style.css" type="text/css" />';
		$variantCSS=implode("\n",$variantCSS);

		return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml">
	<head>
		<meta http-equiv="content-type" content="text/html;charset=utf-8" />
		<meta http-equiv="Content-Style-Type" content="text/css" />
		<meta name="robots" content="index,follow" />
		<meta name="description" content="webDiplomacy is an online, multiplayer, turn-based strategy game that lets you play Diplomacy online." />
		<meta name="keywords" content="diplomacy,diplomacy game,online diplomacy,classic diplomacy,web diplomacy,diplomacy board game,play diplomacy,php diplomacy" />
		<link rel="shortcut icon" href="'.STATICSRV.'favicon.ico" />
		<link rel="icon" href="'.STATICSRV.'favicon.ico" />
		<link rel="stylesheet" href="'.CSSDIR.'/global.css" type="text/css" />
		<link rel="stylesheet" href="'.CSSDIR.'/gamepanel.css" type="text/css" />
		<link rel="stylesheet" href="'.CSSDIR.'/home.css" type="text/css" />
		'.$variantCSS.'
		<script type="text/javascript" src="'.STATICSRV.'contrib/js/prototype.js"></script>
		<script type="text/javascript" src="'.STATICSRV.'contrib/js/scriptaculous.js"></script>
		<link rel="stylesheet" type="text/css" href="'.STATICSRV.'contrib/js/pushup/src/css/pushup.css" />
		<script type="text/javascript" src="'.STATICSRV.'contrib/js/pushup/src/js/pushup.js"></script>
		<script type="text/javascript">
		STATICSRV="'.STATICSRV.'";
		</script>
		<title>'.$title.' - webDiplomacy</title>
	</head>';
	}

	/**
	 * Print the HTML which comes before the main content; title, menu, notification bar.
	 *
	 * @param string|bool[optional] $title If a string is given it will be used as the page title
	 */
	static public function starthtml($title=false)
	{
		global $User;

		self::$scriptname = $scriptname = basename($_SERVER['PHP_SELF']);

		$pages = libHTML::pages();

		if ( isset($User) and ! isset($pages[$scriptname]) )
		{
			die('Access to this page denied for your account type.');
		}

		print libHTML::prebody($title===FALSE ? $pages[$scriptname]['name'] : $title).
			'<body>'.libHTML::menu($pages, $scriptname);

		if( defined('FACEBOOKSCRIPT') ) {
			?>
			<script src="http://static.ak.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php/en_US" type="text/javascript"></script>
			<script type="text/javascript">
			FB.init("b24f8dc93cdbf2ff1ee7db508ae14c6d");
			FB_RequireFeatures(["CanvasUtil"], function(){
				FB.XdComm.Server.init("xd_receiver.htm");
				FB.CanvasClient.startTimerToSizeToContent();
			});
			</script>
			<div id="FB_HiddenIFrameContainer" style="display:none; position:absolute; left:-100px; top:-100px; width:0px; height: 0px;"></div>
			<?php
		}

		print '<noscript><div class="content-notice">
					<p class="notice">You do not have JavaScript enabled. It is required to use webDiplomacy fully.</p>
				</div></noscript>';

		print self::globalNotices();

		if ( is_object($User) && $User->type['User'] )
		{
			$gameNotifyBlock = libHTML::gameNotifyBlock();
			if ( $gameNotifyBlock )
				print '<div class="content-notice"><div class="gamelistings-tabs">'.
					$gameNotifyBlock.
					'</div></div>';
		}
	}

	/**
	 * The server-wide notices, displayed at the top of the page if enabled, and defined in config.php
	 *
	 * @return string
	 */
	static private function globalNotices()
	{
		global $Misc, $User;
		$notice=array();
		if ( $Misc->Maintenance and isset($User) and $User->type['Admin'])
		{
			/*
			 * If the user is regular they are being shown the message as part of the main page,
			 * no need to add it as a notice except for admins (who might be wondering why noone
			 * else is online)
			 */
			$notice[]='Server in maintenance/development mode; only admins can interact with
				it until it is <a href="admincp.php?tab=Control Panel#maintenance">turned off</a>.';
		}

		if ( $Misc->Panic )
		{
			$notice[]=Config::$serverMessages['Panic'];
		}

		if ( $Misc->Notice )
			$notice[] = Config::$serverMessages['Notice'];

		if ( ( time() - $Misc->LastProcessTime ) > Config::$downtimeTriggerMinutes*60 )
			$notice[] = "The last process time was over ".Config::$downtimeTriggerMinutes." minutes
				ago (at ".libTime::text($Misc->LastProcessTime)."); the server
				is not processing games until the cause is found and games are given extra time.";

		if ( $notice )
			return '<div class="content-notice"><p class="notice">'.
				implode('</p><div class="hr"></div><p class="notice">',$notice).
				'</p></div>';
		else
			return '';
	}

	/**
	 * The notification block HTML, containing links to games which need
	 * the user's attention.
	 *
	 * @return string The notification block HTML
	 */
	static public function gameNotifyBlock ()
	{
		global $User, $DB;

		$tabl = $DB->sql_tabl(
			"SELECT g.id, g.variantID, g.name, m.orderStatus, m.countryID, (m.newMessagesFrom+0) as newMessagesFrom, g.processStatus
			FROM wD_Members m
			INNER JOIN wD_Games g ON ( m.gameID = g.id )
			WHERE m.userID = ".$User->id." AND ( m.status='Playing' OR m.status='Left' )
				AND ( ( NOT m.orderStatus LIKE '%Ready%' AND NOT m.orderStatus LIKE '%None%' ) OR NOT ( (m.newMessagesFrom+0) = 0 ) )");

		$gameIDs = array();
		$notifyGames = array();
		while ( $game = $DB->tabl_hash($tabl) )
		{
			$id = (int)$game['id'];
			$gameIDs[] = $id;
			$notifyGames[$id] = $game;
		}

		sort($gameIDs);

		$gameNotifyBlock = '';

		foreach ( $gameIDs as $gameID )
		{
			$notifyGame = $notifyGames[$gameID];
			require_once('objects/basic/set.php');
			$notifyGame['orderStatus'] = new setMemberOrderStatus($notifyGame['orderStatus']);

			// Don't print the game if we're looking at it.
			if ( isset($_REQUEST['gameID']) and $_REQUEST['gameID'] == $gameID )
				continue;

			$gameNotifyBlock .= '<span class="variant'.Config::$variants[$notifyGame['variantID']].'">'.
				'<a gameID="'.$gameID.'" class="country'.$notifyGame['countryID'].'" href="board.php?gameID='.$gameID.'">'.
				$notifyGame['name'];

			if ( $notifyGame['processStatus'] == 'Paused' )
				$gameNotifyBlock .= '-<img src="images/icons/pause.png" alt="Paused" title="Game paused" />';

			$gameNotifyBlock .= ' ';

			$gameNotifyBlock .= $notifyGame['orderStatus']->icon();

			if ( $notifyGame['newMessagesFrom'] )
				$gameNotifyBlock .= '<img src="images/icons/mail.png" alt="New messages" title="New messages!" />';

			$gameNotifyBlock .= '</a></span> ';
		}
		return $gameNotifyBlock;
	}

	/**
	 * Return an array of links, along with their names, who can view them, and
	 * whether they appear in the menu.
	 *
	 * @return array
	 */
	static public function pages ()
	{
		global $User;

		$allUsers = array('Guest','User','Moderator','Admin');
		$loggedOnUsers = array('User','Moderator','Admin');

		$links=array();

		// Items displayed in the menu
		$links['index.php']=array('name'=>'Home', 'inmenu'=>TRUE, 'title'=>"See what's happening");
		$links['forum.php']=array('name'=>'Forum', 'inmenu'=>TRUE, 'title'=>"The forum; chat, get help, help others, arrange games, discuss strategies");
		$links['gamelistings.php']=array('name'=>'Games', 'inmenu'=>TRUE, 'title'=>"Game listings; a searchable list of the games on this server");

		if (is_object($User))
		{
			if( !$User->type['User'] )
			{
				$links['logon.php']=array('name'=>'Log on', 'inmenu'=>false, 'title'=>"Log onto webDiplomacy using an existing user account");
				$links['register.php']=array('name'=>'Register', 'inmenu'=>TRUE, 'title'=>"Make a new user account");
			}
			else
			{
				$links['logon.php']=array('name'=>'Log off', 'inmenu'=>false, 'title'=>"Log onto webDiplomacy using an existing user account");
				$links['gamecreate.php']=array('name'=>'New game', 'inmenu'=>TRUE, 'title'=>"Start up a new game");
				$links['usercp.php']=array('name'=>'Settings', 'inmenu'=>TRUE, 'title'=>"Change your user specific settings");
			}
		}
		$links['help.php']=array('name'=>'Help', 'inmenu'=>TRUE, 'title'=>'Get help and information; guides, intros, FAQs, stats, links');

		// Items not displayed on the menu
		$links['map.php']=array('name'=>'Map', 'inmenu'=>FALSE);
		$links['faq.php']=array('name'=>'FAQ', 'inmenu'=>FALSE);
		$links['rules.php']=array('name'=>'Rules', 'inmenu'=>FALSE);
		$links['intro.php']=array('name'=>'Intro', 'inmenu'=>FALSE);
		$links['credits.php']=array('name'=>'Credits', 'inmenu'=>FALSE);
		$links['board.php']=array('name'=>'Board', 'inmenu'=>FALSE);
		$links['profile.php']=array('name'=>'Profile', 'inmenu'=>FALSE);
		$links['translating.php']=array('name'=>'Translating', 'inmenu'=>FALSE);
		$links['points.php']=array('name'=>'Points', 'inmenu'=>FALSE);
		$links['halloffame.php']=array('name'=>'Hall of fame', 'inmenu'=>FALSE);
		$links['developers.php']=array('name'=>'Developer info', 'inmenu'=>FALSE);
		$links['datc.php']=array('name'=>'DATC', 'inmenu'=>FALSE);
		$links['variants.php']=array('name'=>'Variants', 'inmenu'=>FALSE);

		if ( is_object($User) )
		{
			if ( $User->type['Admin'] or $User->type['Moderator'] )
				$links['admincp.php']=array('name'=>'Admin CP', 'inmenu'=>true);

			$links['gamemaster.php']=array('name'=>'GameMaster', 'inmenu'=>FALSE);
		}

		if ( defined('FACEBOOKSCRIPT') )
		{
			$links['invite.php']=array('name'=>'Invite', 'inmenu'=>TRUE);
			$links['logon.php']['inmenu']=false;
			$links['register.php']['inmenu']=false;
		}

		return $links;
	}

	/**
	 * Prints the logo, welcome text and menu.
	 *
	 * @param array $pages The array of pages, with parameters, to be parsed
	 * @param string $scriptname The name of the script currently running
	 *
	 * @return string The logo, welcome text and menu HTML
	 */
	static public function menu ($pages, $scriptname)
	{
		global $User;

	 	$menu = '<!-- Menu begin. -->
				<div id="header">
					<div id="header-container">
						<a href="./">
							<img id="logo" src="images/logo.png" alt="webDiplomacy" />
						</a>';

		if ( is_object( $User ) )
		{
			if ( ! $pages[$scriptname]['inmenu'] )
				$arguments = str_replace('&', '&amp;', $_SERVER['QUERY_STRING']);
			else
				$arguments = '';

			$menu .= '
				<div style="float:right; text-align:right; width:100%">
					<div id="header-welcome">
						'.(is_object($User)?'Welcome, '.$User->profile_link(TRUE).' -
						<span class="logon">('.
							($User->type['User'] ?
							'<a href="logon.php?logoff=on" class="light">Log off</a>)'.
								( defined('AdminUserSwitch') ? ' (<a href="index.php?auid=0" class="light">Switch back</a>)' : '' )
							:'<a href="logon.php" class="light">Log on</a>)').
						'</span>'
						:'Welcome, Guest').'
					</div>';

			$menu .= '<div id="header-goto">';

			if( isset($pages[$scriptname]) and ! $pages[$scriptname]['inmenu'] )
			{
				$menu .= '<a href="'.$scriptname.'?'.$arguments.'" title="The current page; click to refresh" class="current">'
					.$pages[$scriptname]['name'].'</a>';
			}

			foreach($pages as $page=>$script)
			{
				if($script['inmenu'])
				{
					$menu .= '<a href="'.$page.
						( $page==$scriptname ? '?'.$arguments.'" class="current"' : '"').' '.
						( isset($script['title']) ? 'title="'.$script['title'].'"' :'').' '.
						'>'.
						$script['name'].'</a>';
				}
			}

			$menu .= '</div></div>';
		}
		else
		{
			$menu .= '<div id="header-welcome">&nbsp;</div>
				<div id="header-goto">
					<a href="index.php">Home</a>
					<a href="'.$scriptname.'">Reload current page</a>
				</div>';
		}
		$menu .= '</div>
		</div>
		<div id="seperator"></div>
		<div id="seperator-fixed"></div>
		<!-- Menu end. -->';

		return $menu;
	}

	/**
	 * Output the footer HTML and call the close() function to perform final clean-ups. If $DB and $User
	 * are available then the script has ended successfully, and some statistics around outputted.
	 */
	static public function footer()
	{
		global $DB;

		print '<div id="footer">';

		if( is_object($DB) )
		{
			print self::footerStats();

			print '<br /><br />';

			print self::footerCopyright();

			print '<br />Times are <strong id="UTCOffset">UTC+0:00</strong>';

			print Config::customFooter();

			if( Config::$debug )
				print $DB->profilerPrint();

			print self::footerScripts();
		}
		else
		{
			// $DB isn't available, something went wrong
			print self::footerCopyright();
		}

		print '</div></html>';

		close();
	}

	private static function footerStats() {
		global $DB, $Misc, $User;

		$buf = '';

		// Run time, select queries, insert queries
		$buf .= 'Rendered in: <strong>'.round((microtime(true)-$GLOBALS['scriptStartTime']),2).'sec</strong> - '.
			'Data retrievals: <strong>'.$DB->getqueries.'</strong> - '.
			'Data insertions: <strong>'.$DB->putqueries.'</strong> ';

		if( function_exists('memory_get_usage') )
			$buf .= ' - Memory used: <strong>'.round((memory_get_usage()/1024)/1024, 3).'MB</strong> ';

		$buf .= '<br /><br />';

		$stats=array(
			'Logged on'=>$Misc->OnlinePlayers,
			'Playing'=>$Misc->ActivePlayers,
			'Registered'=>$Misc->TotalPlayers
		);

		$first=true;
		foreach($stats as $name=>$stat)
		{
			if ( $first ) $first=false;
			else $buf .= ' - ';

			$buf .= $name.': <strong>'.$stat.'</strong> ';
		}
		$buf .= ' - Pages served: <strong>'.$Misc->Hits.'</strong>';
		$buf .= '<br />';

		$stats=array('Starting games'=>$Misc->GamesNew,
			'Open games'=>$Misc->GamesOpen,
			'Active games'=>$Misc->GamesActive,
			'Finished games'=>$Misc->GamesFinished);
		$first=true;
		foreach($stats as $name=>$stat)
		{
			if ( $first ) $first=false;
			else $buf .= ' - ';

			$buf .= $name.': <strong>'.$stat.'</strong> ';
		}

		if ( !isset($User) || !$User->type['Moderator'] ) return $buf;


		$buf .= '<br /><br />';

		$stats=array(
			'<a href="gamemaster.php" class="light">Last process</a>'=>($Misc->LastProcessTime?libTime::text($Misc->LastProcessTime):'Never'),
			'<a href="admincp.php?tab=Control%20Panel%20Logs" class="light">Last mod action</a>'=>($Misc->LastModAction?libTime::text($Misc->LastModAction):'Never'),
			'<a href="admincp.php?tab=Status%20lists" class="light">Error logs</a>'=>$Misc->ErrorLogs,
			'Paused games'=>$Misc->GamesPaused,
			'<a href="admincp.php?tab=Status%20lists" class="light">Crashed games</a>'=>$Misc->GamesCrashed,
		);

		$first=true;
		foreach($stats as $name=>$stat)
		{
			if ( $first ) $first=false;
			else $buf .= ' - ';

			$buf .= $name.': <strong>'.$stat.'</strong> ';
		}

		return $buf;
	}

	static private function footerCopyright() {
		// Version, sourceforge and HTML compliance logos
		return 'webDiplomacy version <strong>'.number_format(VERSION/100,2).'</strong><br />
			<a href="http://sourceforge.net/projects/phpdiplomacy">
				<img alt="webDiplomacy @ Sourceforge"
					src="http://sourceforge.net/sflogo.php?group_id=125692" />
			</a>';
	}

	/*
	 * By jayp, saved for future cacheing improvements
	 public static function cacheControl($expire=-1, $etag=NULL, $lastmod=NULL) {
		if ($expire < 0) {
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
		} else {
			if (!empty($etag))
				header('Etag: "'.$etag.'"');
			if (!empty($lastmod))
				header('Last-Modified: '.gmdate("D, d M Y H:i:s", $lastmod)." GMT");
			header('Expires: '.gmdate("D, d M Y H:i:s", (time()+$expire)).' GMT');
			header('Cache-Control: max-age='.$expire.', must-revalidate');
			if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $etag) {
				header('HTTP/1.0 304 Not Modified', TRUE, 304);
				die();
			} else if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $lastmod) {
				header('HTTP/1.0 304 Not Modified', TRUE, 304);
				die();
			}
		}
	}*/

	public static $footerScript=array();
	public static $footerIncludes=array('utility.js','cacheUpdate.js','timeHandler.js','forum.js');
	
	public static function likeCount($likeCount) {
		if($likeCount==0) return '';
		//return ' <span class="likeCount">('.$likeCount.' like'.($likeCount>1?'s':'').')</span>';
		return ' <span class="likeCount">(+'.$likeCount.')</span>';
	}
	
	static private function footerScripts() {
		global $User;

		$buf = '';

		// onlineUsers, for the online icons
		$statsDir = libCache::dirName('stats');
		$onlineFile = $statsDir.'/onlineUsers.json';
		if( file_exists($onlineFile) )
			$buf .= '<script type="text/javascript" src="'.STATICSRV.$onlineFile.'"></script>';
		else
			$buf .= '<script type="text/javascript">onlineUsers = $A([ ]);</script>';

		if( !is_object($User) ) return $buf;
		elseif( $User->type['User'] ) // Run user-specific page modifications
		{
			// Muted users
			$gameMutePairs = array();
			foreach($User->getMuteCountries() as $gameMutePair)
				$gameMutePairs[] = '['.$gameMutePair[0].','.$gameMutePair[1].']';

			$buf .= '
			<script type="text/javascript">
			muteUsers = $A(['.implode(',',$User->getMuteUsers()).']);
			muteCountries = $A(['.implode(',',$gameMutePairs).']);
			muteThreads = $A(['.implode(',',$User->getMuteThreads()).']);
			</script>';
			unset($gameMutePairs);
			self::$footerIncludes[] = 'mute.js';
			self::$footerScript[] = 'muteAll();';

			// Participated threads
			$cacheUserParticipatedThreadIDsFilename = libCache::dirID('users',$User->id).'/readThreads.js';

			if( file_exists($cacheUserParticipatedThreadIDsFilename) )
			{
				$buf .= '<script type="text/javascript" src="'.STATICSRV.$cacheUserParticipatedThreadIDsFilename.'?nocache='.rand(0,999999).'"></script>';
				libHTML::$footerScript[]='setForumParticipatedIcons();';
			}
		}

		foreach( self::$footerIncludes as $includeJS )
			$buf .= '<script type="text/javascript" src="'.STATICSRV.JSDIR.'/'.$includeJS.'"></script>';

		// Utility (error detection, message protection), HTML post-processing,
		// time handling functions. Only logged-in users need to run these
		$buf .= '
		<script type="text/javascript">
			var UserClass = function () {
				this.id='.$User->id.';
				this.username="'.htmlentities($User->username).'";
				this.points='.$User->points.'
				this.lastMessageIDViewed='.$User->lastMessageIDViewed.';
				this.timeLastSessionEnded='.$User->timeLastSessionEnded.';
				this.type='.$User->type.';
				this.token="'.md5(Config::$secret.$User->id.$User->type).'";
			}
			User = new UserClass();

			document.observe("dom:loaded", function() {
				onlineUsers.push(User.id);

				setUserOnlineIcons();
				setForumMessageIcons();
				setPostsItalicized();
				updateTimestamps();
				updateUTCOffset();
				updateTimers();

				'.implode("\n", self::$footerScript).'
			}, this);
		</script>
		';

		return $buf;
	}
}

?>
