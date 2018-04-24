<?php

/**
*
* Post Love [Brazilian Portuguese [pt_br]]
* Brazilian Portuguese translation by eunaumtenhoid (c) 2017 [ver 1.2.1] (https://github.com/phpBBTraducoes)
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
	'POSTLOVE_CONTROL'	=> 'Curtir Post',
	'POSTLOVE_USE_CSS'	=> 'Use CSS fornecido',
	'POSTLOVE_USE_CSS_EXPLAIN'	=> 'Para uma personalização mais fácil da extensão POST LOVE, você pode impedi-lo de carregar o CSS padrão. Se você quiser usar suas próprias imagens, consulte <code>global_header_head_append.html</code>.',
	'POSTLOVE_SHOW_LIKES'	=> 'Mostra o número de postagens que este usuário curtiu',
	'POSTLOVE_SHOW_LIKES_EXPLAIN'	=> 'Mostre em <code>viewtopic</code> o número de postagens que o usuário curtiu.',
	'POSTLOVE_SHOW_LIKED'	=> 'Mostra o número de curtidas nas postagens do usuário',
	'POSTLOVE_SHOW_LIKED_EXPLAIN'	=> 'Mostrar em <code>viewtopic</code> quantos posts do usuário foram curtidos por outros.',

	//Version 1.1 langs
	'ACP_POSTLOVE_GRP'	=> 'Post Love',
	'ACP_POSTLOVE'	=> 'Post love',
	'POSTLOVE_EXPLAIN'	=> 'A partir daqui, você pode alterar algumas configurações do Post Love',
	'CONFIRM_MESSAGE'	=> 'Alterações salvas!<br><br><a href="%1$s">Voltar</а>',
	'POSTLOVE_CURRENT_THEME'	=> 'Tema atual',
	'THEME_NAME'	=> 'Nome do tema',
	'THEME_AUTHOR'	=> 'Autor do tema',
	'THEME_DESCRIPTION'	=> 'Descrição do tema',
	'THEME_SUPPORT_STYLES'	=> 'Estilos suportados',
	'THEME_PREVIEW'	=> 'Prever',
	'POSTLOVE_CHOOSE_THEME' => 'Selecione o tema',

	'POSTLOVE_NO_THEMES_INSTALLED'	=> 'Não há temas instalados!<br>Por favor, adicione-os na pasta <i>$phpbb_root_path/ext/anavaro/postlove/themes</i>',
	'THEME_CHANGED'	=> 'Tema alterado',
	'POSTLOVE_NO_WRITE_ACTION'	=> 'Sem acesso de gravação!<br>Permita o acesso de gravação a pasta <i>$phpbb_root_path/ext/anavaro/postlove/styles</i>',

	'POSTLOVE_AUTHOR_LIKE'	=> 'O autor pode curtir posts',
	'POSTLOVE_AUTHOR_LIKE_EXPLAIN'	=> 'O autor pode curtir suas próprios posts ou não',

	'POSTLOVE_CLEAN_LOVES'	=> 'Limpar post loves',
	'POSTLOVE_CLEAN_LOVES_EXPLAIN'	=> 'Se você instalou o Post Love antes da postagem automática e usou limpeza love - por favor, pressione Limpar para limpar os Post Loves desnecessários ',
	'CLEAN'	=> 'LIMPAR',
));
