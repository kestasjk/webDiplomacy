<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the WhoControlsAmericaV variant for webDiplomacy

	The WhoControlsAmericaV variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The WhoControlsAmerica variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of 
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class WhoControlsAmericaVariant_drawMap extends drawMap {

	/**
	 * An array of colors for different countries, indexed by countryID
	 * @var array
	 */
	protected $countryColors = array(
		0 => array(226, 198, 158), // Neutral
		1 => array(239, 196, 228), // Liberal Interests
		2 => array(121, 175, 198), // Republican Party
		3 => array(164, 196, 153), // The Military
		4 => array(160, 138, 117), // Corporate America
		5 => array(196, 143, 133), // Democratic Party
		6 => array( 64, 108, 128), // Conservative Interests
		7 => array(168, 126, 159), // The Underworld
		8 => array(234, 234, 175)  // Secret Societies
	);


	/**
	 * Resources, all required except names, which will be drawn on by the computer if not supplied.
	 * @return array[$resourceName]=$resourceLocation
	 */
	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map'=>'variants/WhoControlsAmerica/resources/smallmap.png',
				'army'=>'variants/WhoControlsAmerica/resources/small_patriot.png',
				'fleet'=>'contrib/smallfleet.png',
				'names'=>'variants/WhoControlsAmerica/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map'=>'variants/WhoControlsAmerica/resources/map.png',
				'army'=>'variants/WhoControlsAmerica/resources/small_patriot.png',
				'fleet'=>'contrib/fleet.png',
				'names'=>'variants/WhoControlsAmerica/resources/mapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
	}

	public function countryFlag($terrName, $countryID)
	{

		$flagBlackback = $this->color(array(0, 0, 0));
		$flagColor = $this->color($this->countryColors[$countryID]);

		list($x, $y) = $this->territoryPositions[$terrName];

		$coordinates = array(
			'top-left' => array( 
						 'x'=>$x-intval($this->army['width']/2+2),
						 'y'=>$y-intval($this->army['height']/2+1)
						 ),
			'bottom-right' => array(
						 'x'=>$x+intval($this->army['width']/2+2)-1,
						 'y'=>$y+intval($this->army['height']/2+2)
						 )
		);

		imagefilledrectangle($this->map['image'],
			$coordinates['top-left']['x'], $coordinates['top-left']['y'],
			$coordinates['bottom-right']['x'], $coordinates['bottom-right']['y'],
			$flagBlackback);
		imagefilledrectangle($this->map['image'],
			$coordinates['top-left']['x']+1, $coordinates['top-left']['y']+1,
			$coordinates['bottom-right']['x']-1, $coordinates['bottom-right']['y']-1,
			$flagColor);
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