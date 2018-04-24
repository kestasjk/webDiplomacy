<?php

/**
*
* Post Love [English]
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
	'POSTLOVE_CONTROL'	=> 'Beitrag gefällt mir',
	'POSTLOVE_USE_CSS'	=> 'CSS verwenden',
	'POSTLOVE_USE_CSS_EXPLAIN'	=> 'Um POST LOVE einfacher anzupassen, kann man es daran hindern, die mitgelieferte CSS-Datei zu verwenden. Eigenen Bilder können in der <code>overall_header_head_append.html</code> eingebunden werden.',
	'POSTLOVE_SHOW_LIKES'	=> 'Zeige die Anzahl an Beiträge, die dem Benutzer gefallen.',
	'POSTLOVE_SHOW_LIKES_EXPLAIN'	=> 'Zeige die Anzahl an Beiträge in  <code>viewtopic</code> die Anzahl an Beiträge, die dem Benutzer gefallen.',
	'POSTLOVE_SHOW_LIKED'	=> 'Zeige die Anzahl an Beiträgen des User\'s die Anderen gefallen haben.',
	'POSTLOVE_SHOW_LIKED_EXPLAIN'	=> 'Zeige in <code>viewtopic</code> die Anzahl an Beiträgen des User\'s die Anderen gefallen haben.',

	//Version 1.1 langs
	'ACP_POSTLOVE_GRP'	=> 'Post Love',
	'ACP_POSTLOVE'	=> 'Post love',
	'POSTLOVE_EXPLAIN'	=> 'Hier kann man die Post Love Einstellungen ändern',
	'CONFIRM_MESSAGE'	=> 'Änderungen gespeichert!<br><br><a href="%1$s">Zurück</а>',
	'POSTLOVE_CURRENT_THEME'	=> 'Aktuelles Thema',
	'THEME_NAME'	=> 'Themen Name',
	'THEME_AUTHOR'	=> 'Themen Author',
	'THEME_DESCRIPTION'	=> 'Themen Beschreibung',
	'THEME_SUPPORT_STYLES'	=> 'Unterstützte Styles',
	'THEME_PREVIEW'	=> 'Vorschau',
	'POSTLOVE_CHOOSE_THEME' => 'Thema auswählen',

	'POSTLOVE_NO_THEMES_INSTALLED'	=> 'Es sind keine Themen installiert!<br>Bitte im Ordner <i>$phpbb_root_path/ext/anavaro/postlove/themes</i> hinzufügen',
	'THEME_CHANGED'	=> 'Theme changed',
	'POSTLOVE_NO_WRITE_ACTION'	=> 'Keine Schreibrechte!<br>Bitte Schreibrechte für den Ordner <i> $phpbb_root_path/ext/anavaro/postlove/styles </i>hinzufügen',

	'POSTLOVE_AUTHOR_LIKE'	=> 'Autor können Beiträge gefallen',
	'POSTLOVE_AUTHOR_LIKE_EXPLAIN'	=> 'Dürfen dem Autor seine eigenen Beiträge gefallen',

	'POSTLOVE_CLEAN_LOVES'	=> 'Gefällt mir Angaben bereinigen',
	'POSTLOVE_CLEAN_LOVES_EXPLAIN'	=> 'Wenn Du Post Love installiert hast, bervor automatisches Aufräumen aktiviert war - Bitte Reinigen drücken um ungewollte Gefällt-Mir-Angaben zu bereinigen',
	'CLEN'	=> 'Reinigen',
	'CLEAN'	=> 'Reinigen',
));
