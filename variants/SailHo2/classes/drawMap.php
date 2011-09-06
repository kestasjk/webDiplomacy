<?php
/*
	Copyright (C) 2010 Carey Jensen / Oliver Auth

	This file is part of the Sail Ho II variant for webDiplomacy

	The Sail Ho II variant for webDiplomacy" is free software: you can 
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either
	version 3 of the License, or (at your option) any later version.

	The Sail Ho II variant for webDiplomacy is distributed in the hope that it
	will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy.  If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class SailHo2Variant_drawMap extends drawMap {

	protected $countryColors = array(
		0 => array(226, 198, 158), // Neutral
		1 => array(164, 196, 153), // East
		2 => array(239, 196, 228), // North
		3 => array(121, 175, 198), // South
		4 => array(234, 234, 175), // West
	);

	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map'     =>'variants/SailHo2/resources/smallmap.png',
				'army'    =>'variants/SailHo2/resources/smallarmy.png',
				'fleet'   =>'variants/SailHo2/resources/smallfleet.png',
				'names'   =>'variants/SailHo2/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map'     =>'variants/SailHo2/resources/map.png',
				'army'    =>'variants/SailHo2/resources/army.png',
				'fleet'   =>'variants/SailHo2/resources/fleet.png',
				'names'   =>'variants/SailHo2/resources/mapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
	}

}

?>