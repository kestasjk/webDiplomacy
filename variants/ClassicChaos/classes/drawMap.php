<?php
/*
	Copyright (C) 2010 Carey Jensen / Oliver Auth

	This file is part of the Chaos variant for webDiplomacy

	The Chaos variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Chaos variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	country-colors by Iceray0

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class ClassicChaosVariant_drawMap extends drawMap {

	protected $countryColors = array(
		 0 => array(226, 198, 158), /* Neutral        */
		 1 => array( 93, 138, 168), /* Ankara         */
		 2 => array(255,  23,  23), /* Belgium        */
		 3 => array(191,  54, 126), /* Berlin         */
		 4 => array(237,  60, 202), /* Brest          */
		 5 => array(137, 207, 240), /* Budapest       */
		 6 => array( 34,  50,  65), /* Bulgaria       */
		 7 => array(164, 198,  57), /* Constantinople */
		 8 => array(202, 179,  16), /* Denmark        */
		 9 => array( 75,  83,  32), /* Edinburgh      */
		10 => array(253,  95,   0), /* Greece         */
		11 => array(178, 190, 181), /* Holland        */
		12 => array( 26, 221,  81), /* Kiel           */
		13 => array(255, 153, 102), /* Liverpool      */
		14 => array(  0, 255, 255), /* London         */
		15 => array(253, 238,   0), /* Marseilles     */
		16 => array(192, 114, 200), /* Moscow         */
		17 => array(160, 138, 117), /* Munich         */
		18 => array(  0,  66,  37), /* Naples         */
		19 => array( 77,  77, 197), /* Norway         */
		20 => array(135, 169, 107), /* Paris          */
		21 => array( 92, 255, 155), /* Portugal       */
		22 => array(250, 157, 166), /* Rome           */
		23 => array(255, 249, 224), /* Rumania        */
		24 => array(207, 235,  30), /* Serbia         */
		25 => array( 70, 179,   0), /* Sevastopol     */
		26 => array(255, 191,   0), /* Smyrna         */
		27 => array(147, 162, 208), /* Spain          */
		28 => array(139,   0,   0), /* St-Petersburg  */
		29 => array(172, 225, 175), /* Sweden         */
		30 => array(  0, 127, 255), /* Trieste        */
		31 => array(251, 204, 231), /* Tunis          */
		32 => array(107,  63, 149), /* Venice         */
		33 => array(255, 140,   0), /* Vienna         */
		34 => array(  0, 204, 153)  /* Warsaw         */
	);

	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map'     =>'variants/ClassicChaos/resources/smallmap.png',
				'army'    =>'contrib/smallarmy.png',
				'fleet'   =>'contrib/smallfleet.png',
				'names'   =>'variants/ClassicChaos/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map'     =>'variants/ClassicChaos/resources/map.png',
				'army'    =>'contrib/army.png',
				'fleet'   =>'contrib/fleet.png',
				'names'   =>'variants/ClassicChaos/resources/mapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
	}

}

?>
