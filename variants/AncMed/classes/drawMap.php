<?php
/*
	Copyright (C) 2010 Oliver Auth

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

class AncMedVariant_drawMap extends drawMap {

	protected $countryColors = array(
		0 =>  array(226, 198, 158), // Neutral
		1 =>  array(121, 175, 198), // Carthage
		2 =>  array(234, 234, 175), // Egypt
		3 =>  array(164, 196, 153), // Greece
		4 =>  array(168, 126, 159), // Persia
		5 =>  array(196, 143, 133)  // Rome
	);

	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map'     =>'variants/AncMed/resources/smallmap.png',
				'army'    =>'contrib/smallarmy.png',
				'fleet'   =>'contrib/smallfleet.png',
				'names'   =>'variants/AncMed/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map'     =>'variants/AncMed/resources/map.png',
				'army'    =>'contrib/army.png',
				'fleet'   =>'contrib/fleet.png',
				'names'   =>'variants/AncMed/resources/mapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
	}
	
	protected function color(array $color, $image=false)
	{

		if ( ! is_array($image) )
			$image = $this->map;

		list($r, $g, $b) = $color;

		$colorRes = imagecolorexact($image['image'], $r, $g, $b);
		if ($colorRes == -1)
			$colorRes = imageColorAllocate($image['image'], $r, $g, $b);

 		return $colorRes;
 	}
	
}

?>