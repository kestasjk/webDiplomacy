<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the Youngstown - Redux variant for webDiplomacy

	The Youngstown - Redux variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Youngstown - Redux variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class ZoomMap_drawMap extends drawMap
{
	// Always only load the largemap (as there is no smallmap)
	public function __construct($smallmap)
	{
		parent::__construct(false);
	}
	
	// Always use the small orderarrows...
	protected function loadOrderArrows()
	{
		$this->smallmap=true;
		parent::loadOrderArrows();
		$this->smallmap=false;
	}	
	
	// Always use the small standoff-Icons
	public function drawStandoff($terrName)
	{
		$this->smallmap=true;
		parent::drawStandoff($terrName);
		$this->smallmap=false;
	}
}

class YoungstownReduxVariant_drawMap extends ZoomMap_drawMap {

	protected $countryColors = array(
		 0  => array(226, 198, 158), // Neutral
		 1  => array(255, 127,  39), // India
		 2  => array(  5, 116,  24), // Japan
		 3  => array(198, 68,  68), // Austria
		 4  => array( 31, 192,  22), // Italy
		 5  => array(122,  71, 216), // China
		 6  => array(250,   80,   84), // Britain
		 7  => array( 71,  100, 237), // France
		 8  => array(180, 180, 180), // Germany
		 9  => array(242, 237,  19), // Turkey
		10  => array(255, 255, 255), // Russia
	);

	protected function resources() {
		return array(
			'map'     =>'variants/YoungstownRedux/resources/map.png',
			'army'    =>'contrib/smallarmy.png',
			'fleet'   =>'contrib/smallfleet.png',
			'names'   =>'variants/YoungstownRedux/resources/mapNames.png',
			'standoff'=>'images/icons/cross.png'
		);
	}

}
