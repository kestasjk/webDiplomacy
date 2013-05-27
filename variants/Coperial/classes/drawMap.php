<?php
/*
	Copyright (C) 2013 Firehawk

	This file is part of the Coperial variant for webDiplomacy

	The Coperial variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Coperial variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class ColorUnits_drawMap extends drawMap
{

	protected $countryIconColors = array(
		0 => array(255, 213, 128), // Neutral
		1 => array(255, 153, 153), // Britain
		2 => array( 51, 102, 255), // France
		3 => array(102, 102, 102), // Germany
		4 => array(153, 255,   0), // Italy
		5 => array(102,   0,   0), // Austria
		6 => array(255, 153,   0), // Holland
		7 => array(204,  51,  51), // Russia
		8 => array(255, 255,   0), // Turkey
		9 => array(102,   0, 153), // China
		10=> array( 51, 153,   0)  // Japan
	);
	
	public function addUnit($terrName, $unitType)
	{
	
		list($r, $g, $b) = $this->countryIconColors[$this->unit_c[$terrName]];
		imagecolorset($this->{strtolower($unitType)}['image'],0, $r, $g, $b);
		parent::addUnit($terrName, $unitType);
	}
	
	public function colorTerritory($terrID, $countryID)	
	{
		$terrName=$this->territoryNames[$terrID];
		if (strpos($terrName,')') === false)
		{
			$this->unit_c[$terrID]=$countryID;
			$this->unit_c[array_search($terrName. " (North Coast)" ,$this->territoryNames)]=$countryID;
			$this->unit_c[array_search($terrName. " (East Coast)"  ,$this->territoryNames)]=$countryID;
			$this->unit_c[array_search($terrName. " (South Coast)" ,$this->territoryNames)]=$countryID;
			$this->unit_c[array_search($terrName. " (West Coast)"  ,$this->territoryNames)]=$countryID;
		}
		parent::colorTerritory($terrID, $countryID);
	}
	
	public function countryFlag($terrName, $countryID)	
	{
		$this->unit_c[$terrName]=$countryID;
	}

}

class CoperialVariant_drawMap extends ColorUnits_drawMap {

	protected $countryColors = array(
		0 => array(255, 213, 128), // Neutral
		1 => array(251, 161, 161), // Britain
		2 => array( 66, 126, 242), // France
		3 => array(123, 120, 120), // Germany
		4 => array(177, 222,  42), // Italy
		5 => array(119,  15,  15), // Austria
		6 => array(246, 167,  47), // Holland
		7 => array(197,  65,  65), // Russia
		8 => array(247, 219,  28), // Turkey
		9 => array(125,  68, 123), // China
		10=> array( 55, 140,  55)  // Japan
	);
	
	public function __construct($smallmap)
	{
		// Map is too big, so up the memory-limit
		parent::__construct(true);
		ini_set('memory_limit',"32M");
	}

	protected function resources() {
		return array(
			'map'=>'variants/Coperial/resources/map.png',
			'army'=>'variants/Coperial/resources/army.png',
			'fleet'=>'variants/Coperial/resources/fleet.png',
			'names'=>'variants/Coperial/resources/mapNames.png',
			'standoff'=>'images/icons/cross.png'
		);
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