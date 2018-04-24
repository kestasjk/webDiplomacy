<?php
/**
*
* Post Love [Turkish]
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
	'POSTLOVE_CONTROL'	=> 'Paylaşım beğen',
	'POSTLOVE_USE_CSS'	=> 'Sağlanan CSSyi kullan',
	'POSTLOVE_USE_CSS_EXPLAIN'	=> 'POST LOVE eklentisini daha kolay özelleştirebilmek için mevcut CSSyi yüklemesini durdurabilirsiniz. Eğer kendi görsellerinizi kullanmak istiyorsanız, lütfen <code>overall_header_head_append.html</code> a bakınız.',
	'POSTLOVE_SHOW_LIKES'	=> 'Kullanıcının beğendiği mesaj saysını göster',
	'POSTLOVE_SHOW_LIKES_EXPLAIN'	=> '<code>viewtopic</code> içinde kullanıcının beğendiği mesaj sayısını göster.',
	'POSTLOVE_SHOW_LIKED'	=> 'Kullanıcının beğenilen mesaj sayısını göster',
	'POSTLOVE_SHOW_LIKED_EXPLAIN'	=> '<code>viewtopic</code> içinde kullanıcının beğenilen mesaj sayısını göster.',
	//Version 1.1 langs
	'ACP_POSTLOVE_GRP'	=> 'Post Love',
	'ACP_POSTLOVE'	=> 'Post love',
	'POSTLOVE_EXPLAIN'	=> 'Buradan Post Love\'ın bazı ayarlarını değiştirebilirsiniz',
	'CONFIRM_MESSAGE'	=> 'Değişiklikler uygulandı!<br><br><a href="%1$s">Geri</а>',
	'POSTLOVE_CURRENT_THEME'	=> 'Güncel tema',
	'THEME_NAME'	=> 'Tema adı',
	'THEME_AUTHOR'	=> 'Tema sahibi',
	'THEME_DESCRIPTION'	=> 'Tema açıklaması',
	'THEME_SUPPORT_STYLES'	=> 'Desteklenen stiller',
	'THEME_PREVIEW'	=> 'Önizleme',
	'POSTLOVE_CHOOSE_THEME' => 'Tema seç',
	'POSTLOVE_NO_THEMES_INSTALLED'	=> 'Yüklenmiş bir tema yok!<br>Lütfen <i>$phpbb_root_path/ext/anavaro/postlove/themes</i> klasörüne ekleyin',
	'THEME_CHANGED'	=> 'Tema değiştirildi!',
	'POSTLOVE_NO_WRITE_ACTION'	=> 'Yazma yetkisi yok!<br>Lütfen <i> $phpbb_root_path/ext/anavaro/postlove/styles </i>klasörüne yazma yetkisi verin',
	'POSTLOVE_AUTHOR_LIKE'	=> 'Kendi paylaşımlarını beğenme',
	'POSTLOVE_AUTHOR_LIKE_EXPLAIN'	=> 'Yazar kendi paylaşımlarını beğenebilir mi',

	'POSTLOVE_CLEAN_LOVES'	=> 'Clean post loves',
	'POSTLOVE_CLEAN_LOVES_EXPLAIN'	=> 'If you have installed Post Love before automatic post and user love cleaning - please press Clean to clean the unneeded Post Loves',
	'CLEN'	=> 'Clean',
));
