<?php
/*
	Copyright (C) 2004-2010 Kestas J. Kuliukas

	This file is part of webDiplomacy.

	webDiplomacy is free software: you can redistribute it and/or modify
	it under the terms of the GNU Affero General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	webDiplomacy is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('IN_CODE') or die('This script can not be run by itself.');

class ClassicVariant_drawMap extends drawMap {

	/**
	 * An array of colors for different countries, indexed by countryID
	 * @var array
	 */

	public $countryColors=array(
		// Neutral
		0 => array(266, 198, 158)
		// Edinburgh
		1 => array(239, 196, 228)
		// Liverpool
		2 => array(255, 0, 255)
		// London
		3 => array(230, 0, 120)
		// Portugal
		4 => array(255, 76, 0)
		// Spain
		5 => array(255, 144, 0)
		// Tunis
		6 => array(0, 255, 169)
		// Naples
		7 => array(164, 196, 153)
		// Rome
		8 => array(105, 193, 75)
		// Venice
		9 => array(109, 191, 115)
		// Greece
		10 => array(0, 226, 196)
		// Serbia
		11 => array(75, 146, 204)
		// Bulgaria
		12 => array(155, 160, 96)
		// Rumania
		13 => array(173, 62, 149)
		// Constantinople
		14 => array(234, 234, 175)
		// Smyrna
		15 => array(229, 229, 89)
		// Ankara
		16 => array(165, 165, 14)
		// Sevastopol
		17 => array(137, 20, 112)
		// Warsaw
		18 => array(112, 42, 97)
		// Moscow
		19 => array(168, 126, 159)
		// St. Petersburg
		20 => array(152, 117, 165)
		// Sweden
		21 => array(94, 1, 37)
		// Norway
		22 => array(229, 174, 160)
		// Denmark
		23 => array(206, 59, 22)
		// Kiel
		24 => array(237, 201, 168)
		// Berlin
		25 => array(160, 138, 117)
		// Munich
		26 => array(104, 79, 56)
		// Holland
		27 => array(255, 123, 0)
		// Belgium
		28 => array(105, 181, 229)
		// Brest
		29 => array(47, 151, 216)
		// Paris
		30 => array(239, 196, 228)
		// Marseilles
		31 => array(20, 186, 166)
		// Vienna
		32 => array(196, 143, 133)
		// Trieste
		33 => array(193, 105, 89)
		// Budapest
		34 => array(160, 32, 9)
		)
		
	/**
	 * Resources, all required except names, which will be drawn on by the computer if not supplied.
	 * @return array[$resourceName]=$resourceLocation
	 */
	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map'=>l_s('variants/Classic/resources/smallmap.png'),
				'army'=>l_s('contrib/smallarmy.png'),
				'fleet'=>l_s('contrib/smallfleet.png'),
				'names'=>l_s('variants/Classic/resources/smallmapNames.png'),
				'standoff'=>l_s('images/icons/cross.png')
			);
		}
		else
		{
			return array(
				'map'=>l_s('variants/Classic/resources/map.png'),
				'army'=>l_s('contrib/army.png'),
				'fleet'=>l_s('contrib/fleet.png'),
				'names'=>l_s('variants/Classic/resources/mapNames.png'),
				'standoff'=>l_s('images/icons/cross.png')
			);
		}
	}
}

?>