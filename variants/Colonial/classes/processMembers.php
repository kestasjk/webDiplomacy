<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the Colonial variant for webDiplomacy

	The Colonial variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Colonial variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of 
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class ColonialVariant_processMembers extends processMembers {

	function countUnitsSCs()
	{
		parent::countUnitsSCs();

		// After the Support-Center-Counter got updated check if HongKong is occupied by china.
		// If that's true subtract 1 for Chinas total SC's
		
		global $DB;
		list($owner_hk)=$DB->sql_row("SELECT countryID 
			FROM wD_TerrStatus
			WHERE (gameID=".$this->Game->id." AND terrID=9)");
		if ($owner_hk == 2) 
		{
			$this->ByCountryID[2]->supplyCenterNo--;
			$DB->sql_put("UPDATE wD_Members 
				SET	supplyCenterNo = ". $this->ByCountryID[2]->supplyCenterNo ."
				WHERE (gameID = ".$this->Game->id." AND countryID = 2)");
		};
	
	}	

}

?>
