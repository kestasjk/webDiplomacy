<?php

/**
*
* Post Love [Spanish]
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
	'POSTLOVE_CONTROL'	=> 'Mensaje que gusta',
	'POSTLOVE_USE_CSS'	=> 'Usar CSS proporcionado',
	'POSTLOVE_USE_CSS_EXPLAIN'	=> 'Para facilitar la personalización de la extensión POST LOVE, podría evitar la carga del CSS por defecto. Si desea utilizar sus propias imágenes, por favor, consulte el archivo <code>overall_header_head_append.html</code>',
	'POSTLOVE_SHOW_LIKES'	=> 'Mostrar el número de mensajes que le han gustado.',
	'POSTLOVE_SHOW_LIKES_EXPLAIN'	=> 'Mostrar en <code>viewtopic</code> el número de mensajes que han gustado a este usuario.',
	'POSTLOVE_SHOW_LIKED'	=> 'Mostrar el número de mensajes que han gustado.',
	'POSTLOVE_SHOW_LIKED_EXPLAIN'	=> 'Mostrar en <code>viewtopic</code> cuántos mensajes del usuario han gustado a los demás.',

	//Version 1.1 langs
	'ACP_POSTLOVE_GRP'	=> 'Post Love',
	'ACP_POSTLOVE'	=> 'Post love',
	'POSTLOVE_EXPLAIN'	=> 'Desde aquí puede cambiar algunas opciones de Post Love',
	'CONFIRM_MESSAGE'	=> '¡Cambios guardados!<br><br><a href="%1$s">Volver</а>',
	'POSTLOVE_CURRENT_THEME'	=> 'Tema actual',
	'THEME_NAME'	=> 'Nombre del tema',
	'THEME_AUTHOR'	=> 'Autor del tema',
	'THEME_DESCRIPTION'	=> 'Descripción del tema',
	'THEME_SUPPORT_STYLES'	=> 'Temas soportados',
	'THEME_PREVIEW'	=> 'Vista previa',
	'POSTLOVE_CHOOSE_THEME' => 'Seleccionar tema',

	'POSTLOVE_NO_THEMES_INSTALLED'	=> '¡No hay temas instalados!<br>Añádalos en la carpeta <i>$phpbb_root_path/ext/anavaro/postlove/themes</i>',
	'THEME_CHANGED'	=> 'Estilo cambiado',
	'POSTLOVE_NO_WRITE_ACTION'	=> 'No write acccess!<br>Permitir el acceso de escritura a la carpeta <i>$phpbb_root_path/ext/anavaro/postlove/styles</i>',

	'POSTLOVE_AUTHOR_LIKE'	=> 'El autor puede enviar me gusta',
	'POSTLOVE_AUTHOR_LIKE_EXPLAIN'	=> 'Puede el autor hacer me gusta sus propios mensajes o no',

	'POSTLOVE_CLEAN_LOVES'	=> 'Limpiar post loves',
	'POSTLOVE_CLEAN_LOVES_EXPLAIN'	=> 'Si ha instalado Post Love antes de la publicación automática, y el usuario ama la limpieza - por favor, presione Limpiar, para limpiar los innecesarios Post Loves',
	'CLEN'	=> 'Limpiar',
));
