<?php
/*
    Copyright (C) 2013 Oliver Auth

	This file is part of vDiplomacy.

    vDiplomacy is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    vDiplomacy is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with webDiplomacy.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('IN_CODE') or die('This script can not be run by itself.');

$User->clearNotification('CountrySwitch');

if ( isset($_REQUEST['CancelSwitch']) )
{
	$switchID=(int)$_REQUEST['CancelSwitch'];
	list($gameID,$status,$toID, $fromID)=$DB->sql_row('SELECT gameID, status, toID, fromID FROM wD_CountrySwitch WHERE id='.$switchID);
	if ($status == 'Send' && $fromID == $User->id)
	{
		$DB->sql_put('UPDATE wD_CountrySwitch SET status="Canceled" WHERE id='.$switchID);
	}
}

if ( isset($_REQUEST['RejectSwitch']) )
{
	$switchID=(int)$_REQUEST['RejectSwitch'];
	list($gameID,$status,$toID, $fromID)=$DB->sql_row('SELECT gameID, status, toID, fromID FROM wD_CountrySwitch WHERE id='.$switchID);
	if ($status == 'Send' && $toID == $User->id)
	{
		$DB->sql_put('UPDATE wD_CountrySwitch SET status="Rejected" WHERE id='.$switchID);
		$DB->sql_put("UPDATE wD_Users SET notifications = CONCAT_WS(',',notifications, 'CountrySwitch') WHERE id = ".$fromID);
	}
}

if ( isset($_REQUEST['ClaimBackSwitch']) )
{
	$switchID=(int)$_REQUEST['ClaimBackSwitch'];
	list($gameID,$status,$toID, $fromID)=$DB->sql_row('SELECT gameID, status, toID, fromID FROM wD_CountrySwitch WHERE id='.$switchID);
	if ($status == 'Active' && $fromID == $User->id)
	{
		$DB->sql_put('UPDATE wD_CountrySwitch SET status="ClaimedBack" WHERE id='.$switchID);
		$DB->sql_put('UPDATE wD_Members SET userID='.$fromID.' WHERE gameID='.$gameID.' AND userID='.$toID);			
		$DB->sql_put("UPDATE wD_Users SET notifications = CONCAT_WS(',',notifications, 'CountrySwitch') WHERE id = ".$toID);
	}
}

if ( isset($_REQUEST['ReturnSwitch']) )
{
	$switchID=(int)$_REQUEST['ReturnSwitch'];
	list($gameID,$status,$toID, $fromID)=$DB->sql_row('SELECT gameID, status, toID, fromID FROM wD_CountrySwitch WHERE id='.$switchID);
	if ($status == 'Active' && $toID == $User->id)
	{
		$DB->sql_put('UPDATE wD_CountrySwitch SET status="Returned" WHERE id='.$switchID);
		$DB->sql_put('UPDATE wD_Members SET userID='.$fromID.' WHERE gameID='.$gameID.' AND userID='.$toID);			
		$DB->sql_put("UPDATE wD_Users SET notifications = CONCAT_WS(',',notifications, 'CountrySwitch') WHERE id = ".$fromID);
	}
}

if ( isset($_REQUEST['AcceptSwitch']) )
{
	$switchID=(int)$_REQUEST['AcceptSwitch'];
	list($gameID,$status,$toID, $fromID)=$DB->sql_row('SELECT gameID, status, toID, fromID FROM wD_CountrySwitch WHERE id='.$switchID);
	if ($status == 'Send' && $toID == $User->id)
	{
		list($ok)=$DB->sql_row('SELECT COUNT(*) FROM wD_Members WHERE gameID='.$gameID.' AND userID='.$User->id);
		if ($ok < 1)
		{
			$DB->sql_put('UPDATE wD_CountrySwitch SET status="Active" WHERE id='.$switchID);
			$DB->sql_put('UPDATE wD_Members SET userID='.$toID.' WHERE gameID='.$gameID.' AND userID='.$fromID);			
			$DB->sql_put("UPDATE wD_Users SET notifications = CONCAT_WS(',',notifications, 'CountrySwitch') WHERE id = ".$fromID);
		}
		else
		{
			$error = "You can't switch this country, you are already a member of that game.";
		}
	}
}

if ( isset($_REQUEST['newSwitch']) )
{
	$form  	= $_REQUEST['newSwitch'];
	if ( isset ($form['toID']) && $form['toID']>0 && isset ($form['gameID']))
	{
		$fromID = (int)$User->id;
		$toID   = (int)$form['toID'];
		$gameID = (int)$form['gameID'];
		$Variant=libVariant::loadFromGameID($gameID);
		$Game = $Variant->Game($gameID);
		
		try
		{
			$SendUser = new User($toID);
		}
		catch (Exception $e)
		{
			$error = l_t("Invalid user ID given.");
		}
		
		if (!isset($error))
		{
			// Check if there is a mute against a player
			list($muted) = $DB->sql_row("SELECT count(*) FROM wD_Members AS m
										LEFT JOIN wD_BlockUser AS f ON ( m.userID = f.userID )
										LEFT JOIN wD_BlockUser AS t ON ( m.userID = t.blockUserID )
									WHERE m.gameID = ".$Game->id." AND (f.blockUserID =".$SendUser->id." OR t.userID =".$SendUser->id.")");
									
			// Check for additional requirements:
			if ( $Game->minPhases > $SendUser->phasesPlayed)
				$error = 'The User you selected did not play enough phases to join this game.';
			elseif ( $Game->minRating > abs($SendUser->getReliability()))
				$error = 'The reliability of User you selected is not high enough to join this game.';
			elseif ( count($Variant->countries)>2 && $message = $SendUser->isReliable())
				$error = 'The User you selected can not join new games at the moment.';
			elseif ( array_key_exists ( $toID , $Game->Members->ByUserID))
				$error = 'The User you selected is already a member of this game.';
			elseif ( $muted > 0)
				$error = "The User you selected can't join. A player in this game has him muted or he muted a player in this game.";
			else
			{
				$DB->sql_put('INSERT INTO wD_CountrySwitch (fromID, toID, gameID, status) VALUES ('.
					$fromID.','.$toID.','.$gameID.', "Send")');
				$DB->sql_put("UPDATE wD_Users SET notifications = CONCAT_WS(',',notifications, 'CountrySwitch') WHERE id = ".$toID);
			}
		}
	}
}

?>
	<a name="Switch"></a>
	<form method="post"><ul class="formlist">
	<li class="formlisttitle">Countries given away:</li>
	<li class="formlistfield">
	<TABLE> <THEAD><TH>GameName</TH><TH>Send to</TH><TH>Send from</TH><TH>Status</TH><TH></TH></THEAD>
		<?php
		$sql='SELECT cs.id, g.name, g.id, cs.status, tu.username, tu.id FROM wD_Games g
				INNER JOIN wD_CountrySwitch cs ON (g.id = cs.gameID)
				INNER JOIN wD_Users tu ON (cs.toID = tu.id)
				WHERE (cs.status = "Send" OR cs.status = "Active") AND cs.fromID='.$User->id;
		$tabl = $DB->sql_tabl($sql);
		while(list($id,$gameName,$gameID,$status,$toName,$toID) = $DB->tabl_row($tabl))
		{
			print '<TR><TD><a href="board.php?gameID='.$gameID.'">'.$gameName.'</a></TD><TD><a href="profile.php?userID='.$toID.'">'.$toName.'</a></TD><TD>You</TD>';
			if ($status == "Send")
				print '<TD>Send</TD><TD><a href="usercp.php?tab=CountrySwitch&CancelSwitch='.$id.'"><img src="images/icons/cross.png"> (Cancel)</a></TD></TR>';
			elseif ($status == "Active")
				print '<TD><b>Active</b></TD><TD><a href="usercp.php?tab=CountrySwitch&ClaimBackSwitch='.$id.'"><img src="images/icons/cross.png"> (Claim back)</a></TD></TR>';
		}		
		$sql='SELECT cs.id, g.name, g.id, cs.status, tu.username, tu.id FROM wD_Games g
				INNER JOIN wD_CountrySwitch cs ON (g.id = cs.gameID)
				INNER JOIN wD_Users tu ON (cs.fromID = tu.id)
				WHERE (cs.status = "Send" OR cs.status = "Active") AND cs.toID='.$User->id;
		$tabl = $DB->sql_tabl($sql);
		while(list($id,$gameName,$gameID,$status,$toName,$toID) = $DB->tabl_row($tabl))
		{
			print '<TR><TD><a href="board.php?gameID='.$gameID.'">'.$gameName.'</a></TD><TD>You</TD><TD><a href="profile.php?userID='.$toID.'">'.$toName.'</a></TD>';
			if ($status == "Send")
				print '<TD>Send</TD><TD><a href="usercp.php?tab=CountrySwitch&AcceptSwitch='.$id.'"><img src="images/icons/tick.png"> (Accept)</a> - <a href="usercp.php?tab=CountrySwitch&RejectSwitch='.$id.'"><img src="images/icons/cross.png"> (Cancel)</a></TD></TR>';
			elseif ($status == "Active")
				print '<TD><b>Active</b></TD><TD><a href="usercp.php?tab=CountrySwitch&ReturnSwitch='.$id.'"><img src="images/icons/cross.png"> (Pass back)</a></TD></TR>';
		}
		?>
	</TABLE>
	</li>
	<li class="formlistdesc">All active switches.</li>

	<?php if (isset($error)) {?>
		<li class="formlisttitle">ERROR:</li>
		<li class="formlistfield"><?php print $error;?></li>
		<br>
	<?php }?>
	<li class="formlisttitle">Create new Country Switch:</li>
	<li class="formlistfield">
	<TABLE> <THEAD><TH>GameName / ID</TH><TH>Send to UserID</TH><TH> </TH></THEAD><TR>
	<TD><select name="newSwitch[gameID]">
		<?php
		$sql='SELECT m.gameID, g.name FROM wD_Members m
				INNER JOIN wD_Games g ON (g.id = m.gameID)
				WHERE g.id NOT IN (SELECT gameID FROM wD_CountrySwitch WHERE ((fromID='.$User->id.' OR toID='.$User->id.') AND status IN ("Send","Active"))) AND 
				g.phase!="Pre-game" AND m.status="Playing" AND m.userID='.$User->id;
		$tabl = $DB->sql_tabl($sql);
		while(list($gameID, $gameName) = $DB->tabl_row($tabl))
			print '<option value="'.$gameID.'">'.$gameName.'</option>';
		?>
	</select></TD>
	<TD><input type="text" name="newSwitch[toID]" size="5"><br></TD>
	<TD><input type="submit" class="form-submit notice" value="Submit"></TD></TR></TABLE>
	
	<li class="formlistdesc">Select the game you want to switch to another user. You can claim back your game at any time.</li>
</ul>

