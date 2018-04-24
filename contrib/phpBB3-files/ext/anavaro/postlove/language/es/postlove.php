<?php

/**
*
* newspage [Spanish]
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
	'POSTLOVE_USER_LIKES'	=> 'Al usuario le han gustado',
	'POSTLOVE_USER_LIKED'	=> 'El usuario ha gustado',

	'NOTIFICATION_POSTLOVE_ADD'	=> 'A %s le ha <b>gustado</b> su mensaje:',

	// Ver 1.1
	'LIKE_LINE'	=> '%1$s - %2$s <b>gustó</b> el mensaje de %3$s en “%4$s” en el tema “%5$s”',
	'POSTLOVE_LIST'	=> 'Gustó',
	'POSTLOVE_LIST_VIEW'	=> 'Mostrar lista con todas las acciones similares',
));
