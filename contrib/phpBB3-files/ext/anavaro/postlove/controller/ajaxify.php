<?php
/**
*
* Post Love extension for the phpBB Forum Software package.
*
* @copyright (c) 2014 Lucifer <http://www.anavaro.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace anavaro\postlove\controller;

class ajaxify
{
	/**
	 * Constructor
	 * NOTE: The parameters of this method must match in order and type with
	 * the dependencies defined in the services.yml file for this service.
	 *
	 * @param \phpbb\config|\phpbb\config\config $config Config object
	 * @param \phpbb\db\driver|\phpbb\db\driver\driver_interface $db Database object
	 * @param \phpbb\user $user User object
	 * @param \anavaro\postlove\controller\notifyhelper $notifyhelper Notification helper.
	 * @param $likes_table
	 * @internal param string $table_prefix phpBB Table Prefix
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\user $user, \anavaro\postlove\controller\notifyhelper $notifyhelper,
								$likes_table)
	{
		$this->config = $config;
		$this->db = $db;
		$this->user = $user;
		$this->notifyhelper = $notifyhelper;
		$this->likes_table = $likes_table;
	}

	public function base ($action, $post)
	{
		switch ($action)
		{
			case 'toggle':
				if ($this->user->data['user_type'] == 1 || $this->user->data['user_type'] == 2)
				{
					return new \Symfony\Component\HttpFoundation\JsonResponse(array(
						'error'	=> 1
					));
				}
				else
				{
					//get state for the like
					$sql_array = array(
						'SELECT'	=> 'pl.timestamp as timestamp, pl.user_id as liker_id, p.topic_id as topic_id, p.poster_id as poster, p.post_subject as post_subject',
						'FROM'	=> array(
							POSTS_TABLE	=> 'p',
						),
						'LEFT_JOIN'	=> array(
							array(
								'FROM'	=> array($this->likes_table	=> 'pl'),
								'ON'	=> 'pl.post_id = p.post_id AND pl.user_id = ' . $this->user->data['user_id']
							),
						),
						'WHERE'	=> 'p.post_id = ' . (int) $post
					);
					$sql = $this->db->sql_build_query('SELECT', $sql_array);
					$result = $this->db->sql_query($sql);
					$row = $this->db->sql_fetchrow($result);
					$this->db->sql_freeresult($result);
					if (!$row || (!$this->config['postlove_author_like'] && $row['poster'] == $this->user->data['user_id']))
					{
						return new \Symfony\Component\HttpFoundation\JsonResponse(array(
							'error'	=> 1
						));
					}

					else
					{
						if (!$row['timestamp'])
						{
							//so we don't have record for this user loving this post ... give some love!
							$sql = 'INSERT INTO ' . $this->likes_table . ' (post_id, user_id, type, timestamp) VALUES (' . (int) $post . ', ' . $this->user->data['user_id'] . ', \'post\', ' . time() . ')';
							$result = $this->db->sql_query($sql);
							$this->db->sql_freeresult($result);
							$this->update_like_counts($row['poster'], $this->user->data['user_id'], 1);
							$sql = 'SELECT topic_id, poster_id, post_subject FROM ' . POSTS_TABLE . ' WHERE post_id = ' . (int) $post;
							$result = $this->db->sql_query($sql);
							$row1 = $this->db->sql_fetchrow($result);
							$this->db->sql_freeresult($result);
							$this->notifyhelper->notify('add', $row1['topic_id'], (int) $post, $row1['post_subject'], $row1['poster_id'] , $this->user->data['user_id']);
							return new \Symfony\Component\HttpFoundation\JsonResponse(array(
								'toggle_action'	=> 'add',
								'toggle_post'	=> $post,
							));
						}
						else
						{
							//so we have a record ... and the user don't love it anymore!
							$sql = 'DELETE FROM ' . $this->likes_table . ' WHERE post_id = ' . (int) $post . ' AND user_id = ' . $this->user->data['user_id'];
							$result = $this->db->sql_query($sql);
							$this->db->sql_freeresult($result);
							$this->update_like_counts($row['poster'], $this->user->data['user_id'], -1);
							$this->notifyhelper->notify('remove', $row['topic_id'], (int) $post, $row['post_subject'], $row['poster'], $this->user->data['user_id']);
							return new \Symfony\Component\HttpFoundation\JsonResponse(array(
								'toggle_action' => 'remove',
								'toggle_post'	=> $post,
							));
						}
					}
				}
			break;
		}
		// We should never get this ... but hey - the code smells without it.
		return 0;
	}

	/**
	* Move the two cached counts a like sits between: the poster's likes received and the liker's
	* likes given. main_listener::modify_post_row reads these off the user row rather than counting
	* the likes table once per post it renders, so they have to move with every toggle.
	*
	* @param int $poster_id  The user whose post was liked
	* @param int $liker_id   The user who gave or took back the like
	* @param int $delta      1 when a like was given, -1 when one was taken back
	*/
	private function update_like_counts($poster_id, $liker_id, $delta)
	{
		// The columns are UNSIGNED, so a decrement goes through a signed value with a floor of zero
		// rather than wrapping if a count has somehow drifted below what is on the likes table.
		$received = ($delta > 0 ? 'webdip_like_count + 1' : 'GREATEST(CAST(webdip_like_count AS SIGNED) - 1, 0)');
		$given = ($delta > 0 ? 'webdip_like_given_count + 1' : 'GREATEST(CAST(webdip_like_given_count AS SIGNED) - 1, 0)');

		$this->db->sql_query('UPDATE ' . USERS_TABLE . ' SET webdip_like_count = ' . $received . ' WHERE user_id = ' . (int) $poster_id);
		$this->db->sql_query('UPDATE ' . USERS_TABLE . ' SET webdip_like_given_count = ' . $given . ' WHERE user_id = ' . (int) $liker_id);
	}
}
