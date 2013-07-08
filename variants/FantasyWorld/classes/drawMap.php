<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the Fantasy World variant for webDiplomacy

	The Fantasy World variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The Fantasy World variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class FantasyWorldVariant_drawMap extends drawMap {

	protected $countryColors = array(
		 0 => array(226, 198, 158), // Neutral 
		 1 => array(160, 138, 117), // Arafura 
		 2 => array(164, 196, 153), // Hamra 
		 3 => array(120, 120, 120), // Ishfahan
		 4 => array(239, 196, 228), // Jylland 
		 5 => array(136,  60,  60), // Kyushu
		 6 => array(114, 146, 103), // Lugulu
		 7 => array(234, 234, 175), // Ming-tao
		 8 => array(196, 143, 133), // New Foundland 
		 9 => array(121, 175, 198), // Orleans 
		10 => array(168, 126, 159), // Rajasthan 
		11 => array( 64, 108, 128), // Sakhalin
		12 => array(206, 153, 103)  // Valparaiso
	);

	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map'=>'variants/FantasyWorld/resources/smallmap.png',
				'army'=>'contrib/smallarmy.png',
				'fleet'=>'contrib/smallfleet.png',
				'names'=>'variants/FantasyWorld/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map'=>'variants/FantasyWorld/resources/map.png',
				'army'=>'contrib/army.png',
				'fleet'=>'contrib/fleet.png',
				'names'=>'variants/FantasyWorld/resources/mapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
	}
	
	protected function color(array $color, $image=false)
	{
		if ( ! is_array($image) )
		{
			$image = $this->map;
		}
		
		list($r, $g, $b) = $color;
		
		$colorRes = imagecolorexact($image['image'], $r, $g, $b);
		if ($colorRes == -1)
		{
			$colorRes = imageColorAllocate($image['image'], $r, $g, $b);
			if (!$colorRes)
				$colorRes = imageColorClosest($image['image'], $r, $g, $b);
		}
		
		return $colorRes; 
	}	
}

?>