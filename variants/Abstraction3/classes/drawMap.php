<?php
/*
	Copyright (C) 2012 Oliver Auth

	This file is part of the Abstraction III variant for webDiplomacy

	The Abstraction III variant for webDiplomacy is free software:
	you can redistribute it and/or modify it under the terms of the GNU Affero
	General Public License as published by the Free Software Foundation, either
	version 3 of the License, or (at your option) any later version.

	The Abstraction III variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
	
	If you have questions or suggestions send me a mail: Oliver.Auth@rhoen.de
	
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class Abstraction3Variant_drawMap extends drawMap {

	protected $countryColors = array(
		0  => array(226, 198, 158), // Neutral
 		1  => array(196, 143, 133), // Austria
		2  => array(121, 175, 198), // France
  		3  => array(160, 138, 117), // Germany
		4  => array(164, 196, 153), // Italy
 		5  => array(239, 196, 228), // Britain
  		6  => array(168, 126, 159), // Russia
  		7  => array(234, 234, 175), // Turkey
	);

	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map'     =>'variants/Abstraction3/resources/smallmap.png',
				'army'    =>'contrib/smallarmy.png',
				'fleet'   =>'contrib/smallfleet.png',
				'names'   =>'variants/Abstraction3/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map'     =>'variants/Abstraction3/resources/map.png',
				'army'    =>'contrib/army.png',
				'fleet'   =>'contrib/fleet.png',
				'names'   =>'variants/Abstraction3/resources/mapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
	}

}

?>