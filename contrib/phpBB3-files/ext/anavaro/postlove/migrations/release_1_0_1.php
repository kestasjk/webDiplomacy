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

class release_1_0_1 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\anavaro\postlove\migrations\release_1_0_0',
		);
	}

	public function update_data()
	{
		return array(
			array('config.add', array('postlove_use_css', '1')),
			array('config.add', array('postlove_show_likes', '0')),
			array('config.add', array('postlove_show_liked', '0')),
		);
	}
}
