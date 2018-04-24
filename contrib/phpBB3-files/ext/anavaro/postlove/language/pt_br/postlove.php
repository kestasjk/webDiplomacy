<?php

/**
*
* newspage [Brazilian Portuguese [pt_br]]
* Brazilian Portuguese translation by eunaumtenhoid (c) 2017 [ver 1.2.1] (https://github.com/phpBBTraducoes)
* @package language
* @version $Id$
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'POSTLOVE_USER_LIKES'	=> 'O usuário curtiu',
	'POSTLOVE_USER_LIKED'	=> 'O usuário foi curtido',

	'NOTIFICATION_POSTLOVE_ADD'	=> '%s <b>Curtiu</b> seu post:',
	'NOTIFICATION_TYPE_POST_LOVE'	=> 'Posts Curtidos.',

	// Ver 1.1
	'LIKE_LINE'	=> '%1$s - %2$s <b>Curtiu</b> o post do %3$s “%4$s” no tópico “%5$s”',
	'POSTLOVE_LIST'	=> 'Curtidas',
	'POSTLOVE_LIST_VIEW'	=> 'Mostrar lista com todas as ações de curtir',
));
