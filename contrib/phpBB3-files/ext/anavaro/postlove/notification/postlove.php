<?php
/**
*
* Post Love extension for the phpBB Forum Software package.
* @copyright (c) 2014 Lucifer
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace anavaro\postlove\notification;

/**
*
* @package notifications
*/
class postlove extends \phpbb\notification\type\base
{
	/**
	* Get notification type name
	*
	* @return string
	*/
	public function get_type()
	{
		return 'notification.type.postlove';
	}

	/**
	* Notification option data (for outputting to the user)
	*
	* @var bool|array False if the service should use it's default data
	* 					Array of data (including keys 'id', 'lang', and 'group')
	*/
	public static $notification_option = array(
		'lang'	=> 'NOTIFICATION_TYPE_POST_LOVE',
	);

	/** @var \phpbb\user_loader */
	protected $user_loader;

	/** @var \phpbb\config\config */
	protected $config;

	public function set_config(\phpbb\config\config $config)
	{
		$this->config = $config;
	}

	public function set_user_loader(\phpbb\user_loader $user_loader)
	{
		$this->user_loader = $user_loader;
	}

	/**
	* Is this type available to the current user (defines whether or not it will be shown in the UCP Edit notification options)
	*
	* @return bool True/False whether or not this is available to the user
	*/
	public function is_available()
	{
		return true;
	}

	/**
	 * Get the id of the liker
	 *
	 * @param array $data The data for the like
	 * @return int
	 */
	public static function get_item_id($data)
	{
		return (int) $data['requester_id'];
	}

	/**
	 * Get the id of the parent
	 *
	 * @param array $data The data for the like
	 * @return int
	 */
	public static function get_item_parent_id($data)
	{
		return (int) $data['post_id'];
	}

	/**
	* Find the users who will receive notifications 
	*
	* @param array $data The data for the like
	*
	* @return array
	*/
	public function find_users_for_notification($data, $options = array())
	{
		$options = array_merge(array(
			'ignore_users'			=> array(),
		), $options);
		$users = array(
			$data['user_id']	=> 0,
		);
		$this->user_loader->load_users(array_keys($users));

		return $this->check_user_notification_options(array_keys($users), $options);
	}

	/**
	 * Get the user's avatar
	 */
	public function get_avatar()
	{
		return $this->user_loader->get_avatar($this->get_data('requester_id'), false, true);
	}

	/**
	 * Get the HTML formatted title of this notification
	 *
	 * @return string
	 */
	public function get_title()
	{
		$username = $this->user_loader->get_username($this->get_data('requester_id'), 'no_profile');
		return $this->language->lang('NOTIFICATION_POSTLOVE_ADD', $username);
	}

	/**
	 * Get the HTML formatted reference of the notification
	 *
	 * @return string
	 */
	public function get_reference()
	{
		return censor_text($this->get_data('post_subject'));
	}

	/**
	 * Get email template
	 *
	 * @return string|bool
	 */
	public function get_email_template()
	{
		return false;
	}

	/**
	 * Get email template variables
	 *
	 * @return array
	 */
	public function get_email_template_variables()
	{
		return array();
	}

	/**
	 * Get the url to this item
	 *
	 * @return string URL
	 */
	public function get_url()
	{
		return append_sid($this->phpbb_root_path . 'viewtopic.' . $this->php_ext, "p=". $this->get_data('post_id') . '#p' . $this->get_data('post_id'));
	}

	/**
	* Users needed to query before this notification can be displayed
	*
	* @return array Array of user_ids
	*/
	public function users_to_query()
	{
		return array($this->get_data('requester_id'));
	}

	/**
	* Function for preparing the data for insertion in an SQL query
	* (The service handles insertion)
	*
	* @param array $data The data for the updated rules
	* @param array $pre_create_data Data from pre_create_insert_array()
	*
	* @return array Array of data ready to be inserted into the database
	*/
	public function create_insert_array($data, $pre_create_data = array())
	{
		$this->set_data('requester_id', $data['requester_id']);
		$this->set_data('user_id', $data['user_id']);
		$this->set_data('post_id', $data['post_id']);
		$this->set_data('topic_id', $data['topic_id']);
		$this->set_data('post_subject', $data['post_subject']);

		parent::create_insert_array($data, $pre_create_data);
	}
}
