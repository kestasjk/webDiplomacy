<?php

/**
*
* Post Love [Dutch]
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
	'POSTLOVE_CONTROL'	=> 'Post Love',
	'POSTLOVE_USE_CSS'	=> 'Gebruik bijgeleverde CSS:',
	'POSTLOVE_USE_CSS_EXPLAIN'	=> 'Om POST LOVE makkelijker te kunnen aanpassen kan je het laden van de standaard CSS uitzetten. Als je je eigen afbeeldingen wil gebruiken, kijk dan in <code>overall_header_head_append.html</code>',
	'POSTLOVE_SHOW_LIKES'	=> 'Laat het aantal berichten zien dat deze gebruiker leuk vindt:',
	'POSTLOVE_SHOW_LIKES_EXPLAIN'	=> 'Laat het aantal berichten dat een gebruiker leuk vind zien in <code>viewtopic</code> pagina.',
	'POSTLOVE_SHOW_LIKED'	=> 'Laat het aantal door anderen leuk gevonden berichten van de gebruiker zien:',
	'POSTLOVE_SHOW_LIKED_EXPLAIN'	=> 'Laat in <code>viewtopic</code> zien hoeveel berichten van deze gebruiker leuk gevonden worden door anderen.',

	//Version 1.1 langs
	'ACP_POSTLOVE_GRP'	=> 'Post Love',
	'ACP_POSTLOVE'	=> 'Post Love',
	'POSTLOVE_EXPLAIN'	=> 'Hier kun je instellingen van Post Love veranderen.',
	'CONFIRM_MESSAGE'	=> 'Veranderingen opgeslagen!<br><br><a href="%1$s">Terug</а>',
	'POSTLOVE_CURRENT_THEME'	=> 'Huidig thema',
	'THEME_NAME'	=> 'Naam thema',
	'THEME_AUTHOR'	=> 'Auteur',
	'THEME_DESCRIPTION'	=> 'Beschrijving van thema',
	'THEME_SUPPORT_STYLES'	=> 'Ondersteunde thema\'s',
	'THEME_PREVIEW'	=> 'Voorvertoning',
	'POSTLOVE_CHOOSE_THEME' => 'Selecteer thema',

	'POSTLOVE_NO_THEMES_INSTALLED'	=> 'Er zijn geen thema\'s geinstalleerd!<br>Voeg ze toe in de <i>$phpbb_root_path/ext/anavaro/postlove/themes</i> map',
	'THEME_CHANGED'	=> 'Thema veranderd',
	'POSTLOVE_NO_WRITE_ACTION'	=> 'Geen schrijftoegang!<br>Geef toestemming tot schrijven voor de<i> $phpbb_root_path/ext/anavaro/postlove/styles</i> map',

	'POSTLOVE_AUTHOR_LIKE'	=> 'Auteur kan bericht leuk vinden:',
	'POSTLOVE_AUTHOR_LIKE_EXPLAIN'	=> 'Kan de auteur zijn/haar eigen berichten leuk vinden (of niet).',

	'POSTLOVE_CLEAN_LOVES'	=> 'Opruimen post loves',
	'POSTLOVE_CLEAN_LOVES_EXPLAIN'	=> 'Als je een oude versie van Post Love gebruikt hebt waar automatisch opschonen nog niet beschikbaar was, druk dan op \'Opruimen\' om de database op te schonen.',
	'CLEAN'	=> 'Opruimen',
));
