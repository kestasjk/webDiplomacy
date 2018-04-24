<?php
/**
*
* @package Anavaro.com Post Love
* @copyright (c) 2013 Lucifer
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/

namespace anavaro\postlove\acp;

/**
* @package acp
*/

class acp_postlove_module
{
	function main($id, $mode)
	{
		global $db, $config, $template, $request, $table_prefix, $phpbb_root_path;
		global $language, $phpbb_container;

		$language = $phpbb_container->get('language');
		//Define extension path (we will need it)
		$ext_path =  $phpbb_root_path . 'ext/anavaro/postlove/';

		$this->tpl_name = 'acp_postlove';
		$this->page_title = 'ACP_POSTLOVE';

		if ($request->is_set_post('submit'))
		{
			$postlove = $request->variable('poslove', array('' => ''));
			foreach ($postlove as $id => $var)
			{
				$config->set($id, $var);
			}
			trigger_error($language->lang('CONFIRM_MESSAGE', $this->u_action));
		}
		if ($request->variable('clean', false))
		{
			if (confirm_box(true))
			{
				// Now let's clean all post loves that have no posts
				$sql_ary = array(
					'SELECT'	=> 'pl.post_id as post_id',
					'FROM'		=> array($table_prefix . 'posts_likes' => 'pl'),
					'LEFT_JOIN'	=> array(
						array(
							'FROM'	=> array($table_prefix . 'posts' => 'p'),
							'ON'	=> 'pl.post_id = p.post_id'
						)
					),
					'WHERE'	=> 'p.post_id IS NULL'
				);
				$sql = $db->sql_build_query('SELECT', $sql_ary);
				$result = $db->sql_query($sql);
				$delete_post_likes = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$delete_post_likes[] = $row['post_id'];
				}
				$db->sql_freeresult($result);
				if (!empty($delete_post_likes))
				{
					$sql = 'DELETE FROM ' . $table_prefix . 'posts_likes WHERE ' . $db->sql_in_set('post_id', $delete_post_likes);
					$db->sql_query($sql);
					$deleted_post_likes = $db->sql_affectedrows();
					var_dump($deleted_post_likes . ' post likes deleted');
				}
				$sql_ary = array(
					'SELECT'	=> 'pl.user_id as user_id',
					'FROM'		=> array($table_prefix . 'posts_likes' => 'pl'),
					'LEFT_JOIN'	=> array(
						array(
							'FROM'	=> array($table_prefix . 'users' => 'u'),
							'ON'	=> 'pl.user_id = u.user_id'
						)
					),
					'WHERE'	=> 'u.user_id IS NULL'
				);
				$sql = $db->sql_build_query('SELECT', $sql_ary);
				$result = $db->sql_query($sql);
				$delete_user_likes = array();
				while ($row = $db->sql_fetchrow($result))
				{
					$delete_user_likes[] = $row['user_id'];
				}
				$db->sql_freeresult($result);
				if (!empty($delete_user_likes))
				{
					$sql = 'DELETE FROM ' . $table_prefix . 'posts_likes WHERE ' . $db->sql_in_set('user_id', $delete_user_likes);
					$db->sql_query($sql);
					$deleted_user_likes = $db->sql_affectedrows();
					var_dump($deleted_user_likes . ' user likes deleted');
				}
			}
			else
			{
				confirm_box(false, $language->lang('CONFIRM_OPERATION'), build_hidden_fields(array('clean' => true)));
			}
		}

		$template->assign_vars(array(
			'POST_LIKES'	=> ($config['postlove_show_likes'] == 1 ? true : false),
			'POST_LIKED'	=> ($config['postlove_show_liked'] == 1 ? true : false),
			'AUTHOR_LIKE'	=> ($config['postlove_author_like'] == 1 ? true : false),
		));
	}
}
