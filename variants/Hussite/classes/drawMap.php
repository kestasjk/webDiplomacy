<?php
/*
	Copyright (C) 2011 Milan Mach

	This file is part of the Hussite variant for webDiplomacy

	The Hussite variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Hussite variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of 
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class HussiteVariant_drawMap extends drawMap {

    	public function __construct($smallmap)
	{
		// LargeMap is too big, so up the memory-limit
		parent::__construct($smallmap);
		if ( !$this->smallmap )
			ini_set('memory_limit',"22M");
	}

	protected $countryColors = array(
		0 => array(226, 198, 158), // Neutral
		1 => array(176, 175, 174), // Bavaria
		2 => array(232, 232,  77), // Catholic Landfrieden
		3 => array(153, 104, 110), // Hungary
		4 => array(237, 135, 150), // Kingdom of Poland
		5 => array(140, 103,  76), // Margraviate of Brandenburg
		6 => array( 53, 130, 102), // Orebites
		7 => array(115, 123, 209), // Praguers
		8 => array(186, 171, 235), // Saxony
		9 => array(255, 173,  79), // Taborites
	);

	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map'=>'variants/Hussite/resources/smallmap.png',
				'army'=>'contrib/smallarmy.png',
				'fleet'=>'contrib/smallfleet.png',
				'names'=>'variants/Hussite/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map'=>'variants/Hussite/resources/map.png',
				'army'=>'contrib/army.png',
				'fleet'=>'contrib/fleet.png',
				'names'=>'variants/Hussite/resources/mapNames.png',
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
		{
			$colorRes = imageColorAllocate($image['image'], $r, $g, $b);
			if (!$colorRes)
				$colorRes = imageColorClosest($image['image'], $r, $g, $b);
		}
		
		return $colorRes; 
	}	
}

?>