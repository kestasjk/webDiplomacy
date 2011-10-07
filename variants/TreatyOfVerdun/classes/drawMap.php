<?php
/*
	Copyright (C) 2011 Milan Mach

	This file is part of the 843 variant for webDiplomacy

	The 843 variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The 843 variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of 
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class TreatyOfVerdunVariant_drawMap extends drawMap
{
	protected $countryColors = array(
		0 => array(226, 198, 158), // Neutral
		1 => array(176, 175, 174), // East Francia
		2 => array(232, 232,  77), // Middle Francia
		3 => array(153, 104, 110), // West Francia
	);

	// Always load the largemap
	public function __construct($smallmap) {
		parent::__construct(false);
	}
	
	// The icons have a transparent background already
	protected function setTransparancies() {}
	
	protected function resources() {
		return array(
			'map'=>'variants/TreatyOfVerdun/resources/map.png',
			'army'=>'variants/TreatyOfVerdun/resources/army.png',
			'fleet'=>'variants/TreatyOfVerdun/resources/fleet.png',
			'names'=>'variants/TreatyOfVerdun/resources/mapNames.png',
			'standoff'=>'images/icons/cross.png'
		);
	}

}

?>