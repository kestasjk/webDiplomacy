<?php

/**
*
* Post Love [Bulgarian]
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
	'POSTLOVE_CONTROL'	=> 'Харесване на постове',
	'POSTLOVE_USE_CSS'	=> 'Използвай CSS на разширението',
	'POSTLOVE_USE_CSS_EXPLAIN'	=> 'За по-лесна промяна на стила на разширението за харесване на постове можете да спрете показването на CSS-а по подразбиране. Ако искате да използвате свои каритники моля погледнете <code>overall_header_head_append.html</code>',
	'POSTLOVE_SHOW_LIKES'	=> 'Покажи броя на харесаните от потребителя постове',
	'POSTLOVE_SHOW_LIKES_EXPLAIN'	=> 'Покажи в <code>viewtopic</code> общия брой на харесаните от този потребител постове.',
	'POSTLOVE_SHOW_LIKED'	=> 'Покажи броя на харесаните постове на потребителя',
	'POSTLOVE_SHOW_LIKED_EXPLAIN'	=> 'Покажи в <code>viewtopic</code> общия брой на харесаните постове на този потребител.',

	//Version 1.1 langs
	'ACP_POSTLOVE_GRP'	=> 'Post Love',
	'ACP_POSTLOVE'	=> 'Post love',
	'POSTLOVE_EXPLAIN'	=> 'От тук можете да контролирате различни настройки на харесването на постове',
	'CONFIRM_MESSAGE'	=> 'Промените запазени!<br><br><a href="%1$s">Върни се обратно </а>',
	'POSTLOVE_CURRENT_THEME'	=> 'Настояща тема',
	'THEME_NAME'	=> 'Име на темата',
	'THEME_AUTHOR'	=> 'Автор на темата',
	'THEME_DESCRIPTION'	=> 'Описание на темата',
	'THEME_SUPPORT_STYLES'	=> 'Поддържани стилове',
	'THEME_PREVIEW'	=> 'Преглед',
	'POSTLOVE_CHOOSE_THEME' => 'Избор на тема',

	'POSTLOVE_NO_THEMES_INSTALLED'	=> 'Няма инсталирани допълнителни теми!<br>Моля добавете темите в папка <i> $phpbb_root_path/ext/anavaro/postlove/themes </i>',
	'THEME_CHANGED'	=> 'Темата е сменена',
	'POSTLOVE_NO_WRITE_ACTION'	=> 'Нямам права да пиша!<br>Моля разрешете писането в <i> $phpbb_root_path/ext/anavaro/postlove/styles </i>',

	'POSTLOVE_AUTHOR_LIKE'	=> 'Автора може да харесва',
	'POSTLOVE_AUTHOR_LIKE_EXPLAIN'	=> 'Дали автора може да харесва собствените си постове или не',

	'POSTLOVE_CLEAN_LOVES'	=> 'Почисти излишните харесвания',
	'POSTLOVE_CLEAN_LOVES_EXPLAIN'	=> 'Ако случайно сте използвали Post Love преди да сложат почистването след триене на постове и потребители - натиснете Изчисти, за да почистите излишните записи в базата',
	'CLEN'	=> 'Почисти',
));
