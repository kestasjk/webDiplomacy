<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the 1897 variant for webDiplomacy

	The 1897 variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The 1897 variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
	
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class Classic1897Variant_processOrderBuilds extends processOrderBuilds {

	public function create()
	{
		global $DB, $Game;
		
		$set_sc_after_turn = 4;
		$newOrders = array();

		if ($Game->turn == 0) {

			$terrIDByName = array();
			$tabl = $DB->sql_tabl("SELECT id, name FROM wD_Territories WHERE mapID=".$Game->Variant->mapID);
			while(list($id, $name) = $DB->tabl_row($tabl))
				$terrIDByName[$name]=$id;

			$newOrders[] = "(".$Game->id.", ".$Game->Variant->countryID('England').", 'Build Fleet',".$terrIDByName['London']        .")"; 
			$newOrders[] = "(".$Game->id.", ".$Game->Variant->countryID('France') .", 'Build Army' ,".$terrIDByName['Paris']         .")"; 
			$newOrders[] = "(".$Game->id.", ".$Game->Variant->countryID('Italy')  .", 'Build Army' ,".$terrIDByName['Rome']          .")"; 
			$newOrders[] = "(".$Game->id.", ".$Game->Variant->countryID('Germany').", 'Build Army' ,".$terrIDByName['Berlin']        .")"; 
			$newOrders[] = "(".$Game->id.", ".$Game->Variant->countryID('Austria').", 'Build Army' ,".$terrIDByName['Vienna']        .")"; 
			$newOrders[] = "(".$Game->id.", ".$Game->Variant->countryID('Turkey') .", 'Build Army' ,".$terrIDByName['Constantinople'].")"; 
			$newOrders[] = "(".$Game->id.", ".$Game->Variant->countryID('Russia') .", 'Build Army' ,".$terrIDByName['Moscow']        .")";

		} else {
		
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
					if ($Game->turn > $set_sc_after_turn) {
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
										AND tsa.turn=".$set_sc_after_turn.") AS t
								ON ( t.terrID = ts.terrID )
								WHERE ts.gameID = ".$Game->id."
									AND ts.countryID = ".$Member->countryID."
									AND ts.occupyingUnitID IS NULL");							
						if ( $difference > $max_builds )
						{
							$difference = $max_builds;
						}
					}
				}
				
				for( $i=0; $i < $difference; ++$i )
				{
					$newOrders[] = "(".$Game->id.", ".$Member->countryID.", '".$type."',NULL)";
				}
			}
		}
		
		if ( count($newOrders) ) {
			$DB->sql_put("INSERT INTO wD_Orders
							(gameID, countryID, type, toTerrID)
							VALUES ".implode(', ', $newOrders));
		}
	}
}