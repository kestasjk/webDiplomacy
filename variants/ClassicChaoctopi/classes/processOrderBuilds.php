<?php
/*
	Copyright (C) 2011 Oliver Auth

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
		
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class ClassicChaoctopiVariant_processOrderBuilds extends processOrderBuilds
{

	// You can set default builds here, so the game is not screwed if someone misses his build
	protected $countryUnits = array(
			'Ankara'         => array('Ankara'         =>'Army'),
			'Belgium'        => array('Belgium'        =>'Army'),
			'Berlin'         => array('Berlin'         =>'Army'),
			'Brest'          => array('Brest'          =>'Army'),
			'Budapest'       => array('Budapest'       =>'Army'),
			'Bulgaria'       => array('Bulgaria'       =>'Army'),
			'Constantinople' => array('Constantinople' =>'Army'),
			'Denmark'        => array('Denmark'        =>'Army'),
			'Edinburgh'      => array('Edinburgh'      =>'Army'),
			'Greece'         => array('Greece'         =>'Army'),
			'Holland'        => array('Holland'        =>'Army'),
			'Kiel'           => array('Kiel'           =>'Army'),
			'Liverpool'      => array('Liverpool'      =>'Army'),
			'London'         => array('London'         =>'Army'),
			'Marseilles'     => array('Marseilles'     =>'Army'),
			'Moscow'         => array('Moscow'         =>'Army'),
			'Munich'         => array('Munich'         =>'Army'),
			'Naples'         => array('Naples'         =>'Army'),
			'Norway'         => array('Norway'         =>'Army'),
			'Paris'          => array('Paris'          =>'Army'),
			'Portugal'       => array('Portugal'       =>'Army'),
			'Rome'           => array('Rome'           =>'Army'),
			'Rumania'        => array('Rumania'        =>'Army'),
			'Serbia'         => array('Serbia'         =>'Army'),
			'Sevastopol'     => array('Sevastopol'     =>'Army'),
			'Smyrna'         => array('Smyrna'         =>'Army'),
			'Spain'          => array('Spain'          =>'Army'),
			'St-Petersburg'  => array('St. Petersburg' =>'Army'),
			'Sweden'         => array('Sweden'         =>'Army'),
			'Trieste'        => array('Trieste'        =>'Army'),
			'Tunis'          => array('Tunis'          =>'Army'),
			'Venice'         => array('Venice'         =>'Army'),
			'Vienna'         => array('Vienna'         =>'Army'),
			'Warsaw'         => array('Warsaw'         =>'Army')
	);

	public function create()
	{
		global $DB, $Game;
		if ($Game->turn == 0) {
			// Custom start
			$terrIDByName = array();
			$tabl = $DB->sql_tabl("SELECT id, name FROM wD_Territories WHERE mapID=".$Game->Variant->mapID);
			while(list($id, $name) = $DB->tabl_row($tabl))
				$terrIDByName[$name]=$id;

			$UnitINSERTs = array();
			foreach($this->countryUnits as $countryName => $params)
			{
				$countryID = $Game->Variant->countryID($countryName);

				foreach($params as $terrName=>$unitType)
				{
					$terrID = $terrIDByName[$terrName];
					$unitType = "Build " . $unitType;
					$UnitINSERTs[] = "(".$Game->id.", ".$countryID.", '".$terrID."', '".$unitType."')"; // ( gameID, countryID, terrID, type )
				}
			}
			$DB->sql_put(
				"INSERT INTO wD_Orders ( gameID, countryID, toTerrID, type )
				VALUES ".implode(', ', $UnitINSERTs)
			);		
		} else {
			// Build anywhere
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
				}
	
				for( $i=0; $i < $difference; ++$i )
				{
					$newOrders[] = "(".$Game->id.", ".$Member->countryID.", '".$type."')";
				}
			}
	
			if ( count($newOrders) )
			{
				$DB->sql_put("INSERT INTO wD_Orders
								(gameID, countryID, type)
								VALUES ".implode(', ', $newOrders));
			}
	
		}
		
	}


}

?>
