<?php

/**
*
* newspage [Bulgarian]
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
	'POSTLOVE_USER_LIKES'	=> 'Потребителя е харесал',
	'POSTLOVE_USER_LIKED'	=> 'Потребителя е харесан',

	'NOTIFICATION_POSTLOVE_ADD'	=> '%s <b>хареса</b> вашето мнение:',
	'NOTIFICATION_TYPE_POST_LOVE'	=> 'Харесани постове',

	// Ver 1.1
	'LIKE_LINE'	=> '%1$s - %2$s <b>хареса</b> мнението на %3$s "%4$s" в тема "%5$s"',
	'POSTLOVE_LIST'	=> 'Харесвания',
	'POSTLOVE_LIST_VIEW'	=> 'Покажи списък с харесванията',
));
