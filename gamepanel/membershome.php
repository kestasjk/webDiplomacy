<?php
/*
    Copyright (C) 2004-2010 Kestas J. Kuliukas

	This file is part of webDiplomacy.

    webDiplomacy is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    webDiplomacy is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with webDiplomacy.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('IN_CODE') or die('This script can not be run by itself.');

require_once(l_r('gamepanel/memberhome.php'));
/**
 * This class displays the members subsection of a game panel in a homepage context.
 *
 * @package GamePanel
 */
class panelMembersHome extends panelMembers
{
	/**
	 * Load a panelMemberHome instead of a panelMember
	 */
	protected function loadMember(array $row)
	{
		return $this->Game->Variant->panelMemberHome($row);
	}

	/**
	 * Display a table with the vital members info; who is finalized, who has sent messages etc, each member
	 * takes up a short, thin column.
	 * @return string
	 */
	function membersList()
	{
		global $User;

		// $membersList[$i]=array($nameOrCountryID,$iconOne,$iconTwo,...);
		$membersList = array();

		if( $this->Game->phase == 'Pre-game')
		{
			$count=count($this->ByID);
			for($i=0;$i<$count;$i++)
				$membersList[]=array(($i+1),'<img src="'.l_s('images/icons/tick.png').'" alt=" " title="'.l_t('Player joined, spot filled').'" />');
			for($i=$count;$i<=count($this->Game->Variant->countries);$i++)
				$membersList[]=array(($i+1), '');
		}
		else
		{
			for($countryID=1; $countryID<=count($this->Game->Variant->countries); $countryID++)
			{
				$Member = $this->ByCountryID[$countryID];

				//if ( $User->id == $this->ByCountryID[$countryID]->userID )
				//	continue;
				//elseif( $Member->status != 'Playing' && $Member->status != 'Left' )
				//	continue;

				$membersList[] = $Member->memberColumn();
			}
		}

		$buf = '<table class="homeMembersTable">';
		$rowsCount=count($membersList[0]);

		$alternate = libHTML::$alternate;
		for($i=0;$i<$rowsCount;$i++)
		{
			$rowBuf='';

			$dataPresent=false;
			$remainingPlayers=count($this->ByID);
			$remainingWidth=100;
			foreach($membersList as $data)
			{
				if($data[$i]) $dataPresent=true;

				if( $remainingPlayers>1 )
					$width = floor($remainingWidth/$remainingPlayers);
				else
					$width = $remainingWidth;

				$remainingPlayers--;
				$remainingWidth -= $width;

				$rowBuf .= '<td style="width:'.$width.'%" class="barAlt'.libHTML::alternate().'">'.$data[$i].'</td>';
			}
			libHTML::alternate();
			if($dataPresent)
			{
				$buf .= '<tr>'.$rowBuf.'</tr>';
			}

			libHTML::$alternate = $alternate;
		}
		libHTML::alternate();

		$buf .= '</table>';
		return $buf;


	}
}
?>