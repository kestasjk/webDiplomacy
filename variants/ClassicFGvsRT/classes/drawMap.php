<?php
/*
	Copyright (C) 2010 Cian O Rathaille

	This file is part of the France-Germany Vs Russia-Turkey variant for webDiplomacy

	The France-Germany Vs Russia-Turkey variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The France-Germany Vs Russia-Turkey variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of 
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
	
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class ClassicFGvsRTVariant_drawMap extends drawMap {
	protected $countryColors = array(
		0 => array(226, 198, 158), // Neutral
		1 => array(206, 153, 103), // Frankland
		2 => array(120, 120, 120)  // Juggernaut
	);

	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map'=>'variants/ClassicFGvsRT/resources/smallmap.png',
				'army'=>'contrib/smallarmy.png',
				'fleet'=>'contrib/smallfleet.png',
				'names'=>'variants/ClassicFGvsRT/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map'=>'variants/ClassicFGvsRT/resources/map.png',
				'army'=>'contrib/army.png',
				'fleet'=>'contrib/fleet.png',
				'names'=>'variants/ClassicFGvsRT/resources/mapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
	}
}

?>