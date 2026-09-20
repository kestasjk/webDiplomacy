<?php

/**
*
* Post Love extension for the phpBB Forum Software package.
*
* @copyright (c) 2014 Lucifer <http://www.anavaro.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace anavaro\postlove\event;

/**
* Event listener
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class main_listener implements EventSubscriberInterface
{
	static public function getSubscribedEvents()
	{
		return array(
			'core.viewtopic_modify_post_row'	=>	'modify_post_row',
			'core.user_setup'		=> 'load_language_on_setup',
			'core.memberlist_view_profile'	       => 'user_profile_likes',
			'core.delete_posts_after'			=> 'clean_posts_after',
			'core.delete_user_after'			=> 'clean_users_after',
		);
	}

	/**
	* Constructor
	* NOTE: The parameters of this method must match in order and type with
	* the dependencies defined in the services.yml file for this service.
	*
	* @param \phpbb\auth		$auth		Auth object
	* @param \phpbb\cache\service	$cache		Cache object
	* @param \phpbb\config	$config		Config object
	* @param \phpbb\db\driver	$db		Database object
	* @param \phpbb\request	$request	Request object
	* @param \phpbb\template	$template	Template object
	* @param \phpbb\user		$user		User object
	* @param \phpbb\content_visibility		$content_visibility	Content visibility object
	* @param \phpbb\controller\helper		$helper				Controller helper object
	* @param string			$root_path	phpBB root path
	* @param string			$php_ext	phpEx
	*/
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\template\template $template, \phpbb\user $user,
	\phpbb\controller\helper $helper,
	$loves_table)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->db = $db;
		$this->template = $template;
		$this->user = $user;
		$this->helper = $helper;
		$this->loves_table = $loves_table;
	}

	public function load_language_on_setup($event)
	{
		$this->user->add_lang_ext('anavaro/postlove', 'postlove');
	}

	public function modify_post_row($event)
	{
		if ($this->user->data['user_type'] == 1 || $this->user->data['user_type'] == 2)
		{
			$this->template->assign_var('DISABLE', '1');
			return;
		}

		//var_dump($event['row']['post_id']);
		$image = $likes = '';
		$isliked = false;
		$likers = array();
		$sql_array = array(
			'SELECT'	=>	'pl.user_id as user_id, u.username as username',
			'FROM'	=> array(
				USERS_TABLE	=> 'u',
				$this->loves_table	=> 'pl'
			),
			'WHERE'	=> 'u.user_id = pl.user_id AND post_id = ' . $event['row']['post_id'],
			'ORDER_BY'	=> 'pl.timestamp ASC',
		);

		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$likers[$row['user_id']] = $row['username'];
			if ($row['user_id'] == $this->user->data['user_id'])
			{
				$isliked = true;
			}
		}
		$this->db->sql_freeresult($result);
		if (!empty($likers))
		{
			$post_row = $event['post_row'];
			//let's take the list of peoples that liked this post
			$post_likers = implode(', ', $likers);
			$post_row['POST_LIKERS'] = $post_likers;

			//let's get the number
			$post_likers_number = count($likers);
			$post_row['POST_LIKERS_COUNT'] = $post_likers_number;

			//now the image
			$post_like_class = ($isliked ? 'liked' : 'like');
			$post_row['POST_LIKE_CLASS'] = $post_like_class;
			$post_row['POST_LIKE_URL'] = $this->helper->route('postlove_control', array('action' => 'toggle', 'post' =>$event['row']['post_id']));

			$event['post_row'] = $post_row;
		}
		else
		{
			$post_row = $event['post_row'];
			$post_row['POST_LIKERS_COUNT'] = '0';
			$post_row['POST_LIKE_CLASS'] = 'like';
			$post_row['POST_LIKE_URL'] = $this->helper->route('postlove_control', array('action' => 'toggle', 'post' =>$event['row']['post_id']));
			$event['post_row'] = $post_row;
		}

		$this->template->assign_var('SHOW_USER_LIKES', $this->config['postlove_show_likes']);
		$this->template->assign_var('SHOW_USER_LIKED', $this->config['postlove_show_liked']);
		$this->template->assign_var('IS_POSTROW', '1');
		if (!$this->config['postlove_author_like'] && $event['poster_id'] == $this->user->data['user_id'])
		{
			$post_row = $event['post_row'];
			$post_row['DISABLE'] = 1;
			$event['post_row'] = $post_row;
		}

		//so should we display more info?
		// The two totals below come off the poster's user row, where ajaxify::base keeps them as likes
		// are given and taken back, and recount_like_counts() puts them right after a post or user is
		// permanently deleted. They used to be counted here instead, for every post of every topic page:
		// likes given scanned the likes table, and likes received joined it against the whole posts
		// table. viewtopic selects the poster's user row into $event['row'], so both are already loaded.
		if ($this->config['postlove_show_likes'])
		{
			$post_row = $event['post_row'];
			$post_row['USER_LIKES'] = isset($event['row']['webdip_like_given_count']) ? (int) $event['row']['webdip_like_given_count'] : 0;
			$event['post_row'] = $post_row;
		}
		if ($this->config['postlove_show_liked'])
		{
			$post_row = $event['post_row'];
			$post_row['USER_LIKED'] = isset($event['row']['webdip_like_count']) ? (int) $event['row']['webdip_like_count'] : 0;
			$event['post_row'] = $post_row;
		}
	}

	public function user_profile_likes($event)
	{
		$this->template->assign_var('POSTLOVE_STATS', $this->helper->route('postlove_list', array('user_id' => $event['member']['user_id'])));
	}

	/**
	* Delete post loves on post perm delete
	* No need to fill up the database, right?
	*/
	public function clean_posts_after($event)
	{
		$sql = 'DELETE FROM ' . $this->loves_table . ' WHERE ' . $this->db->sql_in_set('post_id', $event['post_ids']);
		$this->db->sql_query($sql);

		$this->recount_like_counts();
	}

	/**
	* Delete post loves on users perm delete
	* No need to fill up the database, right?
	*/
	public function clean_users_after($event)
	{
		$sql = 'DELETE FROM ' . $this->loves_table . ' WHERE ' . $this->db->sql_in_set('user_id', $event['user_ids']);
		$this->db->sql_query($sql);

		$this->recount_like_counts();
	}

	/**
	* Bring the cached like counts on the user rows back in line with the likes table.
	*
	* ajaxify::base keeps them up to date one like at a time, but permanently deleting a post or a
	* user takes likes out from under that bookkeeping, so recount after those. Both are rare
	* moderator actions, and the WHERE means only the rows whose count really moved are written.
	*
	* The multi-table UPDATE is MySQL/MariaDB only, as is webDiplomacy; this copy of the extension is
	* webDiplomacy's own (see README.md).
	*/
	private function recount_like_counts()
	{
		$sql = 'UPDATE ' . USERS_TABLE . ' u
			LEFT JOIN (
				SELECT p.poster_id, COUNT(*) AS likes
				FROM ' . POSTS_TABLE . ' p
				INNER JOIN ' . $this->loves_table . ' l ON l.post_id = p.post_id
				GROUP BY p.poster_id
			) x ON x.poster_id = u.user_id
			SET u.webdip_like_count = COALESCE(x.likes, 0)
			WHERE u.webdip_like_count <> COALESCE(x.likes, 0)';
		$this->db->sql_query($sql);

		$sql = 'UPDATE ' . USERS_TABLE . ' u
			LEFT JOIN (
				SELECT l.user_id, COUNT(*) AS likes
				FROM ' . $this->loves_table . ' l
				GROUP BY l.user_id
			) x ON x.user_id = u.user_id
			SET u.webdip_like_given_count = COALESCE(x.likes, 0)
			WHERE u.webdip_like_given_count <> COALESCE(x.likes, 0)';
		$this->db->sql_query($sql);
	}
}
