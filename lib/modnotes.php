<?php

class libModNotes {
	static function reportBoxHTML($linkIDType, $linkID) {
		global $User;

		if( !$User->type['Moderator']&&$linkIDType=='User'&&$linkID==$User->id ) return '';

		if( $User->muteReports=='Yes' ) return '';

		return '<input id="reportButton" type="button" value="'.($User->type['Moderator']?'Note':'Report').'" onclick="$(\'reportBox\').show();$(\'reportButton\').hide();" />
		<div id="reportBox" style="display:none"><input id="reportButton" type="button" value="Hide" onclick="$(\'reportButton\').show();$(\'reportBox\').hide();" />
		<form onsubmit="return confirm(\'Are you sure you want to submit this message?\');" method="post">
		<input type="hidden" name="linkIDType" value="'.$linkIDType.'" />
		<input type="hidden" name="linkID" value="'.$linkID.'" />'.
		($linkIDType=='Game'?'<input type="hidden" name="gameID" value="'.$linkID.'" /><input type="hidden" name="viewArchive" value="Reports" />':''). // For profile.php, to remain viewing reports
		($linkIDType=='User'?'<input type="hidden" name="userID" value="'.$linkID.'" /><input type="hidden" name="detail" value="reports" />':''). // For profile.php, to remain viewing reports
		($User->type['Moderator']?'Note type: <select name="type"><option value="Report">Report</option><option value="PublicNote">Public note</option><option value="PrivateNote">Private note</option></select><br />':'<input type="hidden" name="type" value="Report" />').'
		<textarea name="note" style="width:80%;height:600px"></textarea><br />
		<input type="Submit" value="Submit" />
		</form>
		</div>';
	}

	static function checkInsertNote() {
		global $User, $DB;

		if( !isset($_REQUEST['linkIDType'])||!isset($_REQUEST['linkID'])||!isset($_REQUEST['note'])||!isset($_REQUEST['type']) ) return;

		if( $User->muteReports=='Yes' ) return;

		$linkIDType=$_REQUEST['linkIDType'];
		if( $linkIDType!='User'&&$linkIDType!='Game' )
			throw new Exception("Invalid linkIDType '".$linkIDType."'");

		$linkID=(int)$_REQUEST['linkID'];

		$note=$DB->msg_escape($_REQUEST['note'],false);
		$type=$_REQUEST['type'];
		if($type!='Report'&&(!$User->type['Moderator']||($type!='PrivateNote'&&$type!='PublicNote')))
			throw new Exception("Invalid note type '".$type."'");

		$DB->sql_put("INSERT INTO wD_ModeratorNotes (linkIDType, linkID, `type`, fromUserID, note, timeSent)
			VALUES ('".$linkIDType."',".$linkID.",'".$type."',".$User->id.",'".$note."',".time().")");

		if( $User->type['Moderator'] )
			libHTML::notice('Note posted', "Your note has been posted.");
		else
			libHTML::notice('Report sent', "Your report has been posted, and will be dealt with by a moderator soon. Thanks for your patience.");
	}

	static function reportsDisplay($linkIDType, $linkID=0) {
		global $User, $DB;

		if($linkIDType!='User'&&$linkIDType!='Game'&&$linkIDType!='All')
			throw new Exception("Invalid note link-ID-type given: '".$linkIDType."', only User/Game allowed");

		$linkID=(int)$linkID;

		$sql = "SELECT m.linkIDType, m.linkID, m.fromUserID, u.username, m.type, m.note, m.timeSent FROM wD_ModeratorNotes m INNER JOIN wD_Users u ON ( u.id = m.fromUserID ) WHERE 1=1 ";
		if( !$User->type['Moderator'] )
			$sql .= "AND ( m.type='Report' OR m.type='PublicNote' )";

		if( $linkIDType!='All'||!$User->type['Moderator'] )
			$sql .= "AND m.linkIDType='".$linkIDType."' AND m.linkID=".$linkID;

		$sql .=" ORDER BY m.timeSent DESC LIMIT ".($linkIDType=='All'?'50':'5');
		$tabl=$DB->sql_tabl($sql);

		$html = '';
		while($note=$DB->tabl_hash($tabl)) {
			$html .= '<tr>
				'.($linkIDType=='All'?'<td><a href="'.($note['linkIDType']=='User'?'profile.php?userID='.$note['linkID'].'">Go to user</a>':'board.php?gameID='.$note['linkID'].'">Go to game</a>').'</td>':'').'
				'.($User->type['Moderator']?'<td><a href="?modNoteDelete='.$note['linkIDType'].'_'.$note['linkID'].'_'.$note['timeSent'].'">Delete</a></td>':'').'
				<td><a href="profile.php?userID='.$note['fromUserID'].'">'.$note['username'].'</a></td>
				<td>'.libTime::text($note['timeSent']).'</td>
				<td>'.$note['type'].'</td>
				<td>'.$note['note'].'</td>
				</tr>';
		}

		if( $html )
			return '<table><tr>'.($linkIDType=='All'?'<th>Link</th>':'').($User->type['Moderator']?'<th>Control</th>':'').'<th>From</th><th>Sent</th><th>Type</th><th>Text</th></tr>'.$html.'</table>';
		else {
			switch($linkIDType) {
				case 'User': return 'No reports/notes exist for this user.';
				case 'Game': return 'No reports/notes exist for this game.';
				case 'All': return 'No reports/notes exist';
				default: return 'Unknown type "'.$linkIDType.'"';
			}
		}
	}

	static function checkDeleteNote() {
		global $User, $DB;

		if( !$User->type['Moderator'] || !isset($_REQUEST['modNoteDelete']) ) return;

		$params = explode('_',$_REQUEST['modNoteDelete']);
		if( count($params)!=3 || ($params[0]!='User'&&$params[0]!='Game') )
			throw new Exception("Invalid mod-note deletion command given");

		list($linkIDType, $linkID, $timeSent)=$params;
		$linkID=(int)$linkID;
		$timeSent=(int)$timeSent;
		$DB->sql_put("DELETE FROM wD_ModeratorNotes WHERE linkIDType='".$linkIDType."' AND linkID=".$linkID." AND timeSent=".$timeSent);

		libHTML::notice('Deleted', 'Moderator note successfully deleted.');
	}
}