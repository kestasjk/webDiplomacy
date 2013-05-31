<?php
/*
	Copyright (C) 2010 Carey Jensen / Oliver Auth

	This file is part of the Fall of the American Empire IV variant for webDiplomacy

	The Fall of the American Empire IV variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The Fall of the American Empire IV variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class Empire4Variant_drawMap extends drawMap {

	protected $countryColors = array(
		0 => array(226, 198, 158), /* Neutral          */
		1 => array(234, 234, 175), /* British-Columbia */
		2 => array(196, 143, 133), /* California       */
		3 => array(206, 153, 103), /* Florida          */
		4 => array( 64, 108, 128), /* Heartland        */
		5 => array(164, 196, 153), /* Mexico           */
		6 => array(168, 126, 159), /* New-York         */
		7 => array(239, 196, 228), /* Peru             */
		8 => array(114, 146, 103), /* Quebec           */
		9 => array(132, 130, 132), /* Texas            */
		10=> array(121, 175, 198)  /* Cuba             */
	);

	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map'     =>'variants/Empire4/resources/smallmap.png',
				'army'    =>'contrib/smallarmy.png',
				'fleet'   =>'contrib/smallfleet.png',
				'names'   =>'variants/Empire4/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map'     =>'variants/Empire4/resources/map.png',
				'army'    =>'contrib/army.png',
				'fleet'   =>'contrib/fleet.png',
				'names'   =>'variants/Empire4/resources/mapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
	}

	public function __construct($smallmap)
	{
		// Map is too big, so up the memory-limit
		parent::__construct($smallmap);
		if ( !$this->smallmap )
			ini_set('memory_limit',"32M");
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
	
	// Draw the flags behind the units for a better readability
	public function countryFlag($terrName, $countryID)
	{
		$flagBlackback = $this->color(array(0, 0, 0));

		$flagColor = $this->color($this->countryColors[$countryID]);

		list($x, $y) = $this->territoryPositions[$terrName];

		$coordinates = array(
			'top-left' => array( 
							'x'=>$x-intval($this->fleet['width']/2+2)+1,
							'y'=>$y-intval($this->fleet['height']/2+2)+1
							),
			'bottom-right' => array(
							'x'=>$x+intval($this->fleet['width']/2+2)-1,
							'y'=>$y+intval($this->fleet['height']/2+2)-1
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

}

?>