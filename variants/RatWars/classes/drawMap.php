<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class Fog_drawMap extends drawmap
{
	protected $cheat = false;
	
	// variable to store the color-index for the fog color
	protected $fog_index;
	
	// Check if it's called from our special map-code. If not a player might cheat and we set all to fog.
	// or the game is over, than we reveal the map.
	public function __construct($smallmap,$all_fog=true)
	{
		global $Game;

		parent::__construct($smallmap);
		
		// Add the fog and sea colors to the country-palette
		$this->fog_index = count($this->countryColors);
		$this->countryColors[$this->fog_index] = array(200, 200, 200); // Fog

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

	}
	
	public function colorTerritory($terrID, $countryID)	
	{
		// Just cover everything with fog if a cheater want to take a look...
		if ($this->cheat) $countryID = $this->fog_index;
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

class RatWarsVariant_drawMap extends Fog_drawMap {

	protected $countryColors = array(
		0  => array(226, 198, 158), /* Neutral   */
		1  => array(239, 196, 228), /* Dead Rabbits */
		2  => array(121, 175, 198), /* Plug Uglies */
		3  => array(168, 126, 159), /* Shirt Tails */
		4  => array(164, 196, 153), /* Hell-Cats */
	);

	// No need to set the transparency for our custom icons and mapnames.
	protected function setTransparancy(array $image, array $color=array(255,255,255)) {}

	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map'     =>'variants/RatWars/resources/smallmap.png',
				'army'    =>'variants/RatWars/resources/smallarmy.png',
				'fleet'   =>'variants/RatWars/resources/smallfleet.png',
				'names'   =>'variants/RatWars/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map'     =>'variants/RatWars/resources/map.png',
				'army'    =>'contrib/army.png',
				'fleet'   =>'contrib/fleet.png',
				'names'   =>'variants/RatWars/resources/mapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
	}

}
