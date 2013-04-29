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
	
SELECT * FROM `wD_CountrySwitch` cs
JOIN wD_Games g ON (g.id=cs.gameID)
WHERE g.phase='Finished' && cs.status='Active'
	
 */

defined('IN_CODE') or die('This script can not be run by itself.');

class libSwitch
{

	static public function CancelSwitch($switchID)
	{
		global $User, $DB;
		list($gameID,$status,$toID, $fromID)=$DB->sql_row('SELECT gameID, status, toID, fromID FROM wD_CountrySwitch WHERE id='.$switchID);
		if ($status == 'Send' && $fromID == $User->id)
		{
			$DB->sql_put('UPDATE wD_CountrySwitch SET status="Canceled" WHERE id='.$switchID);
		}
	}

	static public function RejectSwitch($switchID)
	{
		global $User, $DB;
		list($gameID,$status,$toID, $fromID)=$DB->sql_row('SELECT gameID, status, toID, fromID FROM wD_CountrySwitch WHERE id='.$switchID);
		if ($status == 'Send' && $toID == $User->id)
		{
			$DB->sql_put('UPDATE wD_CountrySwitch SET status="Rejected" WHERE id='.$switchID);
			$DB->sql_put("UPDATE wD_Users SET notifications = CONCAT_WS(',',notifications, 'CountrySwitch') WHERE id = ".$fromID);
		}
	}

	static public function ClaimBackSwitch($switchID)
	{
		global $User, $DB;
		list($gameID,$status,$toID, $fromID)=$DB->sql_row('SELECT gameID, status, toID, fromID FROM wD_CountrySwitch WHERE id='.$switchID);
		if ($status == 'Active' && $fromID == $User->id)
		{
			$DB->sql_put('UPDATE wD_CountrySwitch SET status="ClaimedBack" WHERE id='.$switchID);
			$DB->sql_put('UPDATE wD_Members SET userID='.$fromID.' WHERE gameID='.$gameID.' AND userID='.$toID);			
			$DB->sql_put("UPDATE wD_Users SET notifications = CONCAT_WS(',',notifications, 'CountrySwitch') WHERE id = ".$toID);
		}
	}

	static public function ReturnSwitch($switchID)
	{
		global $User, $DB;
		list($gameID,$status,$toID, $fromID)=$DB->sql_row('SELECT gameID, status, toID, fromID FROM wD_CountrySwitch WHERE id='.$switchID);
		if ($status == 'Active' && $toID == $User->id)
		{
			$DB->sql_put('UPDATE wD_CountrySwitch SET status="Returned" WHERE id='.$switchID);
			$DB->sql_put('UPDATE wD_Members SET userID='.$fromID.' WHERE gameID='.$gameID.' AND userID='.$toID);			
			$DB->sql_put("UPDATE wD_Users SET notifications = CONCAT_WS(',',notifications, 'CountrySwitch') WHERE id = ".$fromID);
		}
	}

	static public function AcceptSwitch($switchID)
	{
		global $User, $DB;
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
	
	static public function clearAllSwitches(&$Game)
	{
		global $DB;
		$sql='SELECT id, fromID, toID FROM wD_CountrySwitch	WHERE status = "Active" AND gameID='.$Game->id;
		$tabl = $DB->sql_tabl($sql);
		while(list($id,$fromID,$toID) = $DB->tabl_row($tabl))
		{		
			$Switch = $Game->Members->ByUserID[$toID];

			unset($Game->Members->ByUserID[$Switch->userID]);
			unset($Game->Members->ByCountryID[$Switch->countryID]);
			unset($Game->Members->ByStatus[$Switch->status][$Switch->id]);

			$DB->sql_put("UPDATE wD_Members SET userID = ".$fromID." WHERE id =".$Switch->id);
			$DB->sql_put('UPDATE wD_CountrySwitch SET status="Returned" WHERE id='.$id);
			$Switch->userID = $fromID;

			$Game->Members->ByUserID[$Switch->userID] = $Switch;
			$Game->Members->ByUserID[$toID] = $Switch;
			$Game->Members->ByCountryID[$Switch->countryID] = $Switch;
			$Game->Members->ByStatus[$Switch->status][$Switch->id] = $Switch;
		}
	}

	static public function NewSwitch($formData)
	{
		global $User, $DB;
		$error = '';
		
		if ( isset ($formData['toID']) && $formData['toID']>0 && isset ($formData['gameID']))
		{
			$fromID = (int)$User->id;
			$toID   = (int)$formData['toID'];
			$gameID = (int)$formData['gameID'];
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
			
			if ($error == '')
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
		return $error;
	}

	static public function allSwitchesHTML($userID)
	{
		global $DB;
		
		$html = '<TABLE><THEAD><TH>GameName</TH><TH>Send to</TH><TH>Send from</TH><TH>Status</TH><TH></TH></THEAD>';
		
		$sql='SELECT cs.id, g.name, g.id, cs.status, tu.username, tu.id FROM wD_Games g
				INNER JOIN wD_CountrySwitch cs ON (g.id = cs.gameID)
				INNER JOIN wD_Users tu ON (cs.toID = tu.id)
				WHERE (cs.status = "Send" OR cs.status = "Active") AND cs.fromID='.$userID;
					
		$tabl = $DB->sql_tabl($sql);
		while(list($id,$gameName,$gameID,$status,$toName,$toID) = $DB->tabl_row($tabl))
		{
			$html .= '<TR> <TD><a href="board.php?gameID='.$gameID.'">'.$gameName.'</a></TD><TD><a href="profile.php?userID='.$toID.'">'.$toName.'</a></TD><TD>You</TD>';
			if ($status == "Send")
				$html .= '<TD>Send</TD> <TD><a href="usercp.php?tab=CountrySwitch&CancelSwitch='.$id.'"><img src="images/icons/cross.png"> ('.l_t('Cancel').')</a></TD> </TR>';
			elseif ($status == "Active")
				$html .= '<TD><b>Active</b></TD><TD><a href="usercp.php?tab=CountrySwitch&ClaimBackSwitch='.$id.'"><img src="images/icons/cross.png"> ('.l_t('Claim back').')</a></TD></TR>';
		}
		
		$sql='SELECT cs.id, g.name, g.id, cs.status, tu.username, tu.id FROM wD_Games g
				INNER JOIN wD_CountrySwitch cs ON (g.id = cs.gameID)
				INNER JOIN wD_Users tu ON (cs.fromID = tu.id)
				WHERE (cs.status = "Send" OR cs.status = "Active") AND cs.toID='.$userID;
		$tabl = $DB->sql_tabl($sql);
		while(list($id,$gameName,$gameID,$status,$toName,$toID) = $DB->tabl_row($tabl))
		{
			$html .= '<TR><TD><a href="board.php?gameID='.$gameID.'">'.$gameName.'</a></TD><TD>You</TD><TD><a href="profile.php?userID='.$toID.'">'.$toName.'</a></TD>';
			if ($status == "Send")
				$html .= '<TD>Send</TD><TD><a href="usercp.php?tab=CountrySwitch&AcceptSwitch='.$id.'"><img src="images/icons/tick.png"> ('.l_t('Accept').')</a> - <a href="usercp.php?tab=CountrySwitch&RejectSwitch='.$id.'"><img src="images/icons/cross.png"> (Cancel)</a></TD></TR>';
			elseif ($status == "Active")
				$html .= '<TD><b>Active</b></TD><TD><a href="usercp.php?tab=CountrySwitch&ReturnSwitch='.$id.'"><img src="images/icons/cross.png"> ('.l_t('Pass back').')</a></TD></TR>';
		}
		
		$html .= '</TABLE>';
		return $html;
			
	}
	
}
