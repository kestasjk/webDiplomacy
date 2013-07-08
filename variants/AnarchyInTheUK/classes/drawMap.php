<?php
/*
	Copyright (C) 2012 Oliver Auth / Scordatura

	This file is part of the Anarchy in the UK variant for webDiplomacy

	The Anarchy in the UK variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Anarchy in the UK variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class AnarchyInTheUKVariant_drawMap extends drawMap
{
   protected $countryColors = array(
		0 => array(211, 249, 188),
		1 => array(239, 196, 228),
		2 => array(030, 144, 255),
		3 => array(185, 000, 000),
		5 => array(032, 162, 032),
		4 => array(240, 205, 041),
		6 => array(147, 112, 219),
	);

   protected function resources() {
      if( $this->smallmap )
      {
         return array(
            'map'     =>'variants/AnarchyInTheUK/resources/smallmap.png',
            'army'    =>'variants/AnarchyInTheUK/resources/smallarmy.png',
            'fleet'   =>'variants/AnarchyInTheUK/resources/smallfleet.png',
            'names'   =>'variants/AnarchyInTheUK/resources/smallmapNames.png',
            'standoff'=>'images/icons/cross.png'
         );
      }
      else
      {
         return array(
            'map'     =>'variants/AnarchyInTheUK/resources/map.png',
            'army'    =>'variants/AnarchyInTheUK/resources/army.png',
            'fleet'   =>'variants/AnarchyInTheUK/resources/fleet.png',
            'names'   =>'variants/AnarchyInTheUK/resources/mapNames.png',
            'standoff'=>'images/icons/cross.png'
         );
      }
   }

	// Draw the flags behind the units for a better readability
	public function countryFlag($terrName, $countryID)
	{
		$flagBlackback = $this->color(array(0, 0, 0));

		$flagColor = $this->color($this->countryColors[$countryID]);

		list($x, $y) = $this->territoryPositions[$terrName];

		$coordinates = array(
			'top-left' => array( 
				'x'=>$x-intval($this->fleet['width']/3+2)+1,
				'y'=>$y-intval($this->fleet['height']/3+2)+1
			),
			'bottom-right' => array(
				'x'=>$x+intval($this->fleet['width']/3+2)-1,
				'y'=>$y+intval($this->fleet['height']/3+2)-1
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
	
	// only set transparency if the color to make transparent exists
	protected function setTransparancy(array $image, array $color=array(255,255,255))
	{
 		$colorRes = imagecolorexact($image['image'], $color[0], $color[1], $color[2]);
		if ($colorRes != -1)
			parent::setTransparancy($image, $color);
	}
}
?>