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

class Classic1897Variant_drawMap extends drawMap {

	protected $countryColors = array(
		0 => array(226, 198, 158),
		1 => array(239, 196, 228),
		2 => array(121, 175, 198),
		3 => array(164, 196, 153),
		4 => array(160, 138, 117),
		5 => array(196, 143, 133),
		6 => array(234, 234, 175),
		7 => array(168, 126, 159)
	);

	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map'=>'variants/Classic1897/resources/smallmap.png',
				'army'=>'contrib/smallarmy.png',
				'fleet'=>'contrib/smallfleet.png',
				'names'=>'variants/Classic1897/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map'=>'variants/Classic1897/resources/map.png',
				'army'=>'contrib/army.png',
				'fleet'=>'contrib/fleet.png',
				'names'=>'variants/Classic1897/resources/mapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
	}
	
	/* In the 1st Diplomacy phase it shows the territory-status of the pre-game map,
	|  but the Territory-status changed, because all the territories without a build where
	|  made neutral.
	|  Color only the territories that have a owner to reflect these changes...
	*/   
	public $start_sc=array();
	public function __construct($smallmap){
		global $DB, $Game;
		parent::__construct($smallmap);
		if (isset($Game)) {
			if (($Game->turn==0) && ($Game->phase == 'Diplomacy')) {
				$tabl=$DB->sql_tabl('SELECT terrID from wD_TerrStatus WHERE GameID='.$Game->id);
				while( list($terr) = $DB->tabl_row($tabl))
					array_push($this->start_sc,$terr);
			}		
		}
	}
	public function colorTerritory($terrID, $countryID) {
		if ((count($this->start_sc) > 0) && (!(in_array($terrID,$this->start_sc,TRUE))))
			$countryID=0;
		parent::colorTerritory($terrID, $countryID);
	}

}

?>