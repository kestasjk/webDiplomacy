<?php
/**
*
* @package Zebra Enhance Extension
* @copyright (c) 2014 Lucifer
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace anavaro\postlove\controller;

use Symfony\Component\DependencyInjection\Container;

/**
* Admin controller
*/
class notifyhelper
{
	/**
	* Constructor
	*
	* @param \phpbb\config\config $config                      Config object
	* @param \phpbb\db\driver\driver $db                       Database object
	* @param \phpbb\request\request $request                   Request object
	* @param \phpbb\template\template $template                Template object
	* @param \phpbb\user $user                                 User object
	* @param Container $phpbb_container
	* @param string $root_path                                 phpBB root path
	* @param string $php_ext                                   phpEx
	* @access public
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, Container $phpbb_container, $root_path, $php_ext)
	{
		$this->config = $config;
		$this->db = $db;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->phpbb_container = $phpbb_container;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	/**
	* Main notification function
	* @param type			Type of notification (add/confirm)
	* @param post_id		Post ID
	* @param poster_user	User to notify
	* @param liker_user	User that trigered the action
	*/
	public function notify($type, $topic_id, $post_id, $post_subject, $poster_user, $liker_user)
	{
		$notification_data = array(
			'topic_id'	=> (int) $topic_id,
			'post_id'	=> (int) $post_id,
			'post_subject'	=>	$post_subject,
			'user_id'	=> (int) $poster_user,
			'requester_id'	=> (int) $liker_user,
		);

		//$this->test($notification_data);
		$phpbb_notifications = $this->phpbb_container->get('notification_manager');
		if ($notification_data['requester_id'] != $notification_data['user_id'])
		{
			switch ($type)
			{
				case 'add':
					$phpbb_notifications->add_notifications('notification.type.postlove', $notification_data);
				break;
				case 'remove':
					$notifications = $phpbb_notifications->get_item_type_class('notification.type.postlove');
					$phpbb_notifications->delete_notifications('notification.type.postlove', $notifications->get_item_id($notification_data));
				break;
			}
		}
	}
}
