<?php
/**
*
* Post Love extension for the phpBB Forum Software package.
*
* @copyright (c) 2014 Lucifer <http://www.anavaro.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace anavaro\postlove\migrations;

/**
* Primary migration
*/

class release_1_0_0 extends \phpbb\db\migration\migration
{
	public function update_data()
	{
		return array(
			array('config.add', array('postlove_version', '1.0.0')),
		);
	}

	//lets create the needed table
	public function update_schema()
	{
		return array(
			'add_tables'    => array(
				$this->table_prefix . 'posts_likes'		=> array(
					'COLUMNS'		=> array(
						'post_id'		=> array('UINT:8', 0),
						'user_id'		=> array('UINT:8', 0),
						'type'		=> array('VCHAR:16', 'post'),
						'timestamp'		=> array('VCHAR:32', 0)
					),
					'PRIMARY_KEY'    => 'post_id, user_id',
				)
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_tables'		=> array(
				$this->table_prefix . 'posts_likes'
			),
		);
	}
}
