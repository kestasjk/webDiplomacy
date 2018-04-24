<?php
/**
*
* @package acp
* @copyright (c) 2015 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace anavaro\postlove\acp;

/**
* @package module_install
*/

class acp_postlove_info
{
	function module()
	{
		return array(
			'filename'	=> 'anavaro\postlove\acp\acp_postlove_module',
			'title'		=> 'ACP_POSTLOVE', // define in the lang/xx/acp/common.php language file
			'version'	=> '1.0.0',
			'modes'		=> array(
				'main'		=> array(
					'title'		=> 'ACP_POSTLOVE',
					'auth' 		=> 'ext_anavaro/postlove && acl_a_user',
					'cat'		=> array('ACP_POSTLOVE_GRP')
				),
			),
		);
	}
}
