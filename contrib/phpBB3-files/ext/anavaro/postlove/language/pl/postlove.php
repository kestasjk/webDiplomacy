<?php

/**
*
* newspage [Polish]
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
	'POSTLOVE_USER_LIKES'	=> 'Posty, które użytkownik polubił',
	'POSTLOVE_USER_LIKED'	=> 'Posty użytkownika, które polubiono',

	'NOTIFICATION_POSTLOVE_ADD'	=> '%s <b>polubił(a)</b> Twój post:',
	'NOTIFICATION_TYPE_POST_LOVE'	=> 'Polubiono post',

	// Ver 1.1
	'LIKE_LINE'	=> '%1$s - %2$s <b>polubił(a)</b> post użytkownika %3$s: "%4$s" w temacie "%5$s"',
	'POSTLOVE_LIST'	=> 'Polubienia',
	'POSTLOVE_LIST_VIEW'	=> 'Pokaż listę wszystkich polubień',
));
