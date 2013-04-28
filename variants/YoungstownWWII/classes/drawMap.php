<?php
/*
	Copyright (C) 2013 Arjun Sarathy / Oliver Auth

	This file is part of the Youngstown World War II variant for webDiplomacy

	The Youngstown World War II variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The Youngstown World War II variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class YoungstownWWIIVariant_drawMap extends drawMap {

	protected $countryColors = array(
		0 => array(240, 198, 158), // Neutral
		1 => array(239, 196, 228), // British Empire
		2 => array(121, 175, 198), // French Empire
		3 => array(164, 196, 153), // Italian Empire
		4 => array(  5, 116,  24), // Japanese Empire
		5 => array(160, 138, 117), // Germany
		6 => array(168, 126, 159)  // Soviet Union
	);

	protected function resources() {
		return array(
			'map'=>'variants/YoungstownWWII/resources/map.png',
			'army'=>'contrib/smallarmy.png',
			'fleet'=>'contrib/smallfleet.png',
			'names'=>'variants/YoungstownWWII/resources/mapNames.png',
			'standoff'=>'images/icons/cross.png'
		);
	}
	public function __construct($smallmap)
	{
		parent::__construct(true);
	}

	public function drawSupportMove($terrID, $fromTerrID, $toTerrID, $success)
	{
		$this->smallmap=false; parent::drawSupportMove($terrID, $fromTerrID, $toTerrID, $success); $this->smallmap=true;
	}
	public function drawConvoy($terrID, $fromTerrID, $toTerrID, $success)
	{
		$this->smallmap=false; parent::drawConvoy($terrID, $fromTerrID, $toTerrID, $success); $this->smallmap=true;
	}
	public function drawSupportHold($fromTerrID, $toTerrID, $success)
	{
		$this->smallmap=false; parent::drawSupportHold($fromTerrID, $toTerrID, $success); $this->smallmap=true;
	}
	public function drawDislodgedUnit($terrID)
	{
		$this->smallmap=false; parent::drawDislodgedUnit($terrID); $this->smallmap=true;
	}
	
}

?>