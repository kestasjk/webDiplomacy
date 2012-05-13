<?php

class libRelations {
	static function reportBoxHTML($userID) {
		global $User;

		if( !$User->type['Moderator']) return '';

		return '<br>
		<span id="reportButton">
			<a href="#" onclick="$(\'reportBox\').show(); $(\'reportButton\').hide(); return false;">-=> Add User <=-</a>
		</span>
		<span id="reportBox" style="display:none">
			<form method="post" style="display:inline;">
				<input type="hidden" name="userID1" value="'.$userID.'" />
				Related UserID:<input size="5" name="userID2" value="" />
				<input type="Submit" value="Submit" />
			</form>
		</span>';
	}

	static function reportsDisplay($userID)
	{
		global $User, $DB;

		list($groupID)=$DB->sql_row("SELECT RLGroup FROM wD_Users WHERE id=".$userID);
		
		$html = '';
		if ($groupID != 0)
		{
			$sql = "SELECT id, username FROM wD_Users WHERE RLGroup=".$groupID.' AND id != '.$userID;
			$tabl = $DB->sql_tabl($sql);
			while(list($id, $username) = $DB->tabl_row($tabl))
				$html .= '- <a href="profile.php?userID='.$id.'">'.$username.'</a>
					<form method="post" style="display:inline;">
						<input hidden name="RemoveUserID" value="'.$id.'">
						<input type="Submit" value="Remove" />
					</form><br>';
		}
		
		if( $html )
		{
			list($notes)=$DB->sql_row("SELECT note FROM wD_ModeratorNotes WHERE linkIDType='RLGroup' AND linkID=".$groupID);
			$text = '
			Has RL friends on this site:<br>
			<br><b>Notes</b>
				<span id="EditNotes">
					 (<a href="#" onclick="$(\'EditNotesBox\').show(); $(\'EditNotes\').hide(); return false;">Edit</a>):<br>
					 '.$notes.'
				</span>
				<span id="EditNotesBox" style="display:none;">:<br>
					<form method="post" style="display:inline;">
						<input hidden name="groupID" value="'.$groupID.'">
						<textarea name="EditNote" style="width:80%;height:200px">'.$notes.'</textarea><br />
						<input type="Submit" value="Submit" />
					</form>				
				</span><br>
			<b>Users:<br></b>';			
			return $text.$html;
		}
		else
			return 'No user relations exist';

	}

	static function checkInsertNote() {
		global $User, $DB;

		if (isset($_REQUEST['EditNote']) && isset($_REQUEST['groupID']))
		{
			$notes=$DB->msg_escape($_REQUEST['EditNote'],false);
			$groupID=(int)$_REQUEST['groupID'];
			$DB->sql_put("DELETE FROM wD_ModeratorNotes WHERE linkIDType='RLGroup' AND linkID=".$groupID);			
			$DB->sql_put("INSERT INTO wD_ModeratorNotes SET 
				note='".$notes."',
				linkID=".$groupID.",
				linkIDType='RLGroup'");
		}

		if( !isset($_REQUEST['userID1'])||!isset($_REQUEST['userID2'])) return;

		$userID1=(int)$_REQUEST['userID1'];
		$userID2=(int)$_REQUEST['userID2'];
		
		// Check if there is alread a relation-group
		list($groupID1)=$DB->sql_row("SELECT RLGroup FROM wD_Users WHERE id=".$userID1);
		list($groupID2)=$DB->sql_row("SELECT RLGroup FROM wD_Users WHERE id=".$userID2);
		
		if ($groupID1 == 0 && $groupID2 == 0)
		{
			$DB->sql_put('INSERT INTO wD_RLGroup SET summary="",notes=""');
			$groupID = $DB->last_inserted();
			$DB->sql_put('UPDATE wD_Users SET RLGroup="'.$groupID.'" WHERE id='.$userID1.' OR id='.$userID2);
		}
		elseif ($groupID1 == 0 && $groupID2 != 0)
		{
			list($groupID)=$DB->sql_row("SELECT RLGroup FROM wD_Users WHERE id=".$userID2);
			$DB->sql_put('UPDATE wD_Users SET RLGroup="'.$groupID.'" WHERE id='.$userID1);
		}
		elseif ($groupID1 != 0 && $groupID2 == 0)
		{
			list($groupID)=$DB->sql_row("SELECT RLGroup FROM wD_Users WHERE id=".$userID1);
			$DB->sql_put('UPDATE wD_Users SET RLGroup="'.$groupID.'" WHERE id='.$userID2);
		}
		elseif ($groupID1 != 0 && $groupID2 != 0 && $groupID1 != $groupID2)
		{
			$DB->sql_put('UPDATE wD_Users SET RLGroup="'.$groupID1.'" WHERE RLGroup='.$groupID2);
		}
	}

	static function checkDeleteNote() {
		global $User, $DB;

		if( !$User->type['Moderator'] || !isset($_REQUEST['RemoveUserID']) ) return;

		$id=(int)$_REQUEST['RemoveUserID'];
		
		list($groupID)  = $DB->sql_row('SELECT RLGroup FROM wD_Users WHERE id='.$id);
		list($userCount)= $DB->sql_row('SELECT count(*) FROM wD_Users WHERE RLGroup='.$groupID);
				
		if ($userCount < 3 )
		{
			$DB->sql_put('UPDATE wD_Users SET RLGroup=0 WHERE RLGroup='.$groupID);
			$DB->sql_put("DELETE FROM wD_ModeratorNotes WHERE linkIDType='RLGroup' AND linkID=".$groupID);			
		}
		else
			$DB->sql_put('UPDATE wD_Users SET RLGroup="0" WHERE id='.$id);
	}
}