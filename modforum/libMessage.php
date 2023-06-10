<?php

class ModForumMessage
{
	public static function splitWords($text) {
		return $text;
		$words = explode(' ', $text);
		$text=array();
		foreach($words as $word)
		{
			if ( strlen($word) >= 20 )
			{
				$text[] = substr($word,0,20);
				$text[] = substr($word,20,strlen($word));
			}
			else
				$text[] = $word;
		}
		return implode(' ', $text);
	}

	static public function linkify($message)
	{
		$message=self::splitWords($message);

		$patterns = array(
				'/gameID[:= _]?([0-9]+)/i',
				'/userID[:= _]?([0-9]+)/i',
				'#(modforum.php.*viewthread[:= _]?)([0-9]+)#i',
				'#/forum.php.*threadID[:= _]?([0-9]+)#i'
			);
		$replacements = array(
				'<a href="board.php?gameID=\1" class="light">gameID=\1</a>',
				'<a href="profile.php?userID=\1" class="light">userID=\1</a>',
				'<a href="modforum.php?viewthread=\2#\2" class="light">modforumthreadID=\2</a>',
				'/forum.php?<a href="forum.php?threadID=\1#\1" class="light">threadID=\1</a>'
			);

		return preg_replace($patterns, $replacements, $message);
	}

	/**
	 * Send a message to the public forum. The variables passed are assumed to be already sanitized
	 *
	 * @param int $toID User/Thread ID to send to
	 * @param int $fromUserID UserID sent from
	 * @param string $message The message to be sent
	 * @param string[optional] $subject The subject
	 * @param string[optional] $type 'Bulletin'(GameMaster->Player) 'ThreadStart'(User->All) 'ThreadReply'(User->Thread)
	 *
	 * @return int The message ID
	 */
	static public function send($toID, $fromUserID, $message, $subject="", $type='Bulletin', $forceReply=false, $requestType='', $gameId=null)
	{
		global $DB, $User;

		//if( defined('AdminUserSwitch') && AdminUserSwitch != $User->id) $fromUserID = AdminUserSwitch;

		$message = self::linkify($message);

		$sentTime=time();

		if( 65000 < strlen($message) )
		{
			throw new Exception("Message too long");
		}

		$gameTurn = 0;
		if( $gameId != null ) list($gameTurn) = $DB->sql_row("SELECT turn FROM wD_Games WHERE id = " . $gameId);

		$DB->sql_put("INSERT INTO wD_ModForumMessages
						SET toID = ".$toID.", fromUserID = ".$fromUserID.", timeSent = ".$sentTime.",
						message = '".$message."', subject = '".$subject."', replies = 0,
						type = '".$type."', latestReplySent = 0, 
						requestType = '" .$requestType. "', gameID = " . ($gameId == null ? "NULL" : $gameId) .", gameTurn = " . $gameTurn);

		$id = $DB->last_inserted();

		if( !$User->type['Moderator'] )
			$DB->sql_put("UPDATE wD_Users SET notifications = CONCAT_WS(',',notifications, 'ModForum') WHERE type LIKE '%Moderator%' AND id != ".$fromUserID);
		
		$DB->sql_put("UPDATE wD_ModForumMessages SET latestReplySent = ".$id.", 
				".($toID == 0 ? "" : 'replies = replies + 1,')."
				".($User->type['Moderator'] ? "isModReplied = 1" : "isUserReplied = 1").",
				".($User->type['Moderator'] ? "isModRead = 1, isUserRead = 0" : "isUserRead = 1, isModRead = 0").",
				isUserMustReply = ".($forceReply ? 1 : 0)."
			WHERE ( id=".$id." OR id=".$toID." )");
			
		if ($User->type['Moderator'])
		{
			$DB->sql_put("UPDATE wD_ModForumMessages SET status='Open' WHERE status='New' AND id = ".$toID);
		}
		
		if ( $type == 'ThreadReply')
		{
			list($starterUserID, $modUserID) = $DB->sql_row('SELECT fromUserID, assigned FROM wD_ModForumMessages WHERE id = '.$toID);
			if ($starterUserID != $fromUserID)
			{
				$DB->sql_put("UPDATE wD_Users SET notifications = CONCAT_WS(',',notifications, 'ModForum') WHERE id = ".$starterUserID);
			}
			if ($modUserID != $fromUserID)
			{
				$DB->sql_put("UPDATE wD_Users SET notifications = CONCAT_WS(',',notifications, 'ModForum') WHERE id = ".$modUserID);
			}
		}
		return $id;
	}

	/**
	 * Remove any HTML added to a message
	 * @param $message The message to filter
	 * @return string The filtered message
	 */
	static function refilterHTML($message)
	{
		$patterns = array(
				'/<[^>]+>/i',
				'/<[^>]+$/i'
			);
		$replacements = array(
				' ',
				' '
			);

		return preg_replace($patterns, $replacements, $message);
	}
}

?>