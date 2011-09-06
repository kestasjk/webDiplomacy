<?php
/*
	Copyright (C) 2011 Oliver Auth

	This file is part of the Sengoku5 variant for webDiplomacy

	The Sengoku5 variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Sengoku5 variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
	
*/

defined('IN_CODE') or die('This script can not be run by itself.');

// Draw the flags behind the units for a better readability
class MoveFlags_drawMap extends drawMap
{
	public function countryFlag($terrID, $countryID)
	{
		list($x, $y) = $this->territoryPositions[$terrID];
		$this->territoryPositions[0] = array($x,$y+$this->fleet['width']/2-1);
		$save = $this->fleet;
		$this->fleet = array('width'=>$this->fleet['width']*1.3, 'height'=>$this->fleet['height']*1.3);
		parent::countryFlag(0, $countryID);
		$this->fleet = $save;
	}
}

class Sengoku5Variant_drawMap extends MoveFlags_drawMap {

	protected $countryColors = array(
		0 => array(226, 198, 158), /* Neutral   */
		1 => array(164, 196, 153), /* Shimazu   */
		2 => array(121, 175, 198), /* Mori      */
		3 => array(239, 196, 228), /* Chosokabe */
		4 => array(206, 153, 103), /* Asakura   */
		5 => array(196, 143, 133), /* Oda       */
		6 => array(234, 234, 175), /* Uesugi    */
		7 => array( 64, 108, 128), /* Takeda    */
		8 => array(168, 126, 159), /* Hojo      */
		9 => array(120, 120, 120), /* Impartial */
	);

	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map'     =>'variants/Sengoku5/resources/smallmap.png',
				'army'    =>'variants/Sengoku5/resources/smallarmy.png',
				'fleet'   =>'variants/Sengoku5/resources/smallfleet.png',
				'names'   =>'variants/Sengoku5/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map'     =>'variants/Sengoku5/resources/map.png',
				'army'    =>'variants/Sengoku5/resources/army.png',
				'fleet'   =>'variants/Sengoku5/resources/fleet.png',
				'names'   =>'variants/Sengoku5/resources/mapNames.png',
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
