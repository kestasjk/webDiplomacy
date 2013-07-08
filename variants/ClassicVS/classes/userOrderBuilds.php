<?php
/*
	Copyright (C) 2011 Oliver Auth

	This file is part of the ClassicVS variant for webDiplomacy

	The ClassicVS variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The ClassicVS variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
	
*/

defined('IN_CODE') or die('This script can not be run by itself.');

// Use the turn-1 SC's instead of the predefined ones (countryID is different each game)
class CustomCountries_userOrderBuilds extends userOrderBuilds
{
	protected function toTerrIDCheck()
	{
		global $DB;
		
		// Don't duplicate destroy validation code
		if( $this->type != 'Build Army' && $this->type != 'Build Fleet' )
			return parent::toTerrIDCheck();
 
		if( $this->type == 'Build Army' )
		{
			return $this->sqlCheck(
				"SELECT ts.terrID 
					FROM wD_TerrStatus ts 
					INNER JOIN (
						SELECT tsa.terrID FROM wD_TerrStatusArchive tsa 
						INNER JOIN wD_Territories t 
							ON (tsa.terrID=t.id) 
						WHERE tsa.gameID=".$this->gameID." 
							AND t.mapID=".MAPID."
							AND tsa.countryID=".$this->countryID." 
							AND t.supply='Yes' AND NOT t.type='Sea'
							AND tsa.turn=0) AS t
					ON (t.terrID=ts.terrID) 
						WHERE ts.gameID=".$this->gameID." 
							AND t.terrID=".$this->toTerrID."
							AND ts.countryID=".$this->countryID."
							AND ts.occupyingUnitID IS NULL;");
		}
		elseif( $this->type == 'Build Fleet' )
		{
			return $this->sqlCheck(
				"SELECT IF(t.coast='Parent', coast.id, t.id) as terrID
					FROM wD_TerrStatus ts
					INNER JOIN wD_Territories t ON ( t.id = ts.terrID )						
					INNER JOIN (
						SELECT tsa.terrID FROM wD_TerrStatusArchive tsa 
						INNER JOIN wD_Territories t 
							ON (tsa.terrID=t.id) 
						WHERE tsa.gameID=".$this->gameID." 
							AND t.mapID=".MAPID."
							AND tsa.countryID=".$this->countryID." 
							AND t.supply='Yes' AND NOT t.type='Sea'
							AND tsa.turn=0) AS t
					ON (t.terrID=ts.terrID) 
					LEFT JOIN wD_Territories coast ON ( coast.mapID=".MAPID." AND coast.coastParentID = t.id AND NOT t.id = coast.id )
				WHERE ts.gameID = ".$this->gameID."
					AND t.mapID=".MAPID."
					AND ts.countryID = ".$this->countryID."
					AND ts.occupyingUnitID IS NULL
					AND t.supply = 'Yes'
					AND t.type = 'Coast'
					AND (
						(t.coast='Parent' AND coast.id=".$this->toTerrID.")
						OR t.id=".$this->toTerrID."
					)
					AND (
						t.coast='No' OR ( t.coast='Parent' AND NOT coast.id IS NULL )
					)");
		}		
	}
}

class ClassicVSVariant_userOrderBuilds extends CustomCountries_userOrderBuilds {}
