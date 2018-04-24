<?php
/**
*
* newspage [Czech]
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
	'POSTLOVE_USER_LIKES'	=> 'Uživateli se líbí',
	'POSTLOVE_USER_LIKED'	=> 'Uživatel se líbí',
	'NOTIFICATION_POSTLOVE_ADD'	=> '%s <b>se líbí</b> váš příspěvek:',
	'NOTIFICATION_TYPE_POST_LOVE'	=> 'Oblíbené příspěvky',

	// Ver 1.1
	'LIKE_LINE'	=> '%1$s – %2$s <b>se líbí</b> příspěvek „%4$s“ uživatele %3$s v tématu „%5$s“',
	'POSTLOVE_LIST'	=> 'Hodnocení',
	'POSTLOVE_LIST_VIEW'	=> 'Zobrazit seznam se všemi událostmi',
));
