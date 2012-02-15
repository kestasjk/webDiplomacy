<?php
/*
	Copyright (C) 2012 kaner406 / Oliver Auth

	This file is part of the Zeus5 variant for webDiplomacy

	The Zeus5 variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The Zeus5 variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
	
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class Zeus5Variant_drawMap extends drawMap {

	protected $countryColors = array(
		0  => array(226, 198, 158), // Neutral
		1  => array(239, 196, 228), // United Kingdom
		2  => array(121, 175, 198), // United States
		3  => array(164, 196, 153), // Italy
  		4  => array(160, 138, 117), // Germany
  		5  => array(196, 143, 133), // Japan
  		6  => array(234, 234, 175), // China
  		7  => array(168, 126, 159), // Soviet Union
		8  => array(226, 198, 158), // Neutral-India
	);
	
	protected $factory = array(); 
	
	public function colorTerritory($terrID, $countryID)
	{
		parent::colorTerritory($terrID, $countryID);
		
		// Draw a small factory in California whan occupied by US.
		if ($terrID == 13 && $countryID == 2)
		{
			$this->factory = $this->loadImage('variants/Zeus5/resources/'.($this->smallmap ? 'small' : '').'fac.png');
			$x = ($this->smallmap ? '160' : '235');
			$y = ($this->smallmap ? '485' : '690');
			$this->putImage($this->factory, $x, $y);
		}
	}
	
	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map'     =>'variants/Zeus5/resources/smallmap.png',
				'army'    =>'contrib/smallarmy.png',
				'fleet'   =>'contrib/smallfleet.png',
				'names'   =>'variants/Zeus5/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map'     =>'variants/Zeus5/resources/map.png',
				'army'    =>'contrib/army.png',
				'fleet'   =>'contrib/fleet.png',
				'names'   =>'variants/Zeus5/resources/mapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
	}

}

?>