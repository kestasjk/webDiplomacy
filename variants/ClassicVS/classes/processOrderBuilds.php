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
class CustomCountries_processOrderBuilds extends processOrderBuilds
{
	public function create()
	{
		global $DB, $Game;
	
                $newOrders = array();
		foreach($Game->Members->ByID as $Member )
		{
			$difference = 0;
			if ( $Member->unitNo > $Member->supplyCenterNo )
			{
				$difference = $Member->unitNo - $Member->supplyCenterNo;
				$type = 'Destroy';
			}
			elseif ( $Member->unitNo < $Member->supplyCenterNo )
			{
				$difference = $Member->supplyCenterNo - $Member->unitNo;
				$type = 'Build Army';
				list($max_builds) = $DB->sql_row(
					"SELECT COUNT(*)
						FROM wD_TerrStatus ts
						INNER JOIN (
							SELECT tsa.terrID FROM wD_TerrStatusArchive tsa 
							INNER JOIN wD_Territories t 
								ON (tsa.terrID=t.id) 
							WHERE tsa.gameID=".$Game->id." 
								AND t.mapID=".$Game->Variant->mapID."
								AND tsa.countryID=".$Member->countryID." 
								AND t.supply='Yes' AND NOT t.type='Sea'
								AND tsa.turn=0 ) AS t
						ON ( t.terrID = ts.terrID )
					WHERE ts.gameID = ".$Game->id."
						AND ts.countryID = ".$Member->countryID."
						AND ts.occupyingUnitID IS NULL");							
				if ( $difference > $max_builds )
					$difference = $max_builds;
			}				
			for( $i=0; $i < $difference; ++$i )
				$newOrders[] = "(".$Game->id.", ".$Member->countryID.", '".$type."',NULL)";
		}
	
		if ( count($newOrders) )
			$DB->sql_put("INSERT INTO wD_Orders
							(gameID, countryID, type, toTerrID)
							VALUES ".implode(', ', $newOrders));
	}
}

class ClassicVSVariant_processOrderBuilds extends CustomCountries_processOrderBuilds {}
