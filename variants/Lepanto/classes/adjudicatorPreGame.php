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

class LepantoVariant_adjudicatorPreGame extends adjudicatorPreGame {

	protected $countryUnits = array(
		'Holy League'	=> array('B 3' =>'Army','B 5' =>'Army','D 2' =>'Army','D 3' =>'Army','D 5' =>'Army','D 6' =>'Army','E 2' =>'Fleet','E 7' =>'Fleet'),
		'Ottoman Empire'=> array('L 3' =>'Army','L 5' =>'Army','J 2' =>'Army','J 3' =>'Army','J 5' =>'Army','J 6' =>'Army','I 2' =>'Fleet','I 7' =>'Fleet')
	);
	
	// Load the neutral territories with starting units in the database
	protected function assignTerritories() {
		global $DB, $Game;

		$terrid=array();
		foreach (array(25,26,28,29,32,37,62,67,70,71,73,74) as $i)
			$terrid[] = "(".$Game->id.", '0', '".$i."')";
		
		$DB->sql_put("INSERT INTO wD_TerrStatus
						( gameID, countryID, terrID )
						VALUES ".implode(', ', $terrid));
						
		parent::assignTerritories();
	}

}
