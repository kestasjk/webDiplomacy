<?php
/*
	Copyright (C) 2011 Oliver Auth

	This file is part of the GreekDip variant for webDiplomacy

	The GreekDip variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The GreekDip variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class BiddingStart_processOrderBuilds extends processOrderBuilds
{
	public $init_build = array (
		'Apollonia'=> 'Army', 'Delphi'  => 'Army', 'Thebes'  => 'Army', 'Olympia'      => 'Army',
		'Messenia' => 'Army', 'Sparta'  => 'Army', 'Argos'   => 'Army', 'Larissa'      => 'Army',
		'Pella'    => 'Army', 'Therma'  => 'Army', 'Thassos' => 'Army', 'Byzantinum'   => 'Army', 
		'Pergamum' => 'Army', 'Ephesus' => 'Army', 'Miletus' => 'Army', 'Halicarnassus'=> 'Army', 
		'Sardis'   => 'Army', 'Laodicoa'=> 'Army', 'Athens'  => 'Fleet','Chalies'      => 'Fleet',
		'Chicos'   => 'Fleet','Concyra' => 'Fleet','Samos'   => 'Fleet','Cydonia'      => 'Fleet',
		'Delos'    => 'Fleet','Ilium'   => 'Fleet','Imbros'  => 'Fleet','Ithaca'       => 'Fleet', 
		'Knossos'  => 'Fleet','Lesbos'  => 'Fleet','Melos'   => 'Fleet','Naxos'        => 'Fleet',
		'Rhodes'   => 'Fleet','Corinth (East Coast)'=> 'Fleet'
	);
	
	public function create()
	{
		global $DB, $Game;
		
		if ($Game->turn == 1)
		{
			$occupiedTerr = array();
			$tabl = $DB->sql_tabl("SELECT terrID, countryID FROM wD_TerrStatus WHERE countryID != 0 AND gameID = ".$Game->id);
			while( list($terrID, $countryID) = $DB->tabl_row($tabl))
				$occupiedTerr[$terrID]=$countryID;

			$terrIDByName = array();
			$tabl = $DB->sql_tabl("SELECT id, name FROM wD_Territories WHERE mapID=".$Game->Variant->mapID);
			while(list($id, $name) = $DB->tabl_row($tabl))
				$terrIDByName[$name]=$id;
				
			$UnitINSERTs = array();
			foreach( $this->init_build as $terrName => $unitType )
			{
				$terrID = $terrIDByName[$terrName];
				if ( isset ($occupiedTerr[$Game->Variant->deCoast($terrID)]))
					$UnitINSERTs[] = "(".$Game->id.", ".$occupiedTerr[$Game->Variant->deCoast($terrID)].", 'Build ".$unitType."', ".$terrID.")";
			}
			
			$DB->sql_put("INSERT INTO wD_Orders	(gameID, countryID, type, toTerrID)	VALUES ".implode(', ', $UnitINSERTs));
		}
		else
		{
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
									AND tsa.turn=1 ) AS t
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
}

class GreekDipVariant_processOrderBuilds extends BiddingStart_processOrderBuilds {}
