<?php

/**
*
* newspage [English]
*
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
	'POSTLOVE_USER_LIKES'	=> 'User\'s +1s to others',
	'POSTLOVE_USER_LIKED'	=> 'User\'s +1s from others',

	'NOTIFICATION_POSTLOVE_ADD'	=> '%s <b>+1\'d</b> your post:',
	'NOTIFICATION_TYPE_POST_LOVE'	=> '+1\'d posts.',

	// Ver 1.1
	'LIKE_LINE'	=> '%1$s - %2$s <b>+1\'d</b> %3$s’s post “%4$s” in topic “%5$s”',
	'POSTLOVE_LIST'	=> '+1s',
	'POSTLOVE_LIST_VIEW'	=> 'Show list with all +1 actions',
));
