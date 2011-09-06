<?php
/*
	Copyright (C) 2010 Emmanuele Ravaioli

	This file is part of the Economic variant for webDiplomacy

	The Economic variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Economic variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class EconomicVariant_drawMap extends drawMap {

	/**
	 * An array of colors for different countries, indexed by countryID
	 * @var array
	 */
	protected $countryColors = array(
		0 => array(226, 198, 158), /* Neutral */
		1 => array(239, 196, 228), /* England */
		2 => array(121, 175, 198), /* France  */
		3 => array(164, 196, 153), /* Italy   */
		4 => array(160, 138, 117), /* Germany */
		5 => array(196, 143, 133), /* Austria */
		6 => array(234, 234, 175), /* Turkey  */
		7 => array(168, 126, 159)  /* Russia  */
	);

	/**
	 * Resources, all required except names, which will be drawn on by the computer if not supplied.
	 * @return array[$resourceName]=$resourceLocation
	 */
	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map'=>'variants/Economic/resources/smallmap.png',
				'army'=>'contrib/smallarmy.png',
				'fleet'=>'contrib/smallfleet.png',
				'names'=>'variants/Economic/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map'=>'variants/Economic/resources/map.png',
				'army'=>'contrib/army.png',
				'fleet'=>'contrib/fleet.png',
				'names'=>'variants/Economic/resources/mapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
	}
	// Draw the colored flags behind the units.
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