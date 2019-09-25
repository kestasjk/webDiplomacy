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
		if($this->Game->phase == 'Pre-game')
		{
			$count = count($this->ByID);
			for ($i = 0; $i < $count; $i++)
				$membersList[] = array(($i + 1),'<img src="images/icons/tick.png" alt=" " title="Player joined, spot filled" />');
			for ($i = $count + 1; $i <= count($this->Game->Variant->countries); $i++)
				$membersList[] = array(($i), '');
		}
		else
		{
			for($countryID = 1; $countryID <= count($this->Game->Variant->countries); $countryID++)
			{
				$Member = $this->ByCountryID[$countryID];
				$membersList[] = $Member->memberColumn();
			}
		}
		// print countries with members > $maxPerRow on multiple rows on home page
		$buf = '<table class="homeMembersTable">';
		$memberNum = count($membersList);
		$maxPerRow = 7;
		$rowsCount = count($membersList[0]);
		$rowsCount2 = ceil($memberNum / $maxPerRow);
		$numPerLine = round($memberNum / $rowsCount2);
		$div = $maxPerRow;
		if ($rowsCount2 > 1) 
		{
			$div = $memberNum / $rowsCount2;
		} 
		else 
		{
			$div = count($membersList);
		}
		$div = ceil($div);
		$alternate = libHTML::$alternate;
		for ($j = 0; $j < $rowsCount2; $j++)
		{ 
			for ($i = 0; $i < $rowsCount; $i++)
			{
				if ($i == 0 && $div % 2 == 0) libHTML::alternate();
				$rowBuf = '';
				$dataPresent = false;
				$count = -1;
				$width = $memberNum / $maxPerRow;
				foreach($membersList as $data)
				{
					$count++;
					if($count < $j * $div || $count >= ($j + 1)*$div) 
					{
						continue;
					}
					if($data[$i]) 
					{
						$dataPresent = true;
					}
					else {
						$data[$i] = '&nbsp;';
					}
					$rowBuf .= '<td style="width:'.$width.'%;" class="barAlt'.libHTML::alternate().'">'.$data[$i].'</td>';
				}
				// account for odd numbers
				if ($j == ($rowsCount2 - 1) && ($memberNum % $div) != 0) 
				{
					$rowBuf .='<td style="display: none;" class="barAlt'.libHTML::alternate().'">&nbsp;</td>';
				}
				if ($i + 1 < $rowsCount && $div % 2 != 0 ) libHTML::alternate();
				if($dataPresent)
				{
					$buf .= '<tr class="homeMembersTableTr">'.$rowBuf.'</tr>';
				}
			}
		}
		$buf .= '</table>';
		return $buf;
	}
}
?>
