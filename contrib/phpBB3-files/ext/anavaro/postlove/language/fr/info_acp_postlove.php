<?php
/**
*
* Post Love extension for the phpBB Forum Software package.
* French translation by Galixte (http://www.galixte.com)
*
* @copyright (c) 2015 Stanislav Atanasov <http://anavaro.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ « » “ ” …
//

$lang = array_merge($lang, array(
	'POSTLOVE_CONTROL'	=> '« J’aime » un message',
	'POSTLOVE_USE_CSS'	=> 'Utiliser la feuille de style CSS fournie',
	'POSTLOVE_USE_CSS_EXPLAIN'	=> 'Permet de personnaliser aisément l’extension « Post Love » pour remplacer la feuille de style CSS par défaut. Pour utiliser ses propres images, se référer au fichier : <code>overall_header_head_append.html</code>.',
	'POSTLOVE_SHOW_LIKES'	=> 'Afficher le nombre de « J’aime » exprimés par l’utilisateur',
	'POSTLOVE_SHOW_LIKES_EXPLAIN'	=> 'Permet d’afficher le nombre de messages aimés par l’utilisateur sur les pages des sujets, <code>viewtopic</code>, au moyen du terme : « J’aime ».',
	'POSTLOVE_SHOW_LIKED'	=> 'Afficher le nombre de « J’aime » reçus par les utilisateurs',
	'POSTLOVE_SHOW_LIKED_EXPLAIN'	=> 'Permet d’afficher le nombre de messages aimés des autres utilisateurs sur les pages des sujets, <code>viewtopic</code>, au moyen du terme « J’aime ».',

	//Version 1.1 langs
	'ACP_POSTLOVE_GRP'	=> 'Aimer un message',
	'ACP_POSTLOVE'	=> 'Aimer un message',
	'POSTLOVE_EXPLAIN'	=> 'Depuis cette page il est possible de modifier les paramètres de l’extension « Post Love ».',
	'CONFIRM_MESSAGE'	=> 'Les modifications ont été sauvegardées !<br><br><a href="%1$s">Retour</а>',
	'POSTLOVE_CURRENT_THEME'	=> 'Thème actuel',
	'THEME_NAME'	=> 'Nom du thème',
	'THEME_AUTHOR'	=> 'Auteur du thème',
	'THEME_DESCRIPTION'	=> 'Description du thème',
	'THEME_SUPPORT_STYLES'	=> 'Styles supportés',
	'THEME_PREVIEW'	=> 'Aperçu',
	'POSTLOVE_CHOOSE_THEME' => 'Sélectionner un thème',

	'POSTLOVE_NO_THEMES_INSTALLED'	=> 'Il n’y a aucun thème installé !<br>Merci d’en ajouter dans le répertoire : <i>$phpbb_root_path/ext/anavaro/postlove/themes</i>.',
	'THEME_CHANGED'	=> 'Le thème a été modifié.',
	'POSTLOVE_NO_WRITE_ACTION'	=> 'Les permissions en écriture ne sont pas correctes !<br>Merci d’attribuer des permissions en écriture sur le répertoire : <i>$phpbb_root_path/ext/anavaro/postlove/styles</i>, au moyen de la commande : CHMOD 777.',

	'POSTLOVE_AUTHOR_LIKE'	=> 'L’auteur peut aimer ses messages',
	'POSTLOVE_AUTHOR_LIKE_EXPLAIN'	=> 'Permettre ou non à l’auteur d’aimer son/ses propre(s) message(s).',

	'POSTLOVE_CLEAN_LOVES'	=> 'Nettoyer les « J’aime » des messages',
	'POSTLOVE_CLEAN_LOVES_EXPLAIN'	=> 'Permet de nettoyer les « J’aime » inutiles des messages. Cette action est utile si l’extension « Post Love » a été installée avant le nettoyage automatique des messages et des « j’aime » d’utilisateurs.',
	'CLEN'	=> 'Nettoyer',
));
