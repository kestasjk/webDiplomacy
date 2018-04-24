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

class release_1_1_0 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array(
			'\anavaro\postlove\migrations\release_1_0_1',
		);
	}

	public function update_data()
	{
		return array(
			array('config.add', array('postlove_installed_theme', 'default')),
			array('config.add', array('postlove_author_like', 1)),
			array('config.remove', array('postlove_use_css')),
			array('module.add', array(
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_POSTLOVE_GRP'
			)),
			array('module.add', array(
				'acp',
				'ACP_POSTLOVE_GRP',
				array(
					'module_basename'	=> '\anavaro\postlove\acp\acp_postlove_module',
					'module_mode'		=> array('main'),
					'module_auth'        => 'ext_anavaro/postlove && acl_a_user',
				)
			)),
		);
	}
}
