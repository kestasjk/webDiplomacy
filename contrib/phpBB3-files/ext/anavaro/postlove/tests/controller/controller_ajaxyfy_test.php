<?php
/**
*
* Post Love extension for the phpBB Forum Software package.
*
* @copyright (c) 2015 Lucifer <https://www.anavaro.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace anavaro\postlove\tests\controller;

/**
* @group controller
*/

require_once dirname(__FILE__) . '/../../../../../includes/functions.php';
require_once dirname(__FILE__) . '/../../../../../includes/functions_content.php';

class controller_ajaxify_test extends \phpbb_database_test_case
{

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
		// Setup DB
		$this->db = $this->new_dbal();

		//Setup Config
		$this->config = new \phpbb\config\config(array());

		// Setup User
		$this->user = $this->getMock('\phpbb\user', array(), array(
			new \phpbb\language\language(new \phpbb\language\language_file_loader($phpbb_root_path, $phpEx)),
			'\phpbb\datetime'));

		// Setup notifyhelper (I should drop that in future versions)
		$this->notifyhelper = $this->getMockBuilder('\anavaro\postlove\controller\notifyhelper')->disableOriginalConstructor()
			->getMock();
	}

	/**
	* Create our controller
	*/
	protected function get_controller($user_id, $is_registered, $postlove_author_like)
	{
		$this->user->data['user_id'] = $user_id;
		$this->user->data['user_type'] = $is_registered;
		$this->config['postlove_author_like'] = $postlove_author_like;

		return new \anavaro\postlove\controller\ajaxify(
			$this->config,
			$this->db,
			$this->user,
			$this->notifyhelper,
			'phpbb_posts_likes'
		);
	}

	/**
	* Test data for the test_ajaxify_controller test
	*
	* @return array Test data
	*/
	public function controller_ajaxify_data()
	{
		return array(
			'anon'	=> array(
				1, // Anonimous
				1, // bot / anon
				true, // Allow author to like
				1, // post ID
				'{"error":1}'
			),
			'inactive'	=> array(
				1, // Anonimous
				2, // inactive
				true, // Allow author to like
				1, // post ID
				'{"error":1}'
			),
			'user_cant_like'	=> array(
				1, // Anonimous
				0, // Active
				false, // Allow author to like
				4, // post ID
				'{"error":1}'
			),
			'no_such_post'	=> array(
				1, // Anonimous
				0, // Active
				true, // Allow author to like
				5, // post ID
				'{"error":1}'
			),
			'user_can_like'	=> array(
				1, // Anonimous
				0, // Active
				true, // Allow author to like
				4, // post ID
				'{"toggle_action":"add","toggle_post":4}'
			),
			'like'	=> array(
				2, // Anonimous
				0, // Active
				true, // Allow author to like
				3, // post ID
				'{"toggle_action":"add","toggle_post":3}'
			),
			'unlike'	=> array(
				2, // Anonimous
				0, // Active
				true, // Allow author to like
				1, // post ID
				'{"toggle_action":"remove","toggle_post":1}'
			),
		);
	}

	/**
	 * Test the controller
	 *
	 * @dataProvider controller_ajaxify_data
	 */
	public function test_ajaxify_controller($user_id, $user_type, $postlove_author_like, $post_id, $expected)
	{
		$controller = $this->get_controller($user_id, $user_type, $postlove_author_like);
		$response = $controller->base('toggle', $post_id);
		$this->assertInstanceOf('\Symfony\Component\HttpFoundation\JsonResponse', $response);
		$this->assertEquals(200, $response->getStatusCode());
	//	var_dump($response->getContent());
		$this->assertContains($expected, $response->getContent());
	}
}