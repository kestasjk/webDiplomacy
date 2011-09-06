<?php
/*
	Copyright (C) 2011 Oliver Auth

	This file is part of the GreekDip variant for webDiplomacy

	The GreekDip variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The GreekDip variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class BiddingStart_drawmap extends drawMap
{
	public $bet;
		
	public function ColorTerritory($terrID, $countryID)
	{
		if (strpos($this->territoryNames[$terrID],'Coin Stack') === false)
			parent::ColorTerritory($terrID, $countryID);
	}
	
	public function addUnit($terrID, $unitType)
	{
		if ( isset($this->bet[$terrID]) )
		{
			list($x, $y) = $this->territoryPositions[$terrID];
			$this->territoryPositions[0] = array($x,$y+$this->fleet['width']/3);
			parent::countryFlag(0, 0);
			$this->drawText($this->bet[$terrID], $x, $y); 			
		}
		elseif (strpos($this->territoryNames[$terrID],'Coin Stack') === false)
			parent::addUnit($terrID, $unitType);
	}

	public function drawMove($fromTerrID, $toTerrID, $success)
	{
		if (strpos($this->territoryNames[$fromTerrID],'Coin Stack') === false)
			parent::drawMove($fromTerrID, $toTerrID, $success);
		elseif ($success == true)
		{
			if ( isset($this->bet[$toTerrID]) )
				$this->bet[$toTerrID]++;
			else
				$this->bet[$toTerrID]=1;
		}
		else
		{
			$this->drawStandoff($toTerrID);
			if (isset($this->bet[$toTerrID]) )
			{
				list($x, $y) = $this->territoryPositions[$toTerrID];
				$this->drawText($this->bet[$toTerrID], $x, $y); 			
			}
		}
	}
	
	public function drawSupportMove($terrID, $fromTerrID, $toTerrID, $success)
	{
		if (strpos($this->territoryNames[$terrID],'Coin Stack') === false)
			parent::drawSupportMove($terrID, $fromTerrID, $toTerrID, $success);
		elseif ($success == true)
			if ( isset($this->bet[$toTerrID]) )
				$this->bet[$toTerrID]++;
			else
				$this->bet[$toTerrID]=1;			
	}
	
}

class GreekDipVariant_drawMap extends BiddingStart_drawmap {

	protected $countryColors = array(
		0 => array(226, 198, 158), // Neutral
		1 => array( 64, 108, 128), // Athens
		2 => array(164, 196, 153), // Byzantium
		3 => array(168, 126, 159), // Macedonia
		4 => array(234, 234, 175), // Persia
		5 => array(121, 175, 198), // Rhoades
		6 => array(196, 143, 133), // Sparta
	);

	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map'     =>'variants/GreekDip/resources/smallmap.png',
				'army'    =>'variants/GreekDip/resources/smallarmy.png',
				'fleet'   =>'variants/GreekDip/resources/smallfleet.png',
				'names'   =>'variants/GreekDip/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map'     =>'variants/GreekDip/resources/map.png',
				'army'    =>'variants/GreekDip/resources/army.png',
				'fleet'   =>'variants/GreekDip/resources/fleet.png',
				'names'   =>'variants/GreekDip/resources/mapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
	}

}

?>