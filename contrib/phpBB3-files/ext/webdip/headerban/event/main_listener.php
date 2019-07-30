<?php

namespace webdip\headerban\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class main_listener implements EventSubscriberInterface
{
	// The relative path from phpBB3 to the webDip root. Since it should be in contrib/phpBB3 the default should work:
	private $RELATIVEPATH = '../../';
	
	// The URL path to the root webDip location, for finding images etc
	private $WEBDIPPATH = '../../';
	
	// Contains an array of the notices to be displayed
	protected $noticeBar;
	
	// An array of game data to display
	protected $gameBar;
	
	// This user's points
	protected $points;
	
	// This user's notifications
	protected $notifications;
	
	// The contents of webDip's Misc table
	protected $misc;
	
	/* @var \phpbb\controller\helper */
	protected $db;
	
	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\template\template */
	protected $template;
	
	/* @var \phpbb\user */
	protected $user;

	// If set to true the extension will not attempt to run
	protected $disableExt;
	
	/**
	 * Constructor
	 *
	 * @param \phpbb\controller\helper $helper   Controller helper object
	 * @param \phpbb\template\template $template Template object
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\user $user)
	{
		$this->db = $db;
		$this->helper = $helper;
		$this->template = $template;
		$this->user = $user;
		$this->points = 0;
		$this->noticeBar = array();
		$this->gameBar = array();
		$this->misc = array();
		
		if( file_exists($this->RELATIVEPATH.'config.php') ) {
			define('IN_CODE',true);
			require_once($this->RELATIVEPATH.'config.php');
			// Ensure certain values do not remain in memory, in case phpBB leaks data
			\Config::$database_username='';
			\Config::$database_password='';
			\Config::$salt='';
			\Config::$secret='';
			\Config::$gameMasterSecret='';
			\Config::$jsonSecret='';
			\Config::$mailerConfig=array();
			define('IN_CODE',false);
			$this->disableExt = false;
		}
		else
		{
			// This may occur if in the admin CP, in which case we do not want this to run anyway
			$this->disableExt = true;
		}
	}
	
    /**
     * Assign functions defined in this class to event listeners in the core
     *
     * @return array
     */
    static public function getSubscribedEvents()
    {
        return array(
            'core.user_setup' => 'load_webdip' // Event fires on every page, including ACP
        );
    }
	
    /**
     * @param \phpbb\event\data $event The variant CSS
     */
	public function variantCSS($finalTheme)
	{
		if ($finalTheme == 'dark')
		{
			$variantCSS=array();
			foreach(\Config::$variants as $variantName)
				$variantCSS[] = '<link rel="stylesheet" href="'.$this->WEBDIPPATH.('variants/'.$variantName.'/resources/darkMode/style.css').'" type="text/css" />';
			//$variantCSS[] = '<link rel="stylesheet" href="'.$this->WEBDIPPATH.('/css/darkMode/global.css').'" type="text/css" />';
			return implode("\n",$variantCSS);
		}
		else 
		{
			$variantCSS=array();
			foreach(\Config::$variants as $variantName)
				$variantCSS[] = '<link rel="stylesheet" href="'.$this->WEBDIPPATH.('variants/'.$variantName.'/resources/style.css').'" type="text/css" />';
			return implode("\n",$variantCSS);
		}
		
	}
    /**
     * @param \phpbb\event\data $event The event object
     */
    public function load_webdip($event)
    {
		if( $this->disableExt )
		{
			$this->template->assign_vars(array(
				'U_WD_WEBDIPPOINTS' => '',
				'U_WD_GAMENOTIFYBLOCK' => '',
				'U_WD_NOTICEBLOCK' => ''
			));
			
			return;
		}
		
		if( isset($this->user->data['webdip_user_id']) && $this->user->data['webdip_user_id'] >= 2 ) 
		{
			$wdId = (int)$this->user->data['webdip_user_id'];
			
			$theme = $this->db->sql_query("SELECT case when darkMode is null or darkMode = 'No' then 'light' else 'dark' end as theme FROM wD_UserOptions WHERE userID =".$wdId);
			while ($row = $this->db->sql_fetchrow($theme))
			{
				$finalTheme = $row['theme'];
			}
			$this->db->sql_freeresult($theme);
			
			$this->fetchMisc();
			$this->fetchAndCheckUser($wdId);
			$this->fetchGameNotices($wdId);
			
			if ($finalTheme == 'dark')
			{
				$this->template->assign_vars(array(
				'U_WD_WEBDIPPOINTS' => '(' . $this->points . ' <img src="' . $this->WEBDIPPATH . 'images/icons/points.png" alt="D" />)'.'<link rel="stylesheet" href="'.$this->WEBDIPPATH.('/css/darkMode/global.css').'" type="text/css" />',
				'U_WD_GAMENOTIFYBLOCK' => $this->gameNotifyBlock(),
				'U_WD_NOTICEBLOCK' => $this->noticeBlock() . $this->variantCSS($finalTheme)
				));
			}
			else
			{
				$this->template->assign_vars(array(
				'U_WD_WEBDIPPOINTS' => '(' . $this->points . ' <img src="' . $this->WEBDIPPATH . 'images/icons/points.png" alt="D" />)',
				'U_WD_GAMENOTIFYBLOCK' => $this->gameNotifyBlock(),
				'U_WD_NOTICEBLOCK' => $this->noticeBlock() . $this->variantCSS($finalTheme)
			));
			}			
		}
		else
		{
			$this->template->assign_vars(array(
				'U_WD_WEBDIPPOINTS' => "0",
				'U_WD_GAMENOTIFYBLOCK' => '',
				'U_WD_NOTICEBLOCK' => ''
			));
		}
    }
	
	// Get wD_Misc so we know when we are in panic mode etc
	private function fetchMisc()
	{
		$result = $this->db->sql_query("SELECT name, value FROM wD_Misc");
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->misc[$row['name']] = $row['value'];
		}
		$this->db->sql_freeresult($result);
	}
	
	// Fetches the user details, and will end here if they are banned in webDip
	private function fetchAndCheckUser($wdId)
	{
		$result = $this->db->sql_query("SELECT type, points, notifications FROM wD_Users WHERE Id = " . $wdId);
		while ($row = $this->db->sql_fetchrow($result))
		{
			if( strpos($row['type'],'Banned') !== false ) {
				die("Account banned. Please contact the moderator team via the main website for more information.");
			}
			$this->points = $row['points'];
			$this->notifications = $row['notifications'];
		}
		$this->db->sql_freeresult($result);
	}
	
	private function fetchGameNotices($wdId)
	{
		$result = $this->db->sql_query("SELECT g.id, g.variantID, g.name, g.phase, m.orderStatus, m.countryID, (m.newMessagesFrom+0) as newMessagesFrom, g.processStatus FROM wD_Members m INNER JOIN wD_Games g ON ( m.gameID = g.id ) WHERE m.userID = " . $wdId . " AND ( ( NOT m.orderStatus LIKE '%Ready%' AND NOT m.orderStatus LIKE '%None%' AND g.phase != 'Finished' ) OR NOT ( (m.newMessagesFrom+0) = 0 ) ) ORDER BY g.processStatus ASC, g.processTime ASC" );
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->gameBar[] = $row;
		}
		$this->db->sql_freeresult($result);
	}
	
	private function gameNotifyBlock()
	{
		// This code adapted from lib\html.php libHTML::gameNotifyBlock():
		$gameNotifyBlock = '';
		if ( strpos($this->notifications,'PrivateMessage')!==false )
		{
			$gameNotifyBlock .= '<span class=""><a href="' . $this->WEBDIPPATH . 'index.php?notices=on">'.
				('PM').' <img src="' . $this->WEBDIPPATH . ''.('images/icons/mail.png').'" alt="'.('New private messages').'" title="'.('New private messages!').'" />'.
				'</a></span> ';
		}

		foreach ( $this->gameBar as $notifyGame )
		{
			// Games that are finished should show as 'no orders'
			if ( $notifyGame['phase'] != 'Finished') {
				if( $notifyGame['orderStatus'] == 'None' ) {
					$orderIcon = '';
				} else {
					$orderIcon = 'alert.png';
					if( strpos($notifyGame['orderStatus'],'Saved')!==false ) {
						$orderIcon = 'alert_minor.png';
					}
					if( strpos($notifyGame['orderStatus'],'Completed')!==false ) {
						$orderIcon = 'tick_faded.png';
					}
					if( strpos($notifyGame['orderStatus'],'Ready')!==false ) {
						$orderIcon = '';
					}
				}
			} else {
					$orderIcon = '';
			}

			$gameNotifyBlock .= '<span class="variant'.\Config::$variants[$notifyGame['variantID']].'">'.
				'<a gameID="'.$notifyGame['id'].'" class="country'.$notifyGame['countryID'].'" href="' . $this->WEBDIPPATH . 'board.php?gameID='.$notifyGame['id'].'">'.
				$notifyGame['name'];

			if ( $notifyGame['processStatus'] == 'Paused' )
				$gameNotifyBlock .= '-<img src="' . $this->WEBDIPPATH . ''.'images/icons/pause.png'.'" alt="'.'Paused'.'" title="'.'Game paused'.'" />';

			$gameNotifyBlock .= ' ';

			if( strlen($orderIcon) > 0 ) {
				$gameNotifyBlock .= '<img src="' . $this->WEBDIPPATH . ''.'images/icons/' . $orderIcon . '" />';
			}
				
			if ( $notifyGame['newMessagesFrom'] )
				$gameNotifyBlock .= '<img src="' . $this->WEBDIPPATH . ''.'images/icons/mail.png'.'" alt="'.'New messages'.'" title="'.'New messages!'.'" />';

			$gameNotifyBlock .= '</a></span> ';
		}
		
		if( strlen($gameNotifyBlock) > 0 )
			$gameNotifyBlock = '<style>
		.gamelistings-tabs img {
			padding-bottom: 3px !important;
		}
		</style>
		<div class="content-notice" style="margin-bottom:4px"><div class="gamelistings-tabs" style="padding-bottom:0">'.$gameNotifyBlock.'</div></div>';
			
		return $gameNotifyBlock;
	}
	
	private function noticeBlock()
	{
		if ( $this->misc['Maintenance'] )
		{
			$result = $this->db->sql_query("SELECT message FROM wD_Config WHERE name = 'Maintenance'");
			while ($row = $this->db->sql_fetchrow($result))
			{
				$maintenance = $row['message'];
			}
			$this->db->sql_freeresult($result);
			$this->noticeBar[]= $maintenance;
		}

		if ( $this->misc['Panic'] )
		{
			$result = $this->db->sql_query("SELECT message FROM wD_Config WHERE name = 'Panic'");
			while ($row = $this->db->sql_fetchrow($result))
			{
				$panic = $row['message'];
			}
			$this->db->sql_freeresult($result);
			$this->noticeBar[] = $panic;
		}

		if ( $this->misc['Notice'] )
		{
			$result = $this->db->sql_query("SELECT message FROM wD_Config WHERE name = 'Notice'");
			while ($row = $this->db->sql_fetchrow($result))
			{
				$notice = $row['message'];
			}
			$this->db->sql_freeresult($result);
			$this->noticeBar[] = $notice;
		}

		if ( ( time() - $this->misc['LastProcessTime'] ) > \Config::$downtimeTriggerMinutes*60 )
			$this->noticeBar[] = ("The last process time was over ".\Config::$downtimeTriggerMinutes." minutes ".
				"ago (at ".date(DATE_RFC850 , $this->misc['LastProcessTime'])." UTC+0); the server ".
				"is not processing games until the cause is found and games are given extra time.");
		
		$noticeBlock = '';
		if ( $this->noticeBar )
			$noticeBlock = '<div class="content-notice" style="margin-bottom:5px"><p class="notice" style="border:0 !important">'.
				implode('</p><div class="hr"></div><p class="notice" style="border:0 !important">',$this->noticeBar).
				'</p></div>';
		return $noticeBlock;
	}
}
