<?php
/*
	Copyright (C) 2010 Emmanuele Ravaioli and Oliver Auth

	This file is part of the Battle of Lepanto variant for webDiplomacy

	The Battle of Lepanto variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The Battle of Lepanto variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class LepantoVariant_drawMap extends drawMap {

	protected $countryColors = array(
		0 => array(  0,   0, 160), // Neutral
		1 => array( 40, 140,   0), // Holy League
		2 => array(215,  55,  55), // Ottoman Empire
	);

	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map'     =>'variants/Lepanto/resources/smallmap.png',
				'army'    =>'variants/Lepanto/resources/smallarmy.png',
				'fleet'   =>'variants/Lepanto/resources/smallfleet.png',
				'names'   =>'variants/Lepanto/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map'     =>'variants/Lepanto/resources/map.png',
				'army'    =>'variants/Lepanto/resources/army.png',
				'fleet'   =>'variants/Lepanto/resources/fleet.png',
				'names'   =>'variants/Lepanto/resources/mapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
	}

	// move the country-flag behind the sails
	public function countryFlag($terrName, $countryID)
	{

		$flagColor = $this->color($this->countryColors[$countryID]);

		list($x, $y) = $this->territoryPositions[$terrName];

		$coordinates = array(
			'top-left' => array(
				'x'=>$x-intval($this->fleet['width']/32*10),
				'y'=>$y-intval($this->fleet['height']/2)
			),
			'bottom-right' => array(
				'x'=>$x+intval($this->fleet['width']/32*10),
				'y'=>$y+1
			)
		);

		imagefilledrectangle($this->map['image'],
			$coordinates['top-left']['x'], $coordinates['top-left']['y'],
			$coordinates['bottom-right']['x'], $coordinates['bottom-right']['y'],
			$flagColor);
	}

}

?>