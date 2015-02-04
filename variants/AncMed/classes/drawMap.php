<?php
/*
	Copyright (C) 2011 Oliver Auth

	This file is part of the the Ancient Mediterranean variant for webDiplomacy

	The Ancient Mediterranean variant for webDiplomacy is free software: 
	you can redistribute it and/or modify it under the terms of the GNU Affero
	General Public License as published by the Free Software Foundation, either 
	version 3 of the License, or (at your option) any later version.

    The Ancient Mediterranean variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class AncMedVariant_drawMap extends drawMap
{
	protected $countryColors = array(
		0 =>  array(226, 198, 158), // Neutral
		1 =>  array(121, 175, 198), // Carthage
		2 =>  array(234, 234, 175), // Egypt
		3 =>  array(164, 196, 153), // Greece
		4 =>  array(168, 126, 159), // Persia
		5 =>  array(196, 143, 133)  // Rome
	);

	// No need to set the transparency for our custom icons and mapnames.
	protected function setTransparancy(array $image, array $color=array(255,255,255)) {}
	
	protected function resources()
	{
		global $Variant;
		
		if( $this->smallmap ) {
			return array(
				'map'     =>'variants/'.$Variant->name.'/resources/smallmap.png',
				'army'    =>'variants/'.$Variant->name.'/resources/smallarmy.png',
				'fleet'   =>'variants/'.$Variant->name.'/resources/smallfleet.png',
				'names'   =>'variants/'.$Variant->name.'/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		} else {
			return array(
				'map'     =>'variants/'.$Variant->name.'/resources/map.png',
				'army'    =>'variants/'.$Variant->name.'/resources/army.png',
				'fleet'   =>'variants/'.$Variant->name.'/resources/fleet.png',
				'names'   =>'variants/'.$Variant->name.'/resources/mapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
	}
}

