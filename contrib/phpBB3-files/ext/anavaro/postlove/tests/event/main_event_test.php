<?php
/**
*
* Post Love extension for the phpBB Forum Software package.
*
* @copyright (c) 2015 Lucifer <https://www.anavaro.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace anavaro\postlove\tests\event;

/**
* @group event
*/

class main_event extends \phpbb_database_test_case
{
	protected $listener;

	/**
	* Define the extensions to be tested
	*
	* @return array vendor/name of extension(s) to test
	*/
	static protected function setup_extensions()
	{
		return array('anavaro/postlove');
	}

	protected $db;

	/**
	* Get data set fixtures
	*/
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/users.xml');
	}

	/**
	* Setup test environment
	*/
	public function setUp()
	{
		parent::setUp();
		// Setup Auth
		$this->auth = $this->getMock('\phpbb\auth\auth');

		//Setup Config
		$this->config = new \phpbb\config\config(array());

		// Setup DB
		$this->db = $this->new_dbal();

		// Setup template
		$this->template = $this->getMockBuilder('\phpbb\template\template')
			->getMock();

		// Setup User
		$this->user = $this->getMock('\phpbb\user', array(), array(
			new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx)),
			'\phpbb\datetime',
			));

		// Setup Controller
		$this->controller_helper = $this->getMockBuilder('\phpbb\controller\helper')
			->disableOriginalConstructor()
			->getMock();
	}

	/**
	* Create our controller
	*/
	protected function set_listener()
	{
		$this->listener = new \anavaro\postlove\event\main_listener(
			$this->auth,
			$this->config,
			$this->db,
			$this->template,
			$this->user,
			$this->controller_helper,
			'phpbb_posts_likes'
		);
	}

	/**
	* Test the event listener is subscribing events
	*/
	public function test_getSubscribedEvents()
	{
		$this->assertEquals(array(
			'core.viewtopic_modify_post_row',
			'core.user_setup',
			'core.memberlist_view_profile',
			'core.delete_posts_after',
			'core.delete_user_after',
		), array_keys(\anavaro\postlove\event\main_listener::getSubscribedEvents()));
	}

	/**
	* data provider for test_modify_post_row
	*/
	public function data_modify_post_row()
	{
		return array(
			'base'	=> array(
				1, //user_id
				1, // post_id
				5, // poster_id
				0, //postlove_show_likes
				0, //postlove_show_liked
				0, //postlove_author_like
				array(
					'POST_LIKERS'	=> 'Test user, Test user 2',
					'POST_LIKERS_COUNT'	=> 2,
					'POST_LIKE_CLASS'	=> 'liked',
					'POST_LIKE_URL'		=> NULL
				),
			),
			'user'	=> array(
				3, //user_id
				1, // post_id
				5, // poster_id
				0, //postlove_show_likes
				0, //postlove_show_liked
				0, //postlove_author_like
				array(
					'POST_LIKERS'	=> 'Test user, Test user 2',
					'POST_LIKERS_COUNT'	=> 2,
					'POST_LIKE_CLASS'	=> 'like',
					'POST_LIKE_URL'		=> NULL
				),
			),
			'post'	=> array(
				1, //user_id
				4, // post_id
				5, // poster_id
				0, //postlove_show_likes
				0, //postlove_show_liked
				0, //postlove_author_like
				array(
					'POST_LIKERS_COUNT'	=> 0,
					'POST_LIKE_CLASS'	=> 'like',
					'POST_LIKE_URL'		=> NULL
				),
			),
			'post_author_not_like'	=> array(
				1, //user_id
				4, // post_id
				1, // poster_id
				0, //postlove_show_likes
				0, //postlove_show_liked
				0, //postlove_author_like
				array(
					'POST_LIKERS_COUNT'	=> 0,
					'POST_LIKE_CLASS'	=> 'like',
					'POST_LIKE_URL'		=> NULL,
					'DISABLE'	=> 1
				),
			),
			'post_author_like'	=> array(
				1, //user_id
				4, // post_id
				1, // poster_id
				0, //postlove_show_likes
				0, //postlove_show_liked
				1, //postlove_author_like
				array(
					'POST_LIKERS_COUNT'	=> 0,
					'POST_LIKE_CLASS'	=> 'like',
					'POST_LIKE_URL'		=> NULL,
				),
			),
			'show_likes'	=> array(
				1, //user_id
				1, // post_id
				1, // poster_id
				1, //postlove_show_likes
				0, //postlove_show_liked
				1, //postlove_author_like
				array(
					'POST_LIKERS'	=> 'Test user, Test user 2',
					'POST_LIKERS_COUNT'	=> 2,
					'POST_LIKE_CLASS'	=> 'liked',
					'POST_LIKE_URL'		=> NULL,
					'USER_LIKES'	=> 3,
				),
			),
			'show_liked'	=> array(
				1, //user_id
				1, // post_id
				1, // poster_id
				0, //postlove_show_likes
				1, //postlove_show_liked
				1, //postlove_author_like
				array(
					'POST_LIKERS'	=> 'Test user, Test user 2',
					'POST_LIKERS_COUNT'	=> 2,
					'POST_LIKE_CLASS'	=> 'liked',
					'POST_LIKE_URL'		=> NULL,
					'USER_LIKED'	=> 6,
				),
			),
			'show_likes_liked'	=> array(
				1, //user_id
				1, // post_id
				1, // poster_id
				1, //postlove_show_likes
				1, //postlove_show_liked
				1, //postlove_author_like
				array(
					'POST_LIKERS'	=> 'Test user, Test user 2',
					'POST_LIKERS_COUNT'	=> 2,
					'POST_LIKE_CLASS'	=> 'liked',
					'POST_LIKE_URL'		=> NULL,
					'USER_LIKES'	=> 3,
					'USER_LIKED'	=> 6,
				),
			),
		);
	}
	/**
	* Let's test modify_post_row
	* @dataProvider data_modify_post_row
	*/
	public function test_modify_post_row($user_id, $post_id, $poster_id, $postlove_show_likes, $postlove_show_liked, $postlove_author_like, $expected)
	{
		$this->config['postlove_show_likes'] = $postlove_show_likes;
		$this->config['postlove_show_liked'] = $postlove_show_liked;
		$this->config['postlove_author_like'] = $postlove_author_like;
		$this->user->data['user_id'] = $user_id;
		$row = array(
			'post_id' => $post_id,
			'user_id' => $poster_id,
		);
		$post_row = array();
		$event_data = array('post_row', 'row', 'poster_id');
		$event = new \phpbb\event\data(compact($event_data));
		$this->set_listener();
		$dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
		$dispatcher->addListener('core.viewtopic_modify_post_row', array($this->listener, 'modify_post_row'));
		$dispatcher->dispatch('core.viewtopic_modify_post_row', $event);
		$output = $event->get_data_filtered($event_data);
		//$this->assertEquals($output['post_row'], $expacted);
		//var_dump($output['post_row']);
		$this->assertEquals(count($output['post_row']), count($expected));
		foreach($output['post_row'] as $ID => $VAR)
		{
			//var_dump($ID);
			$this->assertEquals($VAR, $expected[$ID]);
		}
	}
}