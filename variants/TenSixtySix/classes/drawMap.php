<?php
/*
	Copyright (C) 2011 Oliver Auth

	This file is part of the 1066 variant for webDiplomacy

	The 1066 variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The 1066 variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.		
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class CustomIcons_drawmap extends drawmap
{
	// Arrays for the custom icons:
	protected $unit_c =array(); // An array to store the owner of each territory
	protected $army_c =array(); // Custom army icons
	protected $fleet_c=array(); // Custom fleet icons

	// Load the custom icons...
	protected function loadImages()
	{
		global $Game;
		for ($i=1; $i<=4; $i++)
		{
			$this->army_c[$i]  = $this->loadImage('variants/TenSixtySix/resources/'.($this->smallmap ? 'small' : '').'army_' .$i.'.png');
			$this->fleet_c[$i] = $this->loadImage('variants/TenSixtySix/resources/'.($this->smallmap ? 'small' : '').'fleet_'.$i.'.png');
		}
		parent::loadImages();
	}
	
	// Save the countryID for every colored Territory (and their coasts)
	public function colorTerritory($terrID, $countryID)
	{
		$this->unit_c[$terrID]=$countryID;
		foreach (preg_grep( "/^".$this->territoryNames[$terrID].".* Coast\)$/", $this->territoryNames) as  $id=>$name)
			$this->unit_c[$id]=$countryID;
		parent::colorTerritory($terrID, $countryID);
	}
	
	// Overwrite the country if a unit needs to draw a flag (and don't draw the flag) -> we use custom icons instead
	public function countryFlag($terrName, $countryID)
	{
		$this->unit_c[$terrName]=$countryID;
	}
	
	// Draw the custom icons:
	public function addUnit($terrName, $unitType)
	{
		$this->army  = $this->army_c[$this->unit_c[$terrName]];
		$this->fleet = $this->fleet_c[$this->unit_c[$terrName]];
		parent::addUnit($terrName, $unitType);
	}

}

class Fog_drawMap extends CustomIcons_drawmap
{
	protected $cheat = false;
	
	// variable to store the color-index for the fog and sea color
	protected $fog_index;
	protected $sea_index;
	
	protected $sea_terrs = array(
		'Firth of Clyde' , 'North Atlantic Ocean' , 'Mid Atlantic Ocean' , 'Irish Sea',
		'Bristol Channel', 'North English Channel', 'Southwest North Sea', 'Strait of Dover',
		'Thames Estuary' , 'South English Channel', 'Northwest North Sea', 'Skagerrak',
		'Norwegian Sea'  , 'Northeast North Sea'  , 'Southeast North Sea', 'Baltic Sea',
		'Channel Islands', 'Shetland and Orkneys' , 'Heligoland Bight');
	
	// Check if it's called from our special map-code. If not a player might cheat and we set all to fog.
	// or the game is over, than we reveal the map.
	public function __construct($smallmap,$all_fog=true)
	{
		global $Game;

		parent::__construct($smallmap);
		
		// Add the fog and sea colors to the country-palette
		$this->fog_index = count($this->countryColors);
		$this->sea_index = count($this->countryColors)+1;		
		$this->countryColors[$this->fog_index] = array(222, 200, 177); // Fog
		$this->countryColors[$this->sea_index] = array(176, 209, 201); // Sea

		$this->cheat = $all_fog;
	
		if (isset ($Game))
		{
			if ($Game->phase == 'Finished' || $Game->phase == 'Pre-Game')
				$this->cheat = false;
		}
		else
		{
			$this->cheat = false;
		}

		// Make the seas all blue (maybe the Fog hides this later again)
		foreach ($this->sea_terrs as $seas)
			$this->colorTerritory(array_search($seas ,$this->territoryNames), $this->sea_index);
	}
	
	// Some small islands (or seas) don't belong to a territory. Cover them with for or 
	// unhide them if a border-territory gets revealed.
	public function colorTerritory($terrID, $countryID)	
	{
		// Don't recolor the fake territories when called from the map.php
		if (strpos($this->territoryNames[$terrID],' (fake)')) return;

		// Just cover everything with fog if a cheater want to take a look...
		if ($this->cheat) $countryID = $this->fog_index;
		
		// If there is a "fake" territory to color, color it.
		if (in_array($this->territoryNames[$terrID].' (fake)' ,$this->territoryNames))
		{
			if ($countryID == $this->sea_index)
				parent::colorTerritory(array_search($this->territoryNames[$terrID].' (fake)' ,$this->territoryNames), 0);
			elseif ($countryID == $this->fog_index)
				parent::colorTerritory(array_search($this->territoryNames[$terrID].' (fake)' ,$this->territoryNames), $this->fog_index);			
			else
				parent::colorTerritory(array_search($this->territoryNames[$terrID].' (fake)' ,$this->territoryNames), $this->sea_index);			
		}
		parent::colorTerritory($terrID, $countryID);
	}

	// Hide everyting from the cheaters
	public function countryFlag($terrName, $countryID)	{
		if (!$this->cheat) parent::countryFlag($terrName, $countryID);
	}
	public function addUnit($terrName, $unitType)	{
		if (!$this->cheat) parent::addUnit($terrName, $unitType);
	}
	public function drawStandoff($terrName)	{
		if (!$this->cheat) parent::drawStandoff($terrName);
	}	
	public function drawSupportMove($terrID, $fromTerrID, $toTerrID, $success)	{
		if (!$this->cheat) parent::drawSupportMove($terrID, $fromTerrID, $toTerrID, $success);
	}
	public function drawConvoy($terrID, $fromTerrID, $toTerrID, $success){
		if (!$this->cheat) parent::drawConvoy($terrID, $fromTerrID, $toTerrID, $success);
	}
	public function drawMove($fromTerrID, $toTerrID, $success)	{
		if (!$this->cheat) parent::drawMove($fromTerrID, $toTerrID, $success);
	}
	public function drawSupportHold($fromTerrID, $toTerrID, $success)	{
		if (!$this->cheat) parent::drawSupportHold($fromTerrID, $toTerrID, $success);
	}
	public function drawRetreat($fromTerrID, $toTerrID, $success) {
		if (!$this->cheat) parent::drawRetreat($fromTerrID, $toTerrID, $success);
	}
	public function drawDestroyedUnit($terrID)	{
		if (!$this->cheat) parent::drawDestroyedUnit($terrID);
	}
	public function drawDislodgedUnit($terrID)	{
		if (!$this->cheat) parent::drawDislodgedUnit($terrID);
	}
	public function drawCreatedUnit($terrID, $unitType)	{
		if (!$this->cheat) parent::drawCreatedUnit($terrID, $unitType);
	}
}

class TenSixtySixVariant_drawMap extends Fog_drawmap
{
	protected $countryColors = array(
		0 => array(209, 180,  90), // Neutral
		1 => array(125,  50,  64), // English
		2 => array( 83, 103, 128), // Normans
		3 => array(118, 137, 118), // Norwegians
		4 => array(199, 171,  69), // Neutral units
	);

	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map' =>'variants/TenSixtySix/resources/smallmap.png',
				'army' =>'contrib/smallarmy.png',
				'fleet' =>'contrib/smallfleet.png',
				'names' =>'variants/TenSixtySix/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map' =>'variants/TenSixtySix/resources/map.png',
				'army' =>'contrib/army.png',
				'fleet' =>'contrib/fleet.png',
				'names' =>'variants/TenSixtySix/resources/mapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
	}
	
	protected function color(array $color, $image=false)
	{
		if ( ! is_array($image) )
			$image = $this->map;
		
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

}

?>
