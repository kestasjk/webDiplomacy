<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the Claccic-Fog-of-War variant for webDiplomacy

	The Claccic-Fog-of-War variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General Public
	License as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Claccic-Fog-of-War variant for webDiplomacy is distributed in the hope that 
	it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class ClassicFogVariant_drawMap extends drawMap {

	protected $countryColors = array(
		0 => array(226, 198, 158),
		1 => array(239, 196, 228),
		2 => array(121, 175, 198),
		3 => array(164, 196, 153),
		4 => array(160, 138, 117),
		5 => array(196, 143, 133),
		6 => array(234, 234, 175),
		7 => array(168, 126, 159),
		8 => array(200, 200, 200), // Fog
		9 => array(161, 226, 255)  // Sea
	);

	protected $all_fog  = true;
	protected $show_all = false;
	
	// Check if it's called from our special map-code. If not a player might want to cheat and we set all to fog.
	// or the game is over, than we reveal the map.
	public function __construct($smallmap,$all_fog=true)
	{
		global $Game;
		
		if (isset($Game)) {
			if ($Game->phase == 'Finished')
				$this->show_all=true;
		}
		$this->all_fog=$all_fog;
		parent::__construct($smallmap);
	}
	
	protected function resources()
	{
		if ( $this->smallmap )
			$images=array(
				'army'    =>l_s('contrib/smallarmy.png'),
				'fleet'   =>l_s('contrib/smallfleet.png'),
				'names'   =>l_s('variants/ClassicFog/resources/smallmapNames.png'),
				'standoff'=>l_s('images/icons/cross.png') );
		else
			$images=array(
				'army'    =>l_s('contrib/army.png'),
				'fleet'   =>l_s('contrib/fleet.png'),
				'names'   =>l_s('variants/ClassicFog/resources/mapNames.png'),
				'standoff'=>l_s('images/icons/cross.png'));
		
		if ($this->show_all || $this->all_fog) {
			if( $this->smallmap )
				$images['map'] ='variants/ClassicFog/resources/smallmap_noFog.png';
			else
				$images['map'] ='variants/ClassicFog/resources/map_noFog.png';
		} else {
			if( $this->smallmap )
				$images['map'] ='variants/ClassicFog/resources/smallmap.png';
			else
				$images['map'] ='variants/ClassicFog/resources/map.png';
		}
		return $images;
	}
	
	protected function color(array $color, $image=false)
	{
		if ( ! is_array($image) )
		{
			$image = $this->map;
		}
		
		list($r, $g, $b) = $color;
		
		$colorRes = imagecolorexact($image['image'], $r, $g, $b);
		if ($colorRes == -1)
		{
		$colorRes = imageColorAllocate($image['image'], $r, $g, $b);
			if (!$colorRes)
				$colorRes = imageColorClosest($image['image'], $r, $g, $b);
		}
		
		return $colorRes; 
	}
	
	// Some small islands (or seas) don't belong to a territory. Cover them with for or 
	// unhide them if a border-territory gets revealed.
	public function colorTerritory($terrID, $countryID, $recursive=false)	
	{
		if ($this->show_all && $terrID >81) return;
		if ($this->show_all && $terrID <82) return parent::colorTerritory($terrID, $countryID);
		if ($this->all_fog)                 return parent::colorTerritory($terrID, 8);
		
		if (($terrID > 81) && ($recursive == false)) return;
		
		if     (($terrID == 58) && ($countryID != 8)) $this->colorTerritory(82, 0, true); // N. Atlantic Ocean 
		elseif (($terrID == 58) && ($countryID == 8)) $this->colorTerritory(82, 8, true);
		elseif (($terrID == 59) && ($countryID != 8)) $this->colorTerritory(83, 0, true); // Irish Sea
		elseif (($terrID == 59) && ($countryID == 8)) $this->colorTerritory(83, 8, true); 
		elseif (($terrID == 67) && ($countryID != 8)) $this->colorTerritory(84, 0, true); // Aegean Sea
		elseif (($terrID == 67) && ($countryID == 8)) $this->colorTerritory(84, 8, true);
		elseif (($terrID == 64) && ($countryID != 8)) $this->colorTerritory(85, 0, true); //Tyrrheian Sea
		elseif (($terrID == 64) && ($countryID == 8)) $this->colorTerritory(85, 8, true); 
		elseif (($terrID == 62) && ($countryID != 8)) $this->colorTerritory(86, 0, true); // Western Med.
		elseif (($terrID == 62) && ($countryID == 8)) $this->colorTerritory(86, 8, true);
		elseif (($terrID == 37) && ($countryID != 8)) $this->colorTerritory(87, 9, true); // Kiel
		elseif (($terrID == 37) && ($countryID == 8)) $this->colorTerritory(87, 8, true); 
		elseif (($terrID == 27) && ($countryID != 8)) $this->colorTerritory(88, 9, true); // Sevastopol
		elseif (($terrID == 27) && ($countryID == 8)) $this->colorTerritory(88, 8, true); 
		elseif (($terrID == 25) && ($countryID != 8)) $this->colorTerritory(89, 9, true); // Armenia
		elseif (($terrID == 25) && ($countryID == 8)) $this->colorTerritory(89, 8, true);
		elseif (($terrID == 36) && ($countryID != 8)) $this->colorTerritory(90, 9, true); // Denmark
		elseif (($terrID == 36) && ($countryID == 8)) $this->colorTerritory(90, 8, true); 
		elseif (($terrID == 32) && ($countryID != 8)) $this->colorTerritory(91, 9, true); // St. Petersburg
		elseif (($terrID == 32) && ($countryID == 8)) $this->colorTerritory(91, 8, true);
		
		parent::colorTerritory($terrID, $countryID);

	}

	// Hide order-info as long as the game is not finished.
	public function countryFlag($terrName, $countryID)	{
		if ($this->show_all || !$this->all_fog) parent::countryFlag($terrName, $countryID);
	}
	
	public function addUnit($terrName, $unitType)	{
		if ($this->show_all || !$this->all_fog) parent::addUnit($terrName, $unitType);
	}
	public function drawStandoff($terrName)	{
		if ($this->show_all || !$this->all_fog) parent::drawStandoff($terrName);
	}	
	public function drawSupportMove($terrID, $fromTerrID, $toTerrID, $success)	{
		if ($this->show_all || !$this->all_fog) parent::drawSupportMove($terrID, $fromTerrID, $toTerrID, $success);
	}
	public function drawConvoy($terrID, $fromTerrID, $toTerrID, $success){
		if ($this->show_all || !$this->all_fog) parent::drawConvoy($terrID, $fromTerrID, $toTerrID, $success);
	}
	public function drawMove($fromTerrID, $toTerrID, $success)	{
		if ($this->show_all || !$this->all_fog) parent::drawMove($fromTerrID, $toTerrID, $success);
	}
	public function drawSupportHold($fromTerrID, $toTerrID, $success)	{
		if ($this->show_all || !$this->all_fog) parent::drawSupportHold($fromTerrID, $toTerrID, $success);
	}
	public function drawRetreat($fromTerrID, $toTerrID, $success) {
		if ($this->show_all || !$this->all_fog) parent::drawRetreat($fromTerrID, $toTerrID, $success);
	}
	public function drawDestroyedUnit($terrID)	{
		if ($this->show_all || !$this->all_fog) parent::drawDestroyedUnit($terrID);
	}
	public function drawDislodgedUnit($terrID)	{
		if ($this->show_all || !$this->all_fog) parent::drawDislodgedUnit($terrID);
	}
	public function drawCreatedUnit($terrID, $unitType)	{
		if ($this->show_all || !$this->all_fog) parent::drawCreatedUnit($terrID, $unitType);
	}
	
}

?>
