<?php

/**
*
* Post Love [Polish]
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
	'POSTLOVE_CONTROL'	=> 'Polubienia postów',
	'POSTLOVE_USE_CSS'	=> 'Użyj dostarczonych stylów CSS',
	'POSTLOVE_USE_CSS_EXPLAIN'	=> 'Aby łatwiej spersonalizować rozszerzenie POST LOVE, możesz zatrzymać wczytywanie go z domyślnego stylu CSS. Jeśli chcesz użyć własnych grafik, przejdź do pliku <code>overall_header_head_append.html</code>.',
	'POSTLOVE_SHOW_LIKES'	=> 'Pokaż sumę postów, jakie zostały polubione przez tego użytkownika',
	'POSTLOVE_SHOW_LIKES_EXPLAIN'	=> 'Pokazuje w <code>viewtopic</code> sumę postów, jakie zostały polubione przez tego użytkownika.',
	'POSTLOVE_SHOW_LIKED'	=> 'Pokaż sumę postów tego użytkownika, jakie zostały polubione przez innych',
	'POSTLOVE_SHOW_LIKED_EXPLAIN'	=> 'Pokazuje w <code>viewtopic</code> sumę postów tego użytkownika, jakie zostały polubione przez innych.',

	//Version 1.1 langs
	'ACP_POSTLOVE_GRP'	=> 'Post Love',
	'ACP_POSTLOVE'	=> 'Polubienia postów',
	'POSTLOVE_EXPLAIN'	=> 'Z tego miejsca możesz zmienić ustawienia rozszerzenia Post Love',
	'CONFIRM_MESSAGE'	=> 'Zmiany zostały zapisane pomyślnie!<br><br><a href="%1$s">Powrót</а>',
	'POSTLOVE_CURRENT_THEME'	=> 'Obecny styl',
	'THEME_NAME'	=> 'Nazwa stylu',
	'THEME_AUTHOR'	=> 'Autor stylu',
	'THEME_DESCRIPTION'	=> 'Opis stylu',
	'THEME_SUPPORT_STYLES'	=> 'Wspierane style',
	'THEME_PREVIEW'	=> 'Podgląd',
	'POSTLOVE_CHOOSE_THEME' => 'Wybierz styl',

	'POSTLOVE_NO_THEMES_INSTALLED'	=> 'Obecnie nie jest zainstalowany żaden styl!<br>Dodaj go w folderze <i>$phpbb_root_path/ext/anavaro/postlove/themes</i>',
	'THEME_CHANGED'	=> 'Styl zmieniony',
	'POSTLOVE_NO_WRITE_ACTION'	=> 'Brak uprawnień do zapisu!<br>Zmień uprawnienia zapisu w folderze<i> $phpbb_root_path/ext/anavaro/postlove/styles </i>',

	'POSTLOVE_AUTHOR_LIKE'	=> 'Autor może polubić swoje posty',
	'POSTLOVE_AUTHOR_LIKE_EXPLAIN'	=> 'Określa, czy autor postu może polubić swoje własne posty czy też nie',

	'POSTLOVE_CLEAN_LOVES'	=> 'Wyczyść wszystkie polubienia postów',
	'POSTLOVE_CLEAN_LOVES_EXPLAIN'	=> 'Jeżeli zainstalowałeś rozszerzenie Post Love przed automatycznym postowaniem i czyszczeniem polubień użytkowników - użyj powyższej opcji, aby wyczyścić niepotrzebne polubienia postów.',
	'CLEAN'	=> 'WYCZYŚĆ',
));
