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

class LepantoVariant_userOrderBuilds extends userOrderBuilds
{

	protected function toTerrIDCheck()
	{
		global $DB;

		// Can't destroy flagships
		if ( $this->type == 'Destroy' )
		{
			if ( $this->toTerrID == 11 || $this->toTerrID == 13 || $this->toTerrID == 86 || $this->toTerrID == 88)
				return false;
			return parent::toTerrIDCheck();
		}	

		if( $this->type == 'Build Army' )
		{
			/*
			 * Creating an army at which territory
			 *
			 * Unoccupied supply centers owned by our country, which the specified unit type
			 * can be built in. If a parent coast is found return Child entries.
			 */
			return $this->sqlCheck("SELECT t.id
				FROM wD_TerrStatus ts
				INNER JOIN wD_Territories t
					ON ( t.id = ts.terrID )
				WHERE ts.gameID = ".$this->gameID."
					AND t.mapID=".MAPID."
					AND ts.countryID = ".$this->countryID."
					AND ts.occupyingUnitID IS NULL
					AND t.id=".$this->toTerrID."
					AND t.supply = 'Yes' AND NOT t.type='Sea'
					AND NOT t.coast = 'Child'");
		}
	}
}