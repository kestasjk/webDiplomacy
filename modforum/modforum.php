<?php
class ModForum
{

	static function checkReply()
	{
		global $DB, $User;
		
		// Just clear the ForceReplyTag
		if(isset($_REQUEST['clearModTagID']) AND $User->type['User'] )
		{
			 $DB->sql_put('UPDATE wD_ForceReply SET forceReply="Done" 
							WHERE forceReply = "No"
								AND toUserID = '.$User->id.' 
								AND id = '.(int)$_REQUEST['clearModTagID']);								
		}
		
		// Post a reply
		if(isset($_REQUEST['newModmessage']) AND $User->type['User'] AND ($_REQUEST['newModmessage'] != "") ) 
		{
			// We're being asked to send a message.
			
			require_once('modforum/libMessage.php');
			$message = $DB->msg_escape($_REQUEST['newModmessage']);
			$replyID = (int)$_REQUEST['replyID'];
			
			$messageID = ModForumMessage::send( $replyID, $User->id, $message , '', 'ThreadReply');
			
			list($originalID)=$DB->sql_row('SELECT toID FROM wD_ModForumMessages WHERE id='.$replyID);
			$DB->sql_put('UPDATE wD_ModForumMessages SET latestReplySent="'.$messageID.'" WHERE id = '.$originalID);
								
			$DB->sql_put('UPDATE wD_ForceReply SET forceReply="Done" 
							WHERE forceReply = "Yes"
								AND toUserID = '.$User->id.' 
								AND id = '.(int)$_REQUEST['replyID']);
								
			$DB->sql_put("UPDATE wD_ForceReply
							SET status = 'Replied', replyIP = INET_ATON('".$_SERVER['REMOTE_ADDR']."')
							WHERE toUserID = ".$User->id." 
								AND id = ".(int)$_REQUEST['replyID']."
								AND status = 'Read'");
			
		}

	// Check and clear Modrequest.
	list($openReq)=$DB->sql_row('SELECT count(*) FROM wD_ForceReply WHERE forceReply != "Done" AND toUserID = '.$User->id);
	if ($openReq == 0)
		$User->clearNotification('ForceModMessage');
	
	}

	static function replaceVariables($message, $id)
	{	
		global $User, $DB;
		$userGroup = array();
		
		$tabl = $DB->sql_tabl("SELECT
			f.toUserID, u.username
			FROM wD_ForceReply f
			LEFT JOIN wD_Users u ON (f.toUserID = u.id)
			WHERE f.id = ".$id." && f.toUserID != ".$User->id);
		while( $account = $DB->tabl_hash($tabl) )
			$userGroup[$account['toUserID']] = $account['username'];
		
		$count = count($userGroup);
		
		$userGroupString=""; $first = true;
		foreach ($userGroup as $userID => $username)
		{
			if (!$first && $count-- == 2)
				$userGroupString .= ' and ';
			elseif (!$first) 
				$userGroupString .= ', ';
			else
				$first=false;
				
			$userGroupString .= '<a href="profile.php?userID='.$userID.'">'.$username.'</a>';
		}
		$message = str_replace ( '&lt;username&gt;' , $User->username , $message);
		$message = str_replace ( '&lt;account_names&gt;' , $userGroupString , $message);
		
		if (count($userGroup) > 1)
			$message = str_replace ( '&lt;plural_s&gt;' , 's' , $message);
		else
			$message = str_replace ( '&lt;plural_s&gt;' , '' , $message);
			
		return $message;
	}
	
	static function printModMessages()
	{	
		global $DB, $User;
		
		print '<div class="content">';
		
		$tabl = $DB->sql_tabl("SELECT
			m.id, timeSent, message, forceReply
			FROM wD_ModForumMessages m
			LEFT JOIN wD_ForceReply f ON (f.id = m.id)
			WHERE toUserID = '".$User->id."' AND forceReply != 'Done' 
			ORDER BY timeSent DESC");

		$switch = 1;
		while( $message = $DB->tabl_hash($tabl) )
		{
			$switch = 3-$switch; // 1,2,1,2,1,2...

			$DB->sql_put("UPDATE wD_ForceReply
							SET status = 'Read', readTime = ".time().", readIP = INET_ATON('".$_SERVER['REMOTE_ADDR']."')
							WHERE toUserID = ".$User->id." 
								AND id = ".$message['id']."
								AND status = 'Sent'");
			
			print '<div class="thread threadID'.$message['id'].' threadborder'.$switch.' threadalternate'.$switch.'">';
			print '<div class="leftRule message-head threadalternate'.$switch.'">
						<a href="profile.php?userID='.$message['id'].'">Mod-Team</a>'.
						'<br />
						<strong><em>'.libTime::text($message['timeSent']).'</em></strong><br />
					</div>';
						
			print '<div class="message-subject">';

			print libHTML::forumMessage($message['id'],$message['timeSent']);
			
			print '<strong>Urgent mod message:</strong>';

			$message['message'] = self::replaceVariables($message['message'], $message['id']);
			
			print '</div>				
				<div class="message-body threadalternate'.$switch.'">
					<div class="message-contents" fromUserID="'.$message['id'].'">
						'.$message['message'].'
					</div>
				</div>
			<div style="clear:both;"></div>';
			
			print '<div class="message-foot threadalternate'.$switch.'">';

			// Now we show the Reply box.
			if ( $message['forceReply'] == 'Yes')
				print '<div class="postbox">
						<form class="safeForm" method="post">
						<div class="hrthin"></div>
						<TEXTAREA name="newModmessage" style="margin-bottom:5px;" ROWS="4"></TEXTAREA><br />
						<input type="hidden" value="'.$message['id'].'" name="replyID">
						<input type="hidden" value="'.libHTML::formTicket().'" name="formTicket">
						<input type="submit" class="form-submit" value="'.l_t('Post reply').'" name="'.l_t('Reply').'"></p></form>
						</div>
						<div class="hrthin"></div></div></div>';
			else
				print '<div class="postbox">
						<form class="safeForm" method="post">
						<div class="hrthin"></div>
						<input type="hidden" value="'.$message['id'].'" name="clearModTagID">
						<input type="hidden" value="'.libHTML::formTicket().'" name="formTicket">
						<input type="submit" class="form-submit" value="'.l_t('Thanks for letting me know.').'" name="'.l_t('Reply').'"></p></form>
						</div>
						<div class="hrthin"></div></div></div>';
		}		
		print '</div>';
		
		if ($_SERVER['PHP_SELF'] != '/profile.php')
			libHTML::footer();
	}
	
}

?>