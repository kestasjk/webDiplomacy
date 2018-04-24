<?php
/**
*
* newspage [Turkish]
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
	'POSTLOVE_USER_LIKES'	=> 'Kullanıcının beğenileri',
	'POSTLOVE_USER_LIKED'	=> 'Kullanıcının beğendikleri',
	'NOTIFICATION_POSTLOVE_ADD'	=> '%s paylaşımınızı <b>beğendi</b>:',
	'NOTIFICATION_TYPE_POST_LOVE'	=> 'Beğenilen paylaşımlar.',
	// Ver 1.1
	'LIKE_LINE'	=> '%1$s - %2$s , %3$s tarafından "%5$s" başlığında yapılan "%4$s" paylaşımını <b>beğendi</b>',
	'POSTLOVE_LIST'	=> 'Beğeniler',
	'POSTLOVE_LIST_VIEW'	=> 'Bütün beğeni eylemlerini listele',
));
