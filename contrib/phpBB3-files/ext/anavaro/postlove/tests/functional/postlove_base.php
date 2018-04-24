<?php
/**
*
* Birthday Control
*
* @copyright (c) 2014 Stanislav Atanasov
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace anavaro\postlove\tests\functional;

/**
* @group functional
*/
class postlove_base extends \phpbb_functional_test_case
{
	static protected function setup_extensions()
	{
		return array('anavaro/postlove');
	}

	public function setUp()
	{
		parent::setUp();
	}

	/**
	* Allow birthday (just to be sure) 
	*/
	public function force_allow_postlove()
	{
		$this->get_db();

		$sql = "UPDATE phpbb_config
			SET config_value = 1
			WHERE config_name = 'postlove_use_css'";

		$this->db->sql_query($sql);

		$this->purge_cache();
	}

	/**
	* Require birthday (it's not required on install) 
	*/
	public function show_likes()
	{
		$this->get_db();

		$sql = "UPDATE phpbb_config
			SET config_value = 1
			WHERE config_name = 'postlove_show_likes'";

		$this->db->sql_query($sql);

		$this->purge_cache();
	}
	/**
	* Set age (default is 0)
	*/
	public function show_liked()
	{
		$this->get_db();

		$sql = "UPDATE phpbb_config
			SET config_value = 1
			WHERE config_name = 'postlove_show_liked'";

		$this->db->sql_query($sql);

		$this->purge_cache();
	}
	
	public function get_topic_id($topic_title)
	{
		$sql = 'SELECT topic_id
				FROM ' . TOPICS_TABLE . '
				WHERE topic_title = \'' . $topic_title . '\'';
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		return $row['topic_id'];
	}
}