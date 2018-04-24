<?php

/**
*
* newspage [Dutch]
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
	'POSTLOVE_USER_LIKES'	=> 'Gebruiker vindt leuk',
	'POSTLOVE_USER_LIKED'	=> 'Gebruiker heeft leuk gevonden',

	'NOTIFICATION_POSTLOVE_ADD'	=> '%s vindt je volgende bericht leuk:',
	'NOTIFICATION_TYPE_POST_LOVE'	=> 'Iemand vindt een bericht van je leuk:',

	// Ver 1.1
	'LIKE_LINE'	=> '%1$s - %2$s <b>vindt</b> %3$s\'s bericht "%4$s" leuk in onderwerp "%5$s"',
	'POSTLOVE_LIST'	=> 'Vind ik leuk',
	'POSTLOVE_LIST_VIEW'	=> 'Toon lijst met alle "vind ik leuks"',
));
