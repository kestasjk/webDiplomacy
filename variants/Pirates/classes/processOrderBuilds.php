<?php
/*
	Copyright (C) 2012 Gavin Atkinson / Oliver Auth

	This file is part of the Pirates variant for webDiplomacy

	The Pirates variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The Pirates variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class BuildAnywhere_processOrderBuilds extends processOrderBuilds
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

class Hurricane_processOrderBuilds extends BuildAnywhere_processOrderBuilds
{
	public function create()
	{
		global $DB, $Game;
		parent::create();
		
		// Get the terrid from the old hurricane
		list($old_hurricane)=$DB->sql_row("SELECT terrID FROM wD_Units
				WHERE (gameID=".$Game->id." 
				AND countryID=".(count($Game->Variant->countries) + 1).")");
				
		// And destoy if there is one.
		if ($old_hurricane > 0)
			$DB->sql_put("INSERT INTO wD_Orders
							(gameID, countryID, type, toTerrID)
							VALUES (".$Game->id.","
									.(count($Game->Variant->countries) + 1).","
									."'Destroy',"
									.$old_hurricane.")");
		
		// Get a free Territory (without an SC)
		list($new_hurricane) = $DB->sql_row("SELECT t.id FROM wD_Territories t
					LEFT JOIN wD_TerrStatus ts ON (t.id = ts.terrID && ts.gameID=".$Game->id.")
					WHERE t.mapID=".$Game->Variant->mapID." && ts.occupyingUnitID IS NULL && t.supply='No'
						&& t.id IN (SELECT fromTerrID AS id FROM wD_Borders WHERE mapID=".$Game->Variant->mapID.")
					ORDER BY RAND() LIMIT 1");
		
		// And put a hurricane on it.
		$DB->sql_put("INSERT INTO wD_Orders
							(gameID, countryID, type, toTerrID)
							VALUES (".$Game->id.","
									.(count($Game->Variant->countries) + 1).","
									."'Build Army',"
									.$new_hurricane.")");
		
	}
}

class CustomStart_processOrderBuilds extends Hurricane_processOrderBuilds
{
	protected $countryUnits = array(
		'Spain'   => array('Havana'      =>'Fleet', 'Voodoo Witch Hut'=>'Army','Panama'      =>'Fleet'),
		'England' => array('Spanish Town'=>'Fleet', 'St Kitts'        =>'Army','Barbados'    =>'Fleet'),
		'France'  => array('St Domingue' =>'Fleet', 'Guadeloupe'      =>'Army','Martinique'  =>'Fleet'),
		'Holland' => array('Curacao'     =>'Fleet', 'St Martin'       =>'Army','St Eustatius'=>'Fleet'),
		'Dunkirkers'          => array('Caracus'     =>'Fleet', 'Campeche'    =>'Army'),
		'Henry Morgan'        => array('Port Royal'  =>'Fleet', 'Providence'  =>'Army'),
		'Francois l Olonnais' => array('Tortuga'     =>'Fleet', 'Florida Keys'=>'Army'),
		'Isaac Rochussen'     => array('Mona Passage'=>'Fleet', 'Mid Atlantic'=>'Army','Southeast Caribbean Sea'=>'Fleet'),
		'The Infamous El Guapo' => 
			array('Northern Gulf of Mexico'=>'Fleet', 'Western Gulf of Mexico'=>'Army', 'Yuchatan Channel'=>'Army','Isla de los Pinos'=>'Fleet'),
		'Daniel "The Terror" Johnson' => 
			array('North Riff'=>'Fleet', 'Mayaguana Passage'=>'Army', 'Florida Channel'=>'Army', 'Tongue of the Ocean'=>'Fleet'),
		'Daniel "The Exterminator" Montbars' =>
			array('Bermuda Triangle'=>'Fleet', 'Virgin Islands'=>'Army', 'Crooked Island Passage'=>'Army', 'Northwest Channel'=>'Fleet'),
		'Bartolomeu "The Portuguese" de la Cueva' =>
			array('Gulf of Venezuela'=>'Fleet', 'Skeleton Bluff'=>'Army', 'Waters of the Spanish Main'=>'Army', 'Central Caribbean Sea'=>'Fleet'),
		'Roche "The Rock" Braziliano' =>
			array('South Caribbean Sea'=>'Fleet', 'Southwest Caribbean Sea'=>'Army', 'Northwest Caribbean Sea'=>'Army', 'Northeast Caribbean Sea'=>'Fleet'),					
	);

	public function create()
	{
		global $DB, $Game;
		if ($Game->turn == 0) {

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
			parent::create();
		}		
	}
}

class PiratesVariant_processOrderBuilds extends CustomStart_processOrderBuilds {}
