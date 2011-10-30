<?php
/*
	Copyright (C) 2011 Carey Jensen / Kestas J. Kuliukas / Oliver Auth

	This file is part of the Chaoctopi variant for webDiplomacy

	The Chaoctopi variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Chaoctopi variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	This file is a 1:1 copy with small adjustments from Kestas J. Kuliukas
	code for the Build Anywhere - Variant
	
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class ClassicChaoctopiVariant_panelMembersHome extends panelMembersHome
{

	/**
	* Split the Home-View after 9 Countries for a better readability.
	**/
	function membersList()
	{
		$ret = parent::membersList();
		$ret = preg_replace ( '~ style="width:\d%"~' , '' , $ret);
		$parts = explode('</td>', $ret);

		$html=$part1=$part2='';
		for ($i=0; $i<count($this->Game->Variant->countries); $i++)
		{
			$part1.=$parts[$i].'</td>';
			$part2.=$parts[$i+34].'</td>';
			if (ceil(($i+1)/9) == (($i+1)/9))
			{
				$html .= $part1.'</tr><tr>'.$part2.'</tr><tr>';
				$part1=$part2='';
			}			
		}
		$html .= $part1.'</tr><tr>'.$part2.'</tr></table>';
		
		return $html;		
	}


}

?>
