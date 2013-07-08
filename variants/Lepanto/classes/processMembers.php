<?php
/*
	Copyright (C) 2010 Emmanuele Ravaioli and Oliver Auth

	This file is part of the Battle of Lepanto variant for webDiplomacy

	The Battle of Lepanto variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The Battle of Lepanto variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class LepantoVariant_processMembers extends processMembers {

	function checkForWinner()
	{
		global $DB;
		
		// a sucessfull move in a flagship-territory
		$tabl=$DB->sql_tabl(
			"SELECT countryID
			FROM wD_MovesArchive
			WHERE toTerrID IN ('11','13','86','88') AND type IN ('Move') AND success='Yes' AND gameID = ".$this->Game->id."
			ORDER BY countryID ASC");
			
		$won=0;
		while(list($winmove) = $DB->tabl_row($tabl))
			$won += $winmove;
			
		switch($won)
		{
			case '1':
				$this->ByCountryID[2]->setDefeated();
				return $this->ByCountryID[1];
			case '2':
				$this->ByCountryID[1]->setDefeated();
				return $this->ByCountryID[2];
			case '3':
				$DB->sql_put("UPDATE wD_Members SET votes='Draw' WHERE gameID=".$this->Game->id);
		}
		
		return false;
	}
	
}
?>
