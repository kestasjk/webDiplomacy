<?php
/*
	Copyright (C) 2010 Carey Jensen / Kestas J. Kuliukas / Oliver Auth

	This file is part of the Chaos variant for webDiplomacy

	The Chaos variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Chaos variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	This file is a 1:1 copy with small adjustments from Kestas J. Kuliukas
	code for the Build Anywhere - Variant
	
*/

defined('IN_CODE') or die('This script can not be run by itself.');

// Split the Home-View after 9 Countries for a better readability.
class ClassicChaosVariant_panelMembersHome extends panelMembersHome
{

	function membersList()
	{
		global $User;
		
		// $membersList[$i]=array($nameOrCountry,$iconOne,$iconTwo,...);
		$membersList = array();
		
		if( $this->Game->phase == 'Pre-game')
		{
			$count=count($this->ByID);
			for($i=0;$i<$count;$i++)
				$membersList[]=array(($i+1),'<img src="images/icons/tick.png" alt=" " title="Player joined, spot filled" />');
			for($i=$count+1;$i<=count($this->Game->Variant->countries);$i++)
				$membersList[]=array(($i), '');
		}
		else
		{
			for($countryID=1; $countryID<=count($this->Game->Variant->countries); $countryID++)
			{
				$Member = $this->ByCountryID[$countryID];
				$membersList[] = $Member->memberColumn();
			}
		}
		
		$buf = '<table class="homeMembersTable">';
		$memberNum = count($membersList);
		$rowsCount=count($membersList[0]);
		$maxPerRow = 9;
		$rowsCount2 = ceil($memberNum / $maxPerRow); // No more than 10 countries per line, otherwise show half and half (or third and third and third... etc)
		$numPerLine = round($memberNum / $rowsCount2);
		$div = $maxPerRow;
		if ($rowsCount2 > 1) {
			$div = $memberNum / $rowsCount2;
		} else {
			$div = count($membersList);
		}
		$div = ceil($div);
		
		$alternate = libHTML::$alternate;
		for($j=0;$j<$rowsCount2;$j++)
		{
			for($i=0;$i<$rowsCount;$i++)
			{
					if ($i == 0 && $div % 2 == 0) libHTML::alternate();
				$rowBuf='';
				
				$dataPresent=false;
					
					$count = -1;
				foreach($membersList as $data)
				{
						$count++;
						if($count < $j*$div || $count >= ($j+1)*$div) {
							continue;
						}
					if($data[$i]) $dataPresent=true;
						else $data[$i] = '&nbsp;';
					$rowBuf .= '<td class="barAlt'.libHTML::alternate().'">'.$data[$i].'</td>';
				}
					if ($j == ($rowsCount2 - 1) && ($memberNum % $div) != 0) { // account for odd numbers
						$rowBuf .='<td class="barAlt'.libHTML::alternate().'">&nbsp;</td>';
					}
					if ($i+1 < $rowsCount && $div % 2 != 0 ) libHTML::alternate();
				if($dataPresent)
				{
					$buf .= '<tr>'.$rowBuf.'</tr>';
				}
			}
		}
		
		$buf .= '</table>';
		return $buf;
	}

}

