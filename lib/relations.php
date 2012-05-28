<?php

class libRelations {

	static function checkRelationsGame(User $User, Game $Game)
	{
		global $DB;
		if ($Game->rlPolicy == 'Strict')
		{
			list($rlFriends) = $DB->sql_row("SELECT count(*) FROM wD_Members AS m
												LEFT JOIN wD_Users AS u ON ( u.id = m.userID )
												WHERE m.gameID=".$Game->id." AND u.id != ".$User->id." AND u.rlGroup = ".$User->rlGroup);
			if ($rlFriends > 0 && $User->rlGroup != 0)
				return "Sorry, you are unable to join this game.<br>This is probably because somebody you know in real life has already joined and the RLPolicy of this game is <b>No Friends</b>.<br>You are welcome to play games with no RLPolicy set together with friends as long as you don't metagame.";
		}
		if ($Game->rlPolicy == 'Friends')
		{
			list($gameRLgroup) = $DB->sql_row("SELECT rlGroup FROM wD_Users AS u
												LEFT JOIN wD_Members AS m ON ( u.id = m.userID )
												WHERE m.gameID=".$Game->id);
			if ($gameRLgroup != $User->rlGroup && $User->rlGroup != 0)
				return "Sorry, you are unable to join this game.<br>The players of this game know each other in RL and want to discuss alliances outside this website too.";
		}
	}
	
	static function getallUsers($groupID)
	{
		global $DB;
		$groupUsers=array();
		
		$sql = "SELECT id FROM wD_Users WHERE rlGroup=".$groupID;
		$tabl = $DB->sql_tabl($sql);
		while(list($userID) = $DB->tabl_row($tabl))
			$groupUsers[]=$userID;
			
		return $groupUsers;
	}
	
	static function getCommonGames($groupID)
	{
		global $DB;
		
		$tabl= $DB->sql_tabl(
			"SELECT m.gameID, count(m.gameID) FROM wD_Members m
				LEFT JOIN wD_Users u ON ( u.id = m.userID )
				LEFT JOIN wD_Games g ON ( g.id = m.gameID )
			WHERE g.phase != 'Finished' AND g.phase != 'Pre-game' AND u.rlGroup=".$groupID."
			GROUP BY gameID
			HAVING count(*) > 1");

		$games=array();
		while (list ($gameID, $count) = $DB->tabl_row($tabl))
		{
			$Variant=libVariant::loadFromGameID($gameID);
			if (count($Variant->countries) > $count)
				$games[$gameID]=$count;
		}
		return $games;
	}
	
	static function sendGameMessage($groupID)
	{	
		global $DB, $Game;
		require_once "lib/gamemessage.php";
		
		if ($Game->anon == 'Yes') {
			list($count) = $DB->sql_row(
				"SELECT count(m.id) FROM wD_Members m
					LEFT JOIN wD_Users u ON ( u.id = m.userID )
					LEFT JOIN wD_Games g ON ( g.id = m.gameID )
				WHERE g.id = ".$Game->id." AND u.rlGroup=".$groupID);
			$usersHTML=$count.' players ';
		} else {
			$usersHTML='';
			$sql = "SELECT u.id,u.username FROM wD_Users u
						LEFT JOIN wD_Members m ON ( u.id = m.userID )
						WHERE rlGroup=".$groupID." AND gameID=".$Game->id;
			$user_tabl= $DB->sql_tabl($sql);
			while (list ($id, $username) = $DB->tabl_row($user_tabl))
				$usersHTML .= '<a href="profile.php?userID='.$id.'">'.$username.'</a> ';
		}
		$msg = '<b>Attention!</b> '.$usersHTML.'know each other in Real Life.<br>This is not an issue provided that: everybody plays the best game that they can, has no pre-set alliances with their friend, and communicates within the game environment while playing.'; 
		libGameMessage::send(0, 'GameMaster', $msg);
	}
	
	static function sendGameMessages($groupID)
	{
		global $Game;
		$games = self::getCommonGames($groupID);
		
		foreach ($games as $gameID => $count)
		{
			$Variant=libVariant::loadFromGameID($gameID);
			$Game = $Variant->Game($gameID);
			self::sendGameMessage($groupID);
		}
	}
	
	static function addUserHTML($tag, $value)
	{
		return '
			<span id="reportButton">
				<a href="#" onclick="$(\'reportBox\').show(); $(\'reportButton\').hide(); return false;">-=> Add User <=-</a>
			</span>
			<span id="reportBox" style="display:none">
				<form method="post" style="display:inline;">
					<input type="hidden" name="'.$tag.'" value="'.$value.'" />
					Related UserID:<input size="5" name="addUserID" value="" />
					<input type="Submit" value="Submit" />
				</form>
			</span>';
	}
	
	static function notesHTML($groupID)
	{
		global $User, $DB;
		if( !$User->type['Moderator']) return;

		list($notes)=$DB->sql_row("SELECT note FROM wD_ModeratorNotes WHERE linkIDType='rlGroup' AND linkID=".$groupID);
		
		$html = '
		<b>Notes<span id="EditNoteButton"> (<a href="#" onclick="$(\'EditNoteBox\').show(); $(\'EditNoteText\').hide(); $(\'EditNoteButton\').hide(); return false;">Edit</a>)</span>:</b>
			<TABLE>
				<TD style="border: 1px solid #666">
				<span id="EditNoteText">'.$notes.'</span>
				<span id="EditNoteBox" style="display:none;">
					<form method="post" style="display:inline;">
						<input hidden name="groupID" value="'.$groupID.'">
						<textarea name="EditNote" style="width:100%;height:200px">'.str_ireplace("</textarea>", "<END-TA-DO-NOT-EDIT>", str_ireplace("<br />", "\n", $notes)).'</textarea><br />
						<input type="Submit" value="Submit" />
					</form>				
				</span>
				</TD>
				</TABLE>';
		return $html;
	}
	
	static function allUsersHTML($groupID)
	{
		global $DB, $User;
		
		$html = '';
		$users = self::getallUsers($groupID);
		
		if (count($users > 0))
		{
			$html = '<b>Members of this group:</b><TABLE>';

			if ($User->type['Moderator'])
				$html .=
				'<TFOOT>
					<TR style="border: 1px solid #666"><td colspan=2>
					'.self::addUserHTML("groupID", $groupID).'
					</TR>
				</TFOOT>';
										
			foreach ($users as $userID)
			{
				$groupUser = new User($userID);
				$html .= '<TR><TD style="border: 1px solid #666">'.$groupUser->profile_link().'</TD>';
				if ($User->type['Moderator'])
					$html .= '<TD style="border: 1px solid #666"><form method="post" style="display:inline;">
					<input hidden name="RemoveUserID" value="'.$groupUser->id.'">
					<input type="Submit" value="Remove" /></form></TD>';
				$html .= '</TR>';
			}
			$html .= '</TABLE>';
		}
		return $html;		
	}
	
	static function commonGamesHTML($groupID)
	{
		global $DB, $User;
		$html = '';
		
		$games = self::getCommonGames($groupID);
		if (count($games > 0))
		{
			$html = '
				<b>Common games (with players <b><u>not</u></b> in this group):</b>
				<TABLE>
					<THEAD>
						<TH style="border: 1px solid #000">Game</TH>
						<TH style="border: 1px solid #000">Players</TH>
						<TH style="border: 1px solid #000">Friends</TH>
						<TH style="border: 1px solid #000">Anon</TH>
						<TH style="border: 1px solid #000">rlPolicy</TH>
						<TH style="border: 1px solid #000">Press type</TH>
						<TH style="border: 1px solid #000">Password</TH>
					</THEAD>';
			
			if ($User->type['Moderator'])
				$html .=
				'<TFOOT>
					<TR style="border: 1px solid #666"><td colspan=5>
						<SPAN id="SendGameMessageButton">
							 <a href="#" onclick="$(\'SendGameMessageButtonConfirm\').show(); return false;">Send notification-message to all active games!</a>
						</SPAN>
						<SPAN id="SendGameMessageButtonConfirm" style="display:none;">
							<form method="post" style="display:inline;">
								<input type="hidden" name="GameNotify" value="'.$groupID.'" />
								<input type="Submit" value="-=> Confirm <=-" />
							</form>
						</SPAN>					
					</TR>
				</TFOOT>';
				
			foreach ($games as $gameID => $count)
			{				
				$Variant=libVariant::loadFromGameID($gameID);
				$Game = $Variant->Game($gameID);
				if ($Game->anon == 'No' || $User->type['Moderator'])
				{
					$html .= '<TR>';
					$html .= '<TR><TD style="border: 1px solid #666"><a href="board.php?gameID='.$Game->id.'">'.$Game->name.'</a></TD>';
					$html .= '<TD style="border: 1px solid #666">'.count($Variant->countries).'</TD>';
					$html .= '<TD style="border: 1px solid #666">'.$count.'</TD>';
					$html .= '<TD style="border: 1px solid #666">'.$Game->anon.'</TD>';
					$html .= '<TD style="border: 1px solid #666">'.$Game->rlPolicy.'</TD>';
					$html .= '<TD style="border: 1px solid #666">'.$Game->pressType.'</TD>';
					$html .= '<TD style="border: 1px solid #666">'.($Game->password != '' ? 'Yes':'No').'</TD>';
					$html .= '</TR>';
				}
			}
			$html .= '</TABLE>';			
		}
		return $html;
	}
	
	static function DisclaimerHTML()
	{
		return "
			<div style='font-weight:bold'><strong>For information about rlGroups and gamesettings check the <a href='features.php#faq_5'>features page</a>.</strong></div>
			<div class='hr'></div>
		";
	}
	
	static function reportsDisplay($userID)
	{
		global $User, $DB;

		if( !$User->type['Moderator'] && $User->id != $userID)
			return 'You can only view your own profile...';
		
		list($groupID, $username)=$DB->sql_row("SELECT rlGroup, username FROM wD_Users WHERE id=".$userID);

		$disclaimer = self::DisclaimerHTML();
		
		if ($groupID != 0)
		{
			$notes = self::notesHTML($groupID);
			$games = self::commonGamesHTML($groupID);
			$allusers = self::allUsersHTML($groupID);
			return '<b>'.$username.'</b> is in a RL usergroup.<br><br>'.$notes."<br>".$games."<br>".$allusers."<br>".$disclaimer;
		}
		else
			return 'No user relations exist for <b>'.$username.'</b>.<br><br>'.self::addUserHTML("userID", $userID)."<br>".$disclaimer;
	}

	static function checkRelationsChange()
	{
	
		global $User, $DB;

		if( !$User->type['Moderator'] ) return;

		// Send a notification to all games where 2 or more players of this group have joined...
		if (isset($_REQUEST['GameNotify']))
			self::sendGameMessages((int)$_REQUEST['GameNotify']);
		
		// Edit the note of the group
		if (isset($_REQUEST['EditNote']) && isset($_REQUEST['groupID']))
		{
			$notes=$DB->msg_escape($_REQUEST['EditNote'],false);
			$groupID=(int)$_REQUEST['groupID'];
			$DB->sql_put("DELETE FROM wD_ModeratorNotes WHERE linkIDType='rlGroup' AND linkID=".$groupID);			
			$DB->sql_put("INSERT INTO wD_ModeratorNotes SET 
				note='".$notes."',
				linkID=".$groupID.",
				timeSent=".time().",
				fromUserID='".$User->id."',
				linkIDType='rlGroup'");
		}

		// Add a new user to group or create new group
		if( isset($_REQUEST['addUserID']))
		{
			$adduserID=(int)$_REQUEST['addUserID'];
			
			$userID   =( isset($_REQUEST['userID']) ? (int)$_REQUEST['userID'] : 0);
			$groupID  =( isset($_REQUEST['groupID'])? (int)$_REQUEST['groupID']: 0);
			
			// Check if there is alread a relation-group of user 1
			if ($userID != 0)
			{
				list($userGroup)=$DB->sql_row("SELECT rlGroup FROM wD_Users WHERE id=".$userID);
				if ($userGroup > 0)
					$groupID = $userGroup;
			}
			
			// Check if there is alread a relation-group for the new user.
			list($adduserGroup)=$DB->sql_row("SELECT rlGroup FROM wD_Users WHERE id=".$adduserID);
			
			// Create a new group and put both in...
			if ($groupID == 0 && $adduserGroup == 0)
			{
				list($groupID)=$DB->sql_row("SELECT rlGroup from wD_Users ORDER BY rlGroup DESC LIMIT 1");
				$groupID++;
				$DB->sql_put('UPDATE wD_Users SET rlGroup="'.$groupID.'" WHERE id='.$userID.' OR id='.$adduserID);
			}
			
			// Add the first user to the group of the adduser
			elseif ($groupID == 0 && $adduserGroup != 0)
				$DB->sql_put('UPDATE wD_Users SET rlGroup="'.$adduserGroup.'" WHERE id='.$userID);
			// Add the adduser to the group of the first user
			elseif ($groupID != 0 && $adduserGroup == 0)
				$DB->sql_put('UPDATE wD_Users SET rlGroup="'.$groupID.'" WHERE id='.$adduserID);
				
			// Merge two groups
			elseif ($groupID != 0 && $adduserGroup != 0 && $groupID != $adduserGroup)
			{
				$DB->sql_put('UPDATE wD_Users SET rlGroup="'.$groupID.'" WHERE rlGroup='.$adduserGroup);
				list($notes)=$DB->sql_row("SELECT note FROM wD_ModeratorNotes WHERE linkIDType='rlGroup' AND linkID=".$groupID);
				list($notesAdd)=$DB->sql_row("SELECT note FROM wD_ModeratorNotes WHERE linkIDType='rlGroup' AND linkID=".$adduserGroup);
				$DB->sql_put("DELETE FROM wD_ModeratorNotes WHERE linkIDType='rlGroup' AND linkID=".$groupID);			
				$DB->sql_put("DELETE FROM wD_ModeratorNotes WHERE linkIDType='rlGroup' AND linkID=".$adduserGroup);			
				$DB->sql_put("INSERT INTO wD_ModeratorNotes SET 
					note='".$notes."<br />Group merge:<br />".$notesAdd."',
					linkID=".$groupID.",
					timeSent=".time().",
					fromUserID='".$User->id."',
					linkIDType='rlGroup'");
				
			}
		}
		
		// Delete a user from group
		if( isset($_REQUEST['RemoveUserID']) )
		{
			$id=(int)$_REQUEST['RemoveUserID'];
			
			list($groupID)  = $DB->sql_row('SELECT rlGroup FROM wD_Users WHERE id='.$id);
			list($userCount)= $DB->sql_row('SELECT count(*) FROM wD_Users WHERE rlGroup='.$groupID);
					
			if ($userCount < 3 )
			{
				$DB->sql_put('UPDATE wD_Users SET rlGroup=0 WHERE rlGroup='.$groupID);
				$DB->sql_put("DELETE FROM wD_ModeratorNotes WHERE linkIDType='rlGroup' AND linkID=".$groupID);			
			}
			else
				$DB->sql_put('UPDATE wD_Users SET rlGroup="0" WHERE id='.$id);
		}
	}
}