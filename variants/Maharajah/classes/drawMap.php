<?php
/*
	Copyright (C) 2012 kaner406 / Oliver Auth

	This file is part of the Maharajah variant for webDiplomacy

	The Maharajah variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The Maharajah variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class MoveFlags_drawMap extends drawMap
{
	public function countryFlag($terrID, $countryID)
	{
		
		$flagBlackback = $this->color(array(0, 0, 0));
		$flagColor = $this->color($this->countryColors[$countryID]);

		list($x, $y) = $this->territoryPositions[$terrID];

		$coordinates = array(
			'top-left' => array( 
						 'x'=>$x-intval($this->fleet['width']/2+1),
						 'y'=>$y-intval($this->fleet['height']/2+1)
						 ),
			'bottom-right' => array(
						 'x'=>$x+intval($this->fleet['width']/2+1),
						 'y'=>$y+intval($this->fleet['height']/2-1)
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
class MaharajahVariant_drawMap extends MoveFlags_drawMap {

	protected $countryColors = array(
		0  => array(226, 198, 158), /* Neutral  */ 
		1  => array(239, 196, 228), /* Vijayanagar */
		2  => array(121, 175, 198), /* Gondwana  */
		3  => array(164, 196, 153), /* Persia   */
  		4  => array(160, 138, 117), /* Mughalistan */
  		5  => array(196, 143, 133), /* Delhi */
  		6  => array(234, 234, 175), /* Rajputana  */
  		7  => array(168, 126, 159), /* Bahmana  */
	);
	
	// No need to set the transparency for our custom icons and mapnames.
	protected function setTransparancy(array $image, array $color=array(255,255,255)) {}

	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map'     =>'variants/Maharajah/resources/smallmap.png',
				'army'    =>'variants/Maharajah/resources/smallarmy.png',
				'fleet'   =>'variants/Maharajah/resources/smallfleet.png',
				'names'   =>'variants/Maharajah/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map'     =>'variants/Maharajah/resources/map.png',
				'army'    =>'variants/Maharajah/resources/army.png',
				'fleet'   =>'variants/Maharajah/resources/fleet.png',
				'names'   =>'variants/Maharajah/resources/mapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
	}

}

?>