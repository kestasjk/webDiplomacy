<?php
/*
	Copyright (C) 2011 Carey Jensen / Oliver Auth

	This file is part of the Chaoctopi variant for webDiplomacy

	The Chaoctopi variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Chaoctopi variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
		
	country-colors by Iceray0
		
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class ChaoctopiVariant_drawMap extends drawMap {

	protected $countryColors = array(
		 0 => array(226, 198, 158), /* Neutral        */
		 1 => array( 93, 138, 168), /* Ankara         */
		 2 => array(227,  38,  54), /* Belgium        */
		 3 => array(159,  43, 104), /* Berlin         */
		 4 => array(237,  60, 202), /* Brest          */
		 5 => array(255, 191,   0), /* Budapest       */
		 6 => array(255, 126,   0), /* Bulgaria       */
		 7 => array(164, 198,  57), /* Constantinople */
		 8 => array(  0, 255, 255), /* Denmark        */
		 9 => array( 75,  83,  32), /* Edinburgh      */
		10 => array( 59,  68,  75), /* Greece         */
		11 => array(178, 190, 181), /* Holland        */
		12 => array(135, 169, 107), /* Kiel           */
		13 => array(255, 153, 102), /* Liverpool      */
		14 => array(109,  53,  26), /* London         */
		15 => array(253, 238,   0), /* Marseilles     */
		16 => array(  0, 127, 255), /* Moscow         */
		17 => array(137, 207, 240), /* Munich         */
		18 => array(254, 111,  94), /* Naples         */
		19 => array(  0,   0, 255), /* Norway         */
		20 => array(138,  43, 226), /* Paris          */
		21 => array(181, 166,  66), /* Portugal       */
		22 => array(  0,  66,  37), /* Rome           */
		23 => array(205, 127,  50), /* Rumania        */
		24 => array(150,  75,   0), /* Serbia         */
		25 => array(240, 220, 130), /* Sevastopol     */
		26 => array(  0, 204, 153), /* Smyrna         */
		27 => array(237, 145,  33), /* Spain          */
		28 => array(147, 162, 208), /* St-Petersburg  */
		29 => array(172, 225, 175), /* Sweden         */
		30 => array(210, 105,  30), /* Trieste        */
		31 => array(251, 204, 231), /* Tunis          */
		32 => array(184, 134,  11), /* Venice         */
		33 => array(255, 140,   0), /* Vienna         */
		34 => array(139,   0,   0)  /* Warsaw         */
	);
	
	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map'     =>'variants/Chaoctopi/resources/smallmap.png',
				'army'    =>'contrib/smallarmy.png',
				'fleet'   =>'contrib/smallfleet.png',
				'names'   =>'variants/Chaoctopi/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map'     =>'variants/Chaoctopi/resources/map.png',
				'army'    =>'contrib/army.png',
				'fleet'   =>'contrib/fleet.png',
				'names'   =>'variants/Chaoctopi/resources/mapNames.png',
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
