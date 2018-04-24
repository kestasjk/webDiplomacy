<?php

/**
*
* Postlove [German]
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
	'POSTLOVE_USER_LIKES'	=> 'User gefallen',
	'POSTLOVE_USER_LIKED'	=> 'User gefällt',

	'NOTIFICATION_POSTLOVE_ADD'	=> '%1$s <b>gefällt</b> dein Beitrag "%2$s"',
	'NOTIFICATION_TYPE_POST_LOVE'	=> 'Beitrag gefällt.',

	// Ver 1.1
	'LIKE_LINE'	=> '%1$s - %2$s <b>gefällt</b> %3$s\'s Beitrag "%4$s" im Thema "%5$s"',
	'POSTLOVE_LIST'	=> 'Gefällt',
	'POSTLOVE_LIST_VIEW'	=> 'Zeige Liste mit allen Gefällt-Angaben',
));
