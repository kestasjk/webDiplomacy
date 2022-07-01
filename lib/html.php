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
	public static function pageTitle($title, $description=false)
	{
		return '<div class="content-bare content-board-header content-title-header">
					<div class="pageTitle barAlt1">
						'.$title.'
					</div>
					<div class="pageDescription">
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
		return '<img style="'.self::$hideStyle.'" class="userOnlineImg" userID="'.$userID.'" src="'.l_s('images/icons/online.png').'" alt="'.
			l_t('Online').'" title="'.l_t('User currently logged on').'" />';
	}

	static function platinum()
	{
		return '<img src="'.l_s('images/icons/platinum.png').'" alt="(P)" title="'.l_t('Donator - platinum').'" />';
	}

	static function gold()
	{
		return '<img src="'.l_s('images/icons/gold.png').'" alt="(G)" title="'.l_t('Donator - gold').'" />';
	}

	static function silver()
	{
		return '<img src="'.l_s('images/icons/silver.png').'" alt="(S)" title="'.l_t('Donator - silver').'" />';
	}

	static function bronze()
	{
		return '<img src="'.l_s('images/icons/bronze.png').'" alt="(B)" title="'.l_t('Donator - bronze').'" />';
	}

	static function service()
	{
		return '<img src="'.l_s('images/icons/service.png').'" alt="(P)" title="'.l_t('Service Award').'" />';
	}

	static function owner()
	{
		return '<img src="'.l_s('images/icons/owner.png').'" alt="(P)" title="'.l_t('Site Co-Owner').'" />';
	}

	static function adamantium()
	{
		return '<img src="'.l_s('images/icons/adamantium.png').'" alt="(P)" title="'.l_t('Donator - adamantium').'" />';
	}

	static function goldStar()
	{
		return '<img height="16" width="16" src="'.l_s('images/icons/GoldStar.png').'" alt="(G)" title="'.l_t('1st Place').'" />';
	}

	static function silverStar()
	{
		return '<img height="16" width="16" src="'.l_s('images/icons/SilverStar.png').'" alt="(S)" title="'.l_t('2nd Place').'" />';
	}

	static function bronzeStar()
	{
		return '<img height="16" width="16" src="'.l_s('images/icons/BronzeStar.png').'" alt="(B)" title="'.l_t('3rd Place').'" />';
	}

	/**
	 * The points icon
	 * @return string
	 */
	static function points()
	{
		return '<img src="'.l_s('images/icons/points.png').'" alt="D" title="'.l_t('webDiplomacy points').'" />';
	}

	static function forumMessage($threadID, $messageID)
	{
		return '<a style="'.self::$hideStyle.'" class="messageIconForum" threadID="'.$threadID.'" messageID="'.$messageID.'" href="forum.php?threadID='.$threadID.'#'.$messageID.'">'.
		'<img src="'.l_s('images/icons/mail.png').'" alt="'.l_t('New').'" title="'.l_t('Unread messages!').'" />'.'</a> ';
	}

	static function forumParticipated($threadID)
	{
		return '<a style="'.self::$hideStyle.'" class="participatedIconForum" threadID="'.$threadID.'" href="forum.php?threadID='.$threadID.'#'.$threadID.'">'.
			'<img src="'.l_s('images/icons/star.png').'" alt="'.l_t('Participated').'" title="'.l_t('You have participated in this thread.').'" />'.'</a> ';
	}

	/**
	 * The icon to mute an unmuted player, optionally with link
	 * @param $url URL to link to
	 * @return string
	 */
	static function unmuted($url=false)
	{
		$buf = '';
		if($url) $buf .= '<a onclick="return confirm(\''.l_t("Are you sure you want to mute the messages from this player?").'\');" href="'.$url.'">';
		$buf .= '<img src="'.l_s('images/icons/unmute.png').'" alt="'.l_t('Mute player').'" title="'.l_t('Mute player').'" />';
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
		$buf .= '<img src="'.l_s('images/icons/mute.png').'" alt="'.l_t('Muted. Click to un-mute.').'" title="'.l_t('Muted. Click to un-mute.').'" />';
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
		$buf .= '<img src="'.l_s('images/icons/mail.png').'" alt="'.l_t('Unread message').'" title="'.l_t('Unread message').'" />';
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
		$buf .= '<img src="'.l_s('images/icons/mail_faded.png').'" alt="'.l_t('Recent message').'" title="'.l_t('Recent message').'" />';
		if($url) $buf .= '</a>';
		return $buf;
	}

	public static function serveImage($filename, $contentType='image/png')
	{
		if ( ob_get_contents() != "" ) { die(); }

		header('Content-Length: '.filesize($filename));
		header('Content-Type: '.$contentType);

		print file_get_contents($filename);

		if( DELETECACHE ) {	unlink($filename); }

		die();
	}

	/**
	 * An external link icon
	 * @return string
	 */
	static function link()
	{
		return '<img src="'.l_s('images/historyicons/external.png').'" alt="'.l_t('Link').'" title="'.l_t('Click this to follow the link').'" />';
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
		if( isset($_SESSION['formTickets']) && isset($_REQUEST['formTicket']) && isset($_SESSION['formTickets'][$_REQUEST['formTicket']]) )
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
	 * @param $confirm Boolean to determine whether the action needs javascript confirmation
	 * @return string A link URL or an <a href>
	 */
	static function admincp($actionName, $args=null, $linkName=null,$confirm=false)
	{
		$output = 'admincp.php?tab=Control%20Panel&amp;actionName='.$actionName;

		if( is_array($args) )
			foreach($args as $name=>$val)
			{
				if ( $name == 'gameID' )
					$output .= '&amp;globalGameID='.$val;
				elseif ( $name == 'userID' )
					$output .= '&amp;globalUserID='.$val;
				elseif ( $name == 'postID' )
					$output .= '&amp;globalPostID='.$val;

				$output .= '&amp;'.$name.'='.$val;
			}

		$output .= '#'.$actionName;

		if($linkName)
			return '<a href="'.$output.'" '
			      .($confirm ? 'onclick="return confirm(\''.$linkName.': '.l_t('Please confirm this action.').'\')"' :'')
			      .' class="light">'.$linkName.'</a>';
		else
			return $output;
	}

	public static function threadLink($postID)
	{
		global $DB;

		$postID = (int)$postID;

		list($toID) = $DB->sql_row("SELECT toID FROM wD_ForumMessages WHERE id=".$postID);

		if( $toID == null || $toID == 0 )
			$toID = $postID;

		return '<a href="forum.php?threadID='.$toID.'#'.$postID.'">'.l_t('Go to thread').'</a>';
	}
	static function admincpType($actionType, $id)
	{
		return '<a href="admincp.php?tab=Control%20Panel&amp;global'.$actionType.'ID='.$id.'#'.strtolower($actionType).'Actions">
			'.l_t('View %s admin-actions',l_t(strtolower($actionType))).'</a>';
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
			die('<html><head><title>'.l_t('webDiplomacy fatal error').'</title></head>
				<body><p>'.l_t('Error occurred during script startup, usually a result of inability to connect to the database:').'</p>
				<p>'.$message.'</p></body></html>');
		}

		if(!defined('ERROR'))
			define('ERROR',true);

		self::notice(l_t('Error'), $message);
	}

	/**
	 * Renders reusable modal for tutorial mode, future help functionality
	 * Breaks up message into array of slides based on HTML <br> element
	 * 
	 * @param string $page
	 * @param string $message
	 */
	static public function help($page, $message)
	{
		if (isset($_COOKIE['wD-Tutorial'])) 
		{
			$bootstrap = "";
			$messages = [];
			$i = 0;

			if ($message) 
			{
				$messages = preg_split('/<br[^>]*>/i', $message);
			}

			if (count($messages) > 0) 
			{
				$bootstrap = '
					<div class="tutorial-wrap">
						<div class="tutorial-header">
							<h2>webDiplomacy Tutorial - '.$page.'</h2>
						</div>
				';

				$messages = array_diff($messages, [""]);

				foreach ($messages as $key => $m) 
				{
					$i++;
					if ($i + 1 <= count($messages)) 
					{
						if ($i == 1) 
						{
							$bootstrap .= '<div class="tutorial tutorial-display">';
						} 
						else 
						{
							$bootstrap .= '<div class="tutorial tutorial-hide">';
						}

						$bootstrap .= '
							<p id="tutorial-'.$i.'">'. $m .'</p>
							<div class="tutorial-buttons">
								<div 
									class="form-submit"
									onclick="indexForward('.$i.')"
								>
									Next
								</div>
								<div 
									class="form-submit tutorial-close"
									onclick="hideHelp()"
								>
									Close
								</div>
							</div>
						</div>';
					} 
					else 
					{
						if ($i == 1) 
						{
							$bootstrap .= '<div class="tutorial tutorial-display">';
						} 
						else 
						{
							$bootstrap .= '<div class="tutorial tutorial-hide">';
						}

						$bootstrap .= '
							<p id="tutorial-'.$i.'">'. $m .'</p>
							<div class="tutorial-buttons">
								<div 
									class="form-submit"
									onclick="hideHelp()"
								>
									Close
								</div>
								<div 
									class="form-submit tutorial-end"
									onclick="endTutorial()"
								>
									I do not need a tutorial (turn these off)
								</div>
							</div>
						</div>';
					}
				}

				$bootstrap .= '</div>';
			}

			print $bootstrap;
		}
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
		require_once(l_r('global/definitions.php'));
		
		global $User;
		global $UserOptions;
		$variantCSS=array();

		// set user's dark or light theme
		if(isset($User) && ($User->options->value['darkMode'] == 'No'))
			$darkMode = '';
		else
			$darkMode = 'darkMode/';

		foreach(Config::$variants as $variantName)
			$variantCSS[] = '<link rel="stylesheet" href="'.STATICSRV.l_s('variants/'.$variantName.'/resources/'.$darkMode.'style.css').'?var='.CSSVERSION.'" type="text/css" />';
		$variantCSS=implode("\n",$variantCSS);

		/*
		 * This line when included in the header caused certain translated hyphenated letters to come out as black diamonds with question marks.
		 */
		return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml">
		<head>
		<meta http-equiv="content-type" content="text/html;charset=utf-8" />
			<meta http-equiv="Content-Style-Type" content="text/css" />
			<meta name="robots" content="index,follow" />
			<meta name="description" content="'.l_t('webDiplomacy is an online, multiplayer, turn-based strategy game that lets you play Diplomacy online.').'" />
			<meta name="keywords" content="'.l_t('diplomacy,diplomacy game,online diplomacy,classic diplomacy,web diplomacy,diplomacy board game,play diplomacy,php diplomacy').'" />
			<link rel="shortcut icon" href="'.STATICSRV.l_s('favicon.ico').'" />
			<link rel="icon" href="'.STATICSRV.l_s('favicon.ico').'" />
			
			<script type="text/javascript" src="useroptions.php"></script>
			<script type="text/javascript" src="javascript/clickhandler.js"></script>
			<script type="text/javascript" src="'.STATICSRV.l_j('contrib/js/prototype.js').'"></script>
			<script type="text/javascript" src="'.STATICSRV.l_j('contrib/js/scriptaculous.js').'"></script>
			<link rel="stylesheet" type="text/css" href="'.STATICSRV.l_s('contrib/js/pushup/src/css/pushup.css').'" />
			<script type="text/javascript" src="'.STATICSRV.l_j('contrib/js/pushup/src/js/pushup.js').'"></script>
			<script type="text/javascript">
				STATICSRV="'.STATICSRV.'";
				var cssDirectory = "'.CSSDIR.'";
					var cssVersion = "'.CSSVERSION.'";
			</script>

			<link rel="stylesheet" id="global-css" href="'.CSSDIR.l_s('/'.$darkMode.'global.css').'?ver='.CSSVERSION.'" type="text/css" />
			<link rel="stylesheet" id="game-panel-css" href="'.CSSDIR.l_s('/'.$darkMode.'gamepanel.css').'?ver='.CSSVERSION.'" type="text/css" />
			<link rel="stylesheet" id="home-css" href="'.CSSDIR.l_s('/'.$darkMode.'home.css').'?ver='.CSSVERSION.'" type="text/css" />
			'.$variantCSS.'

			<script type="text/javascript" src="'.l_j('javascript/desktopMode.js').'?ver='.JSVERSION.'"></script>
			<title>'.l_t('%s - webDiplomacy',$title).'</title>
		</head>';
	}

	/**
	 * Print the HTML which comes before the main content; title, menu, notification bar.
	 *
	 * @param string|bool[optional] $title If a string is given it will be used as the page title
	 */
	static public function starthtml($title=false)
	{
		global $User, $DB;

		self::$scriptname = $scriptname = basename($_SERVER['PHP_SELF']);

		$pages = libHTML::pages();

		if ( isset($User) and ! isset($pages[$scriptname]) )
		{
			die(l_t('Access to this page denied for your account type.'));
		}

		print libHTML::prebody($title===FALSE ? l_t($pages[$scriptname]['name']) : $title).
			'<body>'.libHTML::menu($pages, $scriptname);

		if( defined('FACEBOOKSCRIPT') ) 
		{
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
					<p class="notice">'.l_t('You do not have JavaScript enabled. It is required to use webDiplomacy fully.').'</p>
				</div></noscript>';

		print self::globalNotices();

		if (isset($User) && $User->userIsTempBanned() )
		{
			if ( $User->tempBanReason != 'System' && $User->tempBanReason != '')
			{
				print '<div class="content-notice">
					<p class="notice"><br>You are blocked from joining, rejoining, or creating new games by the moderators for '.libTime::remainingText($User->tempBan).
					 ' for the following reason:</br> '.$User->tempBanReason.' </br>
					Contact the moderators at '.Config::$modEMail.' for help. If you attempt to get around this temp ban 
					by making a new account your accounts will be banned with no chance for appeal.<br><br></p>
				</div>';
			}
			else if ( ($User->tempBan - time() ) > (60*60*24*180))
			{
				print '<div class="content-notice">
					<p class="notice"><br>You are blocked from joining, rejoining, or creating new games for a year because you were too unreliable. 
					Contact the moderators at '.Config::$modEMail.' for help. If you attempt to get around this temp ban 
					by making a new account your accounts will be banned with no chance for appeal.<br><br></p>
				</div>';
			}
			else
			{
				print '<div class="content-notice">
						<p class="notice"><br>You are blocked from joining, rejoining, or creating new games for '.libTime::remainingText($User->tempBan).
						' because you were too unreliable. Contact the moderators at '.Config::$modEMail.' if you need help.<br><br></p>
					</div>';
			}
		}

		if( isset($User) and strlen($User->username) > 10 && substr($User->username,0,8)=="diplonow" && $scriptname != 'botgamecreate.php' && $scriptname != 'logon.php' && $scriptname != 'api.php' && $scriptname != 'help.php' && $scriptname != 'datc.php' && $scriptname != 'faq.php' && $scriptname != 'rules.php' )
		{
			list($gameID) = $DB->sql_row("SELECT gameID FROM wD_Members WHERE userID = " . $User->id . " ORDER BY gameID DESC LIMIT 1");

			print libHTML::pageTitle('This is a quick-game only account',l_t('Your quick-game account cannot view this page. Available options below.'));

			print '<div class="content-notice"><br>This account type is only allowed to play instant games against AI/bots and view certain help pages. Please <a href="logon.php?logoff=on">log off</a> and register an unrestricted user account to play games against humans and view user-only areas.<br><br />
				<a href="/beta/?gameID='.$gameID.'">Click here</a> to return to your AI game, or <a href="botgamecreate.php?diplonow=on">click here</a> to start a new AI game.
				<br>
				You can find more help and information on the <a href="help.php">help page</a>.
				<br>
				</div>';
			
			libHTML::footer();

			die();
		}

		if ( is_object($User) && $User->type['User'] )
		{
			$gameNotifyBlock = libHTML::gameNotifyBlock();
			if ( $gameNotifyBlock )
				print '<div class="content-notice"><div class="gamelistings-tabs">'.$gameNotifyBlock.'</div></div>';
		}
	}

	/**
	 * The server-wide notices, displayed at the top of the page if enabled, and defined in config.php
	 *
	 * @return string
	 */
	static private function globalNotices()
	{
		global $Misc, $User, $DB;
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
			list($contents) = $DB->sql_row("SELECT message FROM wD_Config WHERE name = 'Panic'");
			$notice[]=$contents;
		}

		if ( $Misc->Notice && !is_null($DB))
		{
			list($contents) = $DB->sql_row("SELECT message FROM wD_Config WHERE name = 'Notice'");
			$notice[]=$contents;
		}

		if ( ( time() - $Misc->LastProcessTime ) > Config::$downtimeTriggerMinutes*60 )
			$notice[] = l_t("The last process time was over %s minutes ".
				"ago (at %s); the server ".
				"is not processing games until the cause is found and games are given extra time.",
				Config::$downtimeTriggerMinutes,libTime::text($Misc->LastProcessTime));

		if ( $notice )
			return '<div class="content-notice"><p class="notice">'.implode('</p><div class="hr"></div><p class="notice">',$notice).'</p></div>';
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
			"SELECT g.id, g.variantID, g.name, g.phase, m.orderStatus, m.countryID, (m.newMessagesFrom+0) as newMessagesFrom, g.processStatus
			FROM wD_Members m
			INNER JOIN wD_Games g ON ( m.gameID = g.id )
			WHERE m.userID = ".$User->id."
				AND ( ( NOT m.orderStatus LIKE '%Ready%' AND NOT m.orderStatus LIKE '%None%' AND g.phase != 'Finished' ) OR NOT ( (m.newMessagesFrom+0) = 0 ) ) ".
				( ($User->userIsTempBanned()) ? "AND m.status != 'Left'" : "" ) // ignore left games of temp banned user who are banned from rejoining
				." ORDER BY  g.processStatus ASC, g.processTime ASC");
		$gameIDs = array();
		$notifyGames = array();
		while ( $game = $DB->tabl_hash($tabl) )
		{
			$id = (int)$game['id'];
			$gameIDs[] = $id;
			$notifyGames[$id] = $game;
		}

		$gameNotifyBlock = '';

		if ( $User->notifications->PrivateMessage and ! isset($_REQUEST['notices']))
		{
			$gameNotifyBlock .= '<span class=""><a href="index.php?notices=on">'.
				l_t('PM').' <img src="'.l_s('images/icons/mail.png').'" alt="'.l_t('New private messages').'" title="'.l_t('New private messages!').'" />'.
				'</a></span> ';
		}

		if( isset(Config::$customForumURL) ) 
		{
			// We are using a PHPBB install; pull private messages from the phpBB install for this user
			$tabl = $DB->sql_tabl(
			"SELECT p.msg_id, p.pm_new, p.pm_unread, fromm.webdip_user_id, fromU.username, fromU.points, fromU.type
				FROM phpbb_privmsgs_to p
				INNER JOIN phpbb_users toU ON p.user_id = toU.user_id
				INNER JOIN phpbb_users fromm ON fromm.user_id = p.author_id
				INNER JOIN wD_Users fromU ON fromU.Id = fromm.webdip_user_id
				WHERE (pm_new = 1 OR pm_unread = 1) AND toU.webdip_user_id = ".$User->id);
			while($row_hash = $DB->tabl_hash($tabl)) 
			{
				$profile_link = $row_hash['username'];
				$profile_link.=' ('.$row_hash['points'].libHTML::points().User::typeIcon($row_hash['type']).')';

				$gameNotifyBlock .= '<span class=""><a href="'.Config::$customForumURL.'ucp.php?i=pm&mode=view&p='.$row_hash['msg_id'].'">'.
						l_t('PM from %s',$profile_link).' <img src="'.l_s('images/icons/mail.png').'" alt="'.l_t('New private message').'" title="'.l_t('New private message!').'" />'.
						'</a></span> ';
			}
		}

		foreach ( $gameIDs as $gameID )
		{
			$notifyGame = $notifyGames[$gameID];
			require_once(l_r('objects/basic/set.php'));

			// Games that are finished should show as 'no orders'
			if ( $notifyGame['phase'] != 'Finished') 
			{
					$notifyGame['orderStatus'] = new setMemberOrderStatus($notifyGame['orderStatus']);
			} 
			else 
			{
					$notifyGame['orderStatus'] = new setMemberOrderStatus('None');
			}

			// Don't print the game if we're looking at it.
			if ( isset($_REQUEST['gameID']) and $_REQUEST['gameID'] == $gameID )
				continue;

			$gameNotifyBlock .= '<span class="variant'.Config::$variants[$notifyGame['variantID']].'">'.
				'<a gameID="'.$gameID.'" class="country'.$notifyGame['countryID'].'" href="board.php?gameID='.$gameID.'">'.
				$notifyGame['name'];

			if ( $notifyGame['processStatus'] == 'Paused' )
				$gameNotifyBlock .= '-<img src="'.l_s('images/icons/pause.png').'" alt="'.l_t('Paused').'" title="'.l_t('Game paused').'" />';

			$gameNotifyBlock .= ' ';

			$gameNotifyBlock .= $notifyGame['orderStatus']->icon();
			if ( $notifyGame['newMessagesFrom'] )
				$gameNotifyBlock .= '<img src="'.l_s('images/icons/mail.png').'" alt="'.l_t('New messages').'" title="'.l_t('New messages!').'" />';

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
	static public function pages()
	{
		global $User;

		$allUsers = array('Guest','User','Moderator','Admin');
		$loggedOnUsers = array('User','Moderator','Admin');

		$links=array();

		// Items displayed in the menu
		$links['index.php']=array('name'=>'Home', 'inmenu'=>TRUE, 'title'=>"See what's happening");

		if( isset(Config::$customForumURL) ) 
		{
			$links[Config::$customForumURL]=array('name'=>'Forum', 'inmenu'=>TRUE, 'title'=>"The forum; chat, get help, help others, arrange games, discuss strategies");
			$links['forum.php']=array('name'=>'Old Forum', 'inmenu'=>false, 'title'=>"The old forum; chat, get help, help others, arrange games, discuss strategies");
		} 
		else 
		{
			$links['forum.php']=array('name'=>'Forum', 'inmenu'=>TRUE, 'title'=>"The forum; chat, get help, help others, arrange games, discuss strategies");
		}
		
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
				$links['detailedSearch.php']=array('name'=>'Search', 'inmenu'=>TRUE, 'title'=>"advanced search of users and games");
				$links['usercp.php']=array('name'=>'Settings', 'inmenu'=>TRUE, 'title'=>"Change your user specific settings");
			}
		}

		$links['help.php']=array('name'=>'Help/Donate', 'inmenu'=>TRUE, 'title'=>'Get help and information; guides, intros, FAQs, stats, links');
		$links['diplonow.php']=array('name'=>'DiploNow', 'inmenu'=>FALSE, 'title'=>'Start playing Diplomacy with bots now, stop whenever you want.');

		// Items not displayed on the menu
		$links['map.php']=array('name'=>'Map', 'inmenu'=>FALSE);
		$links['faq.php']=array('name'=>'FAQ', 'inmenu'=>FALSE);
		$links['contactUs.php']=array('name'=>'Contact Info', 'inmenu'=>FALSE);
		$links['contactUsDirect.php']=array('name'=>'Contact Us', 'inmenu'=>FALSE);
		$links['donations.php']=array('name'=>'Donations', 'inmenu'=>FALSE);
		$links['tournaments.php']=array('name'=>'Tournaments', 'inmenu'=>FALSE);
		$links['tournamentManagement.php']=array('name'=>'Manage Tournaments', 'inmenu'=>FALSE);
		$links['rules.php']=array('name'=>'Rules', 'inmenu'=>FALSE);
		$links['recentchanges.php']=array('name'=>'Recent changes', 'inmenu'=>FALSE);
		$links['intro.php']=array('name'=>'Intro', 'inmenu'=>FALSE);
		$links['ghostRatings.php']=array('name'=>'The Ghost Ratings', 'inmenu'=>FALSE);
		$links['credits.php']=array('name'=>'Credits', 'inmenu'=>FALSE);
		$links['board.php']=array('name'=>'Board', 'inmenu'=>FALSE);
		$links['profile.php']=array('name'=>'Profile', 'inmenu'=>FALSE);
		$links['search.php']=array('name'=>'Find user', 'inmenu'=>false);
		$links['userprofile.php']=array('name'=>'ProfileNew', 'inmenu'=>FALSE);
		$links['translating.php']=array('name'=>'Translating', 'inmenu'=>FALSE);
		$links['points.php']=array('name'=>'Points', 'inmenu'=>FALSE);
		$links['halloffame.php']=array('name'=>'Hall of fame', 'inmenu'=>FALSE);
		$links['developers.php']=array('name'=>'Developer info', 'inmenu'=>FALSE);
		$links['datc.php']=array('name'=>'DATC', 'inmenu'=>FALSE);
		$links['variants.php']=array('name'=>'Variants', 'inmenu'=>FALSE);
		$links['adminInfo.php']=array('name'=>'Admin Info', 'inmenu'=>FALSE);
		$links['tournamentInfo.php']=array('name'=>'Tournament Info', 'inmenu'=>FALSE);
		$links['tournamentScoring.php']=array('name'=>'Tournament Scoring', 'inmenu'=>FALSE);
		$links['tournamentRegistration.php']=array('name'=>'Tournament Registration', 'inmenu'=>FALSE);
		$links['botgamecreate.php']=array('name'=>'New Bot Game', 'inmenu'=>TRUE, 'title'=>"Start up a new bot game");
		$links['group.php']=array('name'=>'User Relationships', 'inmenu'=>FALSE);


		if ( is_object($User) )
		{
			if ( $User->type['Admin'] or $User->type['Moderator'] )
			{
				$links['search.php']=array('name'=>'Find user', 'inmenu'=>true);  // Overrides the previous one with one that appears in the menu
				$links['admincp.php']=array('name'=>'Admin CP', 'inmenu'=>true);
			}
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
							<img id="logo" src="'.l_s('images/logo.png').'" alt="'.l_t('webDiplomacy').'" />
						</a>';


		if ( is_object( $User ) )
		{
			if ( ! $pages[$scriptname]['inmenu'] )
				$arguments = str_replace('&', '&amp;', $_SERVER['QUERY_STRING']);
			else
				$arguments = '';

			$menu .= '
				<div>
					<div id="header-welcome">
						'.(is_object($User)?l_t('Welcome, %s',$User->profile_link(TRUE)).' -
						<span class="logon">('.
							($User->type['User'] ?
							'<a href="logon.php?logoff=on" class="light">'.l_t('Log off').'</a>)'.
								( defined('AdminUserSwitch') ? ' (<a href="index.php?auid=0" class="light">'.l_t('Switch back').'</a>)' : '' )
							:'<a href="logon.php" class="light">'.l_t('Log on').'</a>)').
						'</span>'
						:l_t('Welcome, Guest')).'
					</div>';

			/* begin dropdown menu */
			$menu .= '
			<div id="header-goto">
            <div class="nav-wrap">
			<div class = "nav-tab"> <a href="index.php?" title="See what\'s happening">Home</a> </div>';
			if( isset(Config::$customForumURL) )
			{
				$menu.='<div class = "nav-tab"> <a href="'.Config::$customForumURL.'" title="The forum; chat, get help, help others, arrange games, discuss strategies">Forum</a> </div>';
			}
			else
			{
				$menu.='<div class = "nav-tab"> <a href="forum.php" title="The forum; chat, get help, help others, arrange games, discuss strategies">Forum</a> </div>';
			}

			if (is_object($User))
			{
				if( !$User->type['User'] )
				{
					$menu.='
					<div class="nav-tab">
						<a href="logon.php" title="Log onto webDiplomacy using an existing user account">Log on</a>
					</div>';
					$menu.='
					<div class="nav-tab">
						<a href="register.php" title="Make a new user account">Register</a>
					</div>';
					$menu.='
					<div id="navSubMenu" class = "clickable nav-tab">Help ▼
                        <div id="nav-drop">
                        	<a href="rules.php">Site Rules</a>
							<a href="faq.php" title="Frequently Asked Questions">FAQ</a>
							<a href="intro.php" title="Intro to Diplomacy">Diplomacy Intro</a>
							<a href="points.php" title="Points and Scoring Systems">Points/Scoring</a>
							<a href="variants.php" title="Active webDiplomacy variants">Variants</a>
							<a href="help.php" title="Site information, guides, stats, links">More Info</a>
							<a href="donations.php">Donate</a>
                        </div>
                    </div>';
				}
				else
				{
					$menu.='
					<div id="navSubMenu" class="clickable nav-tab">Search ▼
                        <div id="nav-drop">
							<a href="search.php">Find User</a>
							<a href="gamelistings.php?gamelistType=Search">Game Search</a>
							<a href="detailedSearch.php" title="advanced search of users and games">Advanced Search</a>
						</div>
					</div>
					<div id="navSubMenu" class="clickable nav-tab">Games ▼
                        <div id="nav-drop">
							<a href="gamelistings.php?gamelistType=New" title="Game listings; a searchable list of the games on this server">New Games</a>
							<a href="gamelistings.php?gamelistType=Open%20Positions" title="Open positions dropped by other players, free to claim">Open Games</a>
							<a href="gamecreate.php" title="Start up a new game">Start a New Game</a>
							<a href="botgamecreate.php" title="Start up a new bots-only game">Start an AI/Bot Game</a>
							<a href="gamelistings.php?gamelistType=Active" title="View/Spectate games currently running">Active Games</a>
							<!-- <a href="ghostRatings.php" title="Ghost Ratings Information">Ghost Ratings</a> -->
							<a href="tournaments.php" title="Information about tournaments on webDiplomacy">Tournaments</a>
							<a href="halloffame.php" title="Information about tournaments on webDiplomacy">Hall of Fame</a>
                        </div>
                    </div>
					<div id="navSubMenu" class="clickable nav-tab">Account ▼
						<div id="nav-drop">';
						if( isset(Config::$customForumURL) ) {
							$menu.='
								<a href="contrib/phpBB3/ucp.php?i=pm" title="Read your messages">Private Messages</a>
								<a href="contrib/phpBB3/ucp.php?i=179" title="Change your forum user settings">Forum Settings</a>';
						}
						$menu.='
							<a href="usercp.php" title="Change your user specific settings">Site Settings</a>
							<a href="group.php" title="Manage your user relationships">User Relationships</a>
						</div>
                	</div>
                	<div id="navSubMenu" class = "clickable nav-tab">Help ▼
                        <div id="nav-drop">
                        	<a href="rules.php">Site Rules</a>
							<a href="faq.php" title="Frequently Asked Questions">FAQ</a>
							<a href="intro.php" title="Intro to Diplomacy">Diplomacy Intro</a>
							<a href="points.php" title="Points and Scoring Systems">Points/Scoring</a>
							<a href="variants.php" title="Active webDiplomacy variants">Variants</a>
							<a href="help.php" title="Site information; guides, stats, links">More Info</a>
							<a href="contactUsDirect.php">Contact Us</a>
							<a href="donations.php">Donate</a>
                        </div>
                    </div>';
				}
			}

			if ( is_object($User) )
			{
				if ( $User->type['Admin'] or $User->type['Moderator'] )
				{
					$menu.=' <div id="navSubMenu" class = "clickable nav-tab">Mods ▼
                        <div id="nav-drop">
							<a href="admincp.php">Admin CP</a>';

					if( isset(Config::$customForumURL) ) { $menu.='<a href="contrib/phpBB3/mcp.php">Forum CP</a>'; }

					$menu.='
						<a href="admincp.php?tab=Multi-accounts">Multi Finder</a>
						<a href="admincp.php?tab=Chatlogs">Pull Press</a>
						<a href="admincp.php?tab=AccessLog">Access Log</a>
						<a href="search.php">Find User</a>';

					if ( $User->type['Admin'] && isset(Config::$customForumURL))
					{
						$menu.='<a href="adminInfo.php">Admin Info</a>';
					}

					$menu.=' </div>
					</div>';
				}
			}
			$menu.='</div></div></div>';
		}
		else
		{
			$menu .= '
				<div id="header-welcome">&nbsp;</div>
					<div id="header-goto">
						<div class="nav-wrap">
							<div class="nav-tab">
								<a style="color:white" href="index.php">'.l_t('Home').'</a>
							</div>
							<div class="nav-tab">
							<a style="color:white" href="'.$scriptname.'">'.l_t('Reload current page').'</a>
							</div>
						</div>
					</div>';
		}
		$menu .= '
			</div></div>
			<div id="seperator"></div>
			<div id="seperator-fixed"></div>
			<!-- Menu end. -->';

		/* end dropdown menu */

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

			print '<br />'.l_t('Times are <strong id="UTCOffset">UTC+0:00</strong>');

			print Config::customFooter();

			if( Config::$debug )
			{
				print self::footerDebugData();
			}

			print self::footerScripts();
		}
		else
		{
			// $DB isn't available, something went wrong
			print self::footerCopyright();
		}

		print '</div></body></html>';

		close();
	}

	private static function footerDebugData()
	{
		global $Locale, $DB;

		$buf = '';
		if( is_object($DB) )
			$buf .= $DB->profilerPrint();

		if( is_object($Locale) )
		{
			$buf .= '<br /><strong>Missed localization lookups:</strong><br />';
			foreach($Locale->failedLookups as $failedText)
				$buf .= htmlentities($failedText).'<br />';
		}

		return $buf;
	}

	private static function footerStats()
	{
		global $DB, $Misc, $User;
		require_once(l_r('global/definitions.php'));

		$buf = '';

		// Run time, select queries, insert queries
		$buf .= l_t('Rendered in: <strong>%ssec</strong> - '.
			'Data retrievals: <strong>%s</strong> - '.
			'Data insertions: <strong>%s</strong>',
				round((microtime(true)-$GLOBALS['scriptStartTime']),2),
				$DB->getqueries,
				$DB->putqueries).' ';

		if( function_exists('memory_get_usage') )
			$buf .= ' - '.l_t('Memory used: <strong>%sMB</strong>',round((memory_get_usage()/1024)/1024, 3)).' ';

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

			$buf .= l_t($name).': <strong>'.$stat.'</strong> ';
		}
		$buf .= ' - '.l_t('Pages served: <strong>%s</strong>',$Misc->Hits);
		$buf .= '<br />';

		$stats=array('Starting games'=>$Misc->GamesNew,
			'Joinable games'=>$Misc->GamesOpen,
			'Active games'=>$Misc->GamesActive,
			'Finished games'=>$Misc->GamesFinished);
		$first=true;

		foreach($stats as $name=>$stat)
		{
			if ( $first ) $first=false;
			else $buf .= ' - ';

			$buf .= l_t($name).': <strong>'.$stat.'</strong> ';
		}

		if ( !isset($User) || !$User->type['Moderator'] ) return $buf;

		$buf .= '<br /><br />';

		$stats=array(
			'<a href="gamemaster.php" class="light">'.l_t('Last process').'</a>'=>($Misc->LastProcessTime?libTime::text($Misc->LastProcessTime):l_t('Never')),
			'<a href="admincp.php?tab=Control%20Panel%20Logs" class="light">'.l_t('Last mod action').'</a>'=>($Misc->LastModAction?libTime::text($Misc->LastModAction):l_t('Never')),
			'<a href="admincp.php?tab=Status%20lists" class="light">'.l_t('Error logs').'</a>'=>$Misc->ErrorLogs,
			l_t('Paused games')=>$Misc->GamesPaused,
			'<a href="admincp.php?tab=Status%20lists" class="light">'.l_t('Crashed games').'</a>'=>$Misc->GamesCrashed,
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

	static private function footerCopyright()
	{
		// Version, sourceforge and HTML compliance logos
		return l_t('webDiplomacy version <strong>%s</strong>',number_format(VERSION/100,2)).'<br />
			<div>
			<a class="light" id="js-desktop-mode" style="cursor: pointer; color: #006699;" onclick="toggleDesktopMode(true)">Enable Desktop Mode</a>
			</div>
			<br />

			<a href="http://github.com/kestasjk/webDiplomacy" class="light">GitHub Project</a> |
			<a href="http://github.com/kestasjk/webDiplomacy/issues" class="light">Bug Reports</a> | <a href="mailto:'.Config::$modEMail.'" class="light">Moderator Email</a> |
			<a href="contactUsDirect.php" class="light">Contact Us Directly</a>';
	}

	public static $footerScript=array();
	public static $footerIncludes=array();

	public static function likeCount($likeCount)
	{
		if($likeCount==0) return '';
		return ' <span class="likeCount">(+'.$likeCount.')</span>';
	}

	static private function footerScripts()
	{
		global $User, $Locale;

		$buf = '';

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
			self::$footerIncludes[] = l_j('mute.js');
			self::$footerScript[] = l_jf('muteAll').'();';

			// Participated threads
			$cacheUserParticipatedThreadIDsFilename = libCache::dirID('users',$User->id).'/readThreads.js';

			if( file_exists($cacheUserParticipatedThreadIDsFilename) )
			{
				$buf .= '<script type="text/javascript" src="'.STATICSRV.$cacheUserParticipatedThreadIDsFilename.'?nocache='.rand(0,999999).'"></script>';
				libHTML::$footerScript[]=l_jf('setForumParticipatedIcons').'();';
			}
		}

		if( is_object($Locale) )
			$Locale->onFinish();

		// Add the javascript includes:
		$footerIncludes = array();
		$footerIncludes[] = l_j('../locales/layer.js');
		$footerIncludes[] = l_j('../locales/English/layer.js');
		$footerIncludes[] = l_j('contrib/sprintf.js');
		$footerIncludes[] = l_j('utility.js');
		$footerIncludes[] = l_j('cacheUpdate.js');
		$footerIncludes[] = l_j('timeHandler.js');
		$footerIncludes[] = l_j('forum.js');
		$footerIncludes[] = l_j('Color.Vision.Daltonize.js');
		$footerIncludes[] = l_j('../cache/stats/onlineUsers.json');

		// Don't localize all the footer includes here, as some of them may be dynamically generated
		foreach( array_merge($footerIncludes,self::$footerIncludes) as $includeJS ) // Add on the dynamically added includes
			$buf .= '<script type="text/javascript" src="'.STATICSRV.JSDIR.'/'.$includeJS.'?ver='.JSVERSION.'"></script>';

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
				this.token="'.md5(Config::$secret.$User->id.'Array').'";
				this.darkMode="'.$User->options->value['darkMode'].'";
			}
			User = new UserClass();
			var headerEvent = document.getElementsByClassName("clickable");

			WEBDIP_DEBUG='.(Config::$debug ? 'true':'false').';

			document.observe("dom:loaded", function() {

				try {
					'.l_jf('Locale.onLoad').'();

					'.l_jf('setForumMessageIcons').'();
					'.l_jf('setPostsItalicized').'();
					'.l_jf('updateTimestamps').'();
					'.l_jf('updateTimestampGames').'();
					'.l_jf('updateUTCOffset').'();
					'.l_jf('updateTimers').'();

					'.implode("\n", self::$footerScript).'

					'.l_jf('Locale.afterLoad').'();
				}
				catch( e ) {
					'.(Config::$debug ? 'alert(e);':'').'
				}
			}, this);
			document.observe("click", function(e) {
				try {
					'.l_jf('clickOut').'(e);
				} catch (e) {
					'.(Config::$debug ? 'alert(e);':'').'
				}
			}, this)
			for (var i = 0; i < headerEvent.length; i++) {
				headerEvent[i].addEventListener("click", function(e){
					try {
						'.l_jf('click').'(e);
					} catch ( e ){
						'.(Config::$debug ? 'alert(e);':'').'
					}
				}, this);
			}
			var toggle = localStorage.getItem("desktopEnabled");
			var darkMode = localStorage.getItem("darkModeEnabled");
			var dark = User.darkMode;
			if (dark == "Yes") {
				dark = true;
			} else {
				dark = false;
			}
			localStorage.setItem("darkModeEnabled", dark);
			var toggleElem = document.getElementById(\'js-desktop-mode\');
            if (toggle == "true") {
                if(toggleElem !== null) {
                	toggleElem.innerHTML = "Disable Desktop Mode";
                }
            } else {
                if(toggleElem !== null) {
                	toggleElem.innerHTML = "Enable Desktop Mode";
                }
            }
			setUserOnlineIcons();
		</script>
		';

		if( Config::$debug )
			$buf .= '<br /><strong>JavaScript localization lookup failures:</strong><br /><span id="jsLocalizationDebug"></span>';

		return $buf;
	}
}

?>
