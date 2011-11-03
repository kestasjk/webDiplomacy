<?php
/*
	Copyright (C) 2011 by kaner406 & Oliver Auth

	This file is part of the War in 2020 variant for webDiplomacy

	The War in 2020 variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The War in 2020 variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
	
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class War2020Variant_drawMap extends drawMap {

	protected $countryColors = array(
		0 => array(226, 198, 158), // Neutral
		1 => array(239, 196, 228), // Australia
		2 => array(114, 146, 103), // USA
		3 => array(121, 175, 198), // OAS
		4 => array(160, 138, 117), // EU
		5 => array(164, 196, 153), // South Africa
		6 => array(196, 143, 133), // India
		7 => array(168, 126, 159), // OPEC
		8 => array(206, 153, 103), // China
		9 => array(234, 234, 175), // Russia
		10=> array(120, 120, 120)  // Japan
	);

	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map'=>'variants/War2020/resources/smallmap.png',
				'army'=>'contrib/smallarmy.png',
				'fleet'=>'contrib/smallfleet.png',
				'names'=>'variants/War2020/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map'=>'variants/War2020/resources/map.png',
				'army'=>'contrib/army.png',
				'fleet'=>'contrib/fleet.png',
				'names'=>'variants/War2020/resources/mapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
	}
}

?>