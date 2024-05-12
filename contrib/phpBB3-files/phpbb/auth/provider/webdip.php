<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\auth\provider;

define("IN_CODE", true);

// webDiplomacy authentication, registered in config/default/container/services_auth.yml

//ALTER TABLE `phpbb_users` ADD `webdip_user_id` INT(0) UNSIGNED NULL AFTER `user_reminded_time`;
//ALTER TABLE `phpbb_users` ADD INDEX(`webdip_user_id`);

class webdip extends \phpbb\auth\provider\base
{
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, \phpbb\request\request $request, \phpbb\user $user, $phpbb_root_path, $php_ext)
	{
		$this->db = $db;
		$this->config = $config;
		$this->request = $request;
		$this->user = $user;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
		
		require_once($this->phpbb_root_path . '../../config.php');
		require_once($this->phpbb_root_path . '../../lib/auth.php');
	}

	// Ignore usernames and passwords but allow a successful login with credentials to allow for "reauthentication" for the admin CP
	public function login($username, $password)
	{
		$user_data = $this->autologin();
		if( $user_data ) {
			return array(
					'status'	=> LOGIN_SUCCESS,
					'error_msg'	=> 'LOGIN_SUCCESS',
					'user_row'	=> $user_data,
			);
		} else {
			return array(
					'status'	=> LOGIN_ERROR_USERNAME,
					'error_msg'	=> 'You must log on via webDiplomacy',
					'user_row'	=> array('user_id' => ANONYMOUS),
			);
		}
		
	}
	
	// webDiplomacy user ID -> webDip user record
	private function getWebDipDetails($userID) {
 
		$sql = 'SELECT *
				FROM wD_Users
				WHERE id = '.$userID;
		$result = $this->db->sql_query($sql);
		$data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		return $data;
	}
	// Autologin based on webDip session, returning phpBB user data
	public function autologin() {
		$userID = $this->getValidatedWebDipUserID();
		
		if( $userID == -1 ) return false;
		
		$sql = 'SELECT *
				FROM ' . USERS_TABLE . '
				WHERE webdip_user_id = '.$userID;
		$result = $this->db->sql_query($sql);
		$data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);
		
		if ( !$data ) {
			// No record found for this valid webDip ID; create the user in phpBB
			
			$wD_Data = $this->getWebDipDetails($userID);
			if( strlen($wD_Data['username']) > 10 && substr($wD_Data['username'],0,8) == "diplonow" )
			{
				die("The forum can't be accessed as a quick-game user account. Please log off and register a regular user account to access the forum.");
			}
			
			$user_row = array(
					'username'=>$wD_Data['username'], 
					'group_id'=>2, 
					'user_email'=>$wD_Data['email'], 
					'user_type'=>0, // Normal user. 3 = founder
					'webdip_user_id'=>$userID
			);
			
			if (!function_exists('user_add'))
			{
				include($this->phpbb_root_path . 'includes/functions_user.' . $this->php_ext);
			}
			$newID = user_add($user_row);
			
			return $this->autologin();
		}
		
		return $data;
	}
	
	// Look for a webDip session key and get the webDip user ID, or -1 if none found
	private function getWebDipUserID($key=false) {
		
		if( !$key ) 
			$key = $this->request->raw_variable('wD-Key', 'N/A', \phpbb\request\request_interface::COOKIE);
		
		if( $key == 'N/A' ) return -1;

		list($userID) = explode('_', $key);
		
		return intval($userID);
	}
	
	private function getValidatedWebDipUserID($key=false) {
		$key = $this->request->raw_variable('wD-Key', 'N/A', \phpbb\request\request_interface::COOKIE);
		if( $key == 'N/A' ) return -1;
		
		$userID = $this->getWebDipUserID($key);

		$sql = 'SELECT LOWER(HEX(password)) as password
				FROM wD_Users
				WHERE id = '.$userID;
		$result = $this->db->sql_query($sql);
		$data = $this->db->sql_fetchrow($result);
		
		if ( !$data ) {
			return -1;
		}
		$this->db->sql_freeresult($result);
		
		$validKey = \libAuth::generateKey($userID, $data['password']);
		
		if( $validKey == $key ) return $userID;
		else return -1;
	}
	
	// Check if logged on as the right user based on the webDip session, if not reauth
	public function validate_session($data) {
		global $request;

		$userId = $this->getValidatedWebDipUserID();
		
		$webDipUserId = $this->user->data['webdip_user_id'];

		if( $userId != $webDipUserId || $webDipUserId < 10 )
		{
				$scriptName = $request->server('PHP_SELF', '');
				
				if( strstr($scriptName, 'index.php') === false
						&& strstr($scriptName, 'viewforum.php') === false
						&& strstr($scriptName, 'viewtopic.php') === false )
						die("To limit server resources unauthenticated users can only access pages for viewing posts.".$userId);

				return false;
		}

		return true;

	}
}
