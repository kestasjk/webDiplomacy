<?php
/**
*
* Post Love [Czech]
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
	'POSTLOVE_CONTROL'	=> 'Oblíbené příspěvky',
	'POSTLOVE_USE_CSS'	=> 'Používat CSS z rozšíření',
	'POSTLOVE_USE_CSS_EXPLAIN'	=> 'Pro snadnější přizpůsobení rozšíření můžete zakázat načítání CSS stylů, které jsou jeho součástí a navrhnout si svůj vlastní styl. Pokud chcete používat vlastní obrázky, zaměřte se na <code>overall_header_head_append.html</code>',
	'POSTLOVE_SHOW_LIKES'	=> 'Zobrazovat počet příspěvků, které se líbí tomuto uživateli.',
	'POSTLOVE_SHOW_LIKES_EXPLAIN'	=> 'Zobrazovat ve <code>viewtopic</code> počet příspěvků, které se uživateli líbí.',
	'POSTLOVE_SHOW_LIKED'	=> 'Zobrazovat počet příspěvků, které se líbí ostatním uživatelům.',
	'POSTLOVE_SHOW_LIKED_EXPLAIN'	=> 'Zobrazovat ve <code>viewtopic</code> počet příspěvků, které se uživatelům líbily.',

	//Version 1.1 langs
	'ACP_POSTLOVE_GRP'	=> 'Post Love',
	'ACP_POSTLOVE'	=> 'Post love',
	'POSTLOVE_EXPLAIN'	=> 'Zde je možné přizpůsobit nastavení Post Love',
	'CONFIRM_MESSAGE'	=> 'Změny uloženy!<br><br><a href="%1$s">Zpět</а>',
	'POSTLOVE_CURRENT_THEME'	=> 'Aktuální motiv',
	'THEME_NAME'	=> 'Název motivu',
	'THEME_AUTHOR'	=> 'Autor motivu',
	'THEME_DESCRIPTION'	=> 'Popis motivu',
	'THEME_SUPPORT_STYLES'	=> 'Podporované styly',
	'THEME_PREVIEW'	=> 'Náhled',
	'POSTLOVE_CHOOSE_THEME' => 'Vyberte motiv',

	'POSTLOVE_NO_THEMES_INSTALLED'	=> 'Nejsou nainstalovány žádné motivy.<br>Přidejte je do složky <i>$phpbb_root_path/ext/anavaro/postlove/themes</i>.',
	'THEME_CHANGED'	=> 'Motiv změněn',
	'POSTLOVE_NO_WRITE_ACTION'	=> 'Nemáte oprávnění k zápisu.<br>Povolte možnost zápisu do složky <i> $phpbb_root_path/ext/anavaro/postlove/styles</i>.',

	'POSTLOVE_AUTHOR_LIKE'	=> 'Autor může označovat své vlastní příspěvky',
	'POSTLOVE_AUTHOR_LIKE_EXPLAIN'	=> 'Je-li povoleno, autor může označit své vlastní příspěvky tlačítkem Líbí se.',

	'POSTLOVE_CLEAN_LOVES'	=> 'Pročistit hodnocení',
	'POSTLOVE_CLEAN_LOVES_EXPLAIN'	=> 'Pokud bylo rozšíření Post Love nainstalováno ještě před uvedením funkce automatického čištění příspěvků a uživatelského Post Love hodnocení, proveďte stiskem tlačítka „Vyčistit“ pročištění nepotřebných Post Love hodnocení.',
	'CLEN'	=> 'Vyčistit',
));
