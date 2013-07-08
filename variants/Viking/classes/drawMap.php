<?php
/*
	Copyright (C) 2011 kaner406 / Oliver Auth

	This file is part of the Viking variant for webDiplomacy

	The Viking variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The Viking variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class VikingVariant_drawMap extends drawMap {

	protected $countryColors = array(
		0  => array(226, 198, 158), /* Neutral              */
		1  => array(239, 196, 228), /* Arab Caliphates      */
		2  => array(114, 146, 103), /* Burgandy             */
		3  => array(121, 175, 198), /* Danmark              */
		4  => array(160, 138, 117), /* Eastern Roman Empire */
		5  => array(164, 196, 153), /* France               */
		6  => array(196, 143, 133), /* Slavic Nations       */
		7  => array(168, 126, 159), /* Norge                */
		8  => array(206, 153, 103), /* Sverige              */
		9  => array(234, 234, 175), /* Neutral units        */
	);
	
	// No need to set the transparency for our custom icons and mapnames.
	protected function setTransparancy(array $image, array $color=array(255,255,255)) {}
	
	protected function resources()
	{
		if( $this->smallmap ) {
			return array(
				'map'     =>'variants/Viking/resources/smallmap.png',
				'army'    =>'variants/Viking/resources/smallarmy.png',
				'fleet'   =>'variants/Viking/resources/smallfleet.png',
				'names'   =>'variants/Viking/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		} else {
			return array(
				'map'     =>'variants/Viking/resources/map.png',
				'army'    =>'variants/Viking/resources/army.png',
				'fleet'   =>'variants/Viking/resources/fleet.png',
				'names'   =>'variants/Viking/resources/mapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
	}
	
	public function countryFlag($terrID, $countryID)
	{
		$flagBlackback = $this->color(array(0, 0, 0));
		$flagColor = $this->color($this->countryColors[$countryID]);
		
		list($x, $y) = $this->territoryPositions[$terrID];

		$coordinates = array(
			'top-left' => array( 
						 'x'=>$x-intval($this->fleet['width']/2+2),
						 'y'=>$y-intval($this->fleet['height']/2+2)
						 ),
			'bottom-right' => array(
						 'x'=>$x+intval($this->fleet['width']/2+1),
						 'y'=>$y+intval($this->fleet['height']/2+2)
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