<?php
/**
*
* Post Love extension for the phpBB Forum Software package.
*
* @copyright (c) 2014 Lucifer <http://www.anavaro.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace anavaro\postlove\controller;

class lovelist
{
	/* @var \phpbb\user */
	protected $user;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\db\driver\driver_interface */
	protected $db;

	/* @var \phpbb\auth\auth */
	protected $auth;

	/* @var \phpbb\user_loader */
	protected $user_loader;

	/* @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\request\request */
	protected $request;

	/**
	 * Constructor
	 * NOTE: The parameters of this method must match in order and type with
	 * the dependencies defined in the services.yml file for this service.
	 *
	 * @param \phpbb\user $user User object
	 * @param \phpbb\language\language $language
	 * @param \phpbb\controller\helper $helper
	 * @param \phpbb\db\driver\driver_interface $db
	 * @param \phpbb\auth\auth $auth
	 * @param \phpbb\user_loader $user_loader
	 * @param \phpbb\template\template $template
	 * @param \phpbb\pagination $pagination
	 * @param \phpbb\request\request $request
	 * @param $likes_table
	 * @param $root_path
	 */
	public function __construct(\phpbb\user $user, \phpbb\language\language $language, \phpbb\controller\helper $helper, \phpbb\db\driver\driver_interface $db, \phpbb\auth\auth $auth, \phpbb\user_loader $user_loader,
	\phpbb\template\template $template,\phpbb\pagination $pagination, \phpbb\request\request $request,
	$likes_table, $root_path)
	{
		$this->user = $user;
		$this->lang = $language;
		$this->helper = $helper;
		$this->db = $db;
		$this->auth = $auth;
		$this->user_loader = $user_loader;
		$this->template = $template;
		$this->pagination = $pagination;
		$this->request = $request;
		$this->likes_table = $likes_table;
		$this->root_path = $root_path;
	}

	/**
	* Post love list
	*	Route: postlove/{user_id}
	*
	* @param int	$user_id	User ID
	* @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	*/
	public function base ($user_id, $page)
	{
		//$short = $this->request->variable('short', '');
		$short = $this->request->is_ajax();
		if ($short)
		{
			$this->template->assign_vars(array(
				'SHORT' => true,
			));
		}
		$limit = 50;
		$start = ($page - 1) * $limit;

		// Add lang
		$this->lang->add_lang(array('postlove'), 'anavaro/postlove');
		// Let's get allowed forums
		// Get the allowed forums
		$forum_ary = array();
		$forum_read_ary = $this->auth->acl_getf('f_read');

		foreach ($forum_read_ary as $forum_id => $allowed)
		{
			if ($allowed['f_read'])
			{
				$forum_ary[] = (int) $forum_id;
			}
		}
		$forum_ids = array_unique($forum_ary);

		// No forums with f_read
		if (!sizeof($forum_ids))
		{
			return -1;
		}

		$sql_array = array(
			'SELECT'	=> 'COUNT(*) as count',
			'FROM'	=> array(
				POSTS_TABLE	=> 'p',
				TOPICS_TABLE	=> 't',
			),
			'LEFT_JOIN'	=> array(
				array(
					'FROM'	=> array($this->likes_table	=> 'pl'),
					'ON'	=> 'pl.post_id = p.post_id'
				),
			),
			'WHERE'	=> 'p.topic_id = t.topic_id AND (p.poster_id = ' . (int) $user_id . ' OR  pl.user_id = ' . (int) $user_id . ') AND pl.user_id > 0 AND ' . $this->db->sql_in_set('p.forum_id', $forum_ids),
			'ORDER_BY'	=> 'pl.timestamp DESC',
			'GROUP_BY'	=> 'pl.timestamp, pl.user_id, p.post_id, t.topic_title'
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);
		$counter = 0;
		while ($row = $this->db->sql_fetchrow($result))
		{
			$counter = $counter + $row['count'];
		}
		$this->db->sql_freeresult($result);
		if ($counter > 0)
		{
			$sql_array['SELECT'] = 'pl.timestamp as timestamp, pl.user_id as liker_id, p.post_id as post_id, p.topic_id as topic_id, p.poster_id as poster, p.post_subject as post_subject, t.topic_title as topic_title';
			$sql = $this->db->sql_build_query('SELECT', $sql_array);
			$result = $this->db->sql_query_limit($sql, $limit, $start);
			$users = $output = $raw_output = array();
			while ($row = $this->db->sql_fetchrow($result))
			{
				if ($row['liker_id'] != $user_id)
				{
					$users[] = $row['liker_id'];
				}
				if ($row['poster'] != $user_id)
				{
					$users[] = $row['poster'];
				}
				$raw_output[] = $row;
			}
			$users[] = (int) $user_id;
			$users = array_unique($users);
			$this->db->sql_freeresult($result);
			$this->user_loader->load_users($users);
			foreach ($raw_output as $row)
			{
				$post_link = '<a href="' . $this->root_path .($short == 1 ? '' : ($page > 1 ? '../../../' : '../')) .'viewtopic.php?p=' . $row['post_id'] . '#'. $row['post_id'] .'" target="_blank" >' . $row['post_subject'] . '</a>';
				$topic_link = '<a href="' . $this->root_path .($short == 1 ? '' : ($page > 1 ? '../../../' : '../')) .'viewtopic.php?t=' . $row['topic_id'] . '" target="_blank" class="topictitle">' . $row['topic_title'] . '</a>';
				$this->template->assign_block_vars('lovelist', array(
					'LINE' => $this->lang->lang('LIKE_LINE', $this->user->format_date($row['timestamp']), $this->user_loader->get_username($row['liker_id'], 'full'), $this->user_loader->get_username($row['poster'], 'full'), $post_link, $topic_link),
				));
			}

			$this->pagination->generate_template_pagination(array(
					'routes' => array(
						'postlove_list',
						'postlove_list_page',
					),
					'params' => array(
						'user_id' => $user_id,
					),
				), 'pagination', 'page', $counter, $limit, $start);
		}
		$page_title = 'Post Love';
		return $this->helper->render('postlove_base.html', $page_title);
	}
}
