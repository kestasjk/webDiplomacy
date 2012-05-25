<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class NorthSeaWarsVariant_drawMap extends drawMap {

	protected $tradeCS=array('grains', 'wood', 'iron','Central North Sea');

	public function addUnit($terrName, $unitType)
	{
		parent::addUnit($terrName, $unitType);		
		// Add 2nd icons for the gateway:
		if (in_array($this->territoryNames[$terrName].' (2)' ,$this->territoryNames))
			parent::addUnit(array_search($this->territoryNames[$terrName].' (2)',$this->territoryNames), $unitType);			
	}

	public function drawDestroyedUnit($terrID)
	{
		parent::drawDestroyedUnit($terrID);
		// Add 2nd destroy icon for the gateway:
		if (in_array($this->territoryNames[$terrID].' (2)' ,$this->territoryNames))
			parent::drawDestroyedUnit(array_search($this->territoryNames[$terrID].' (2)',$this->territoryNames));			
	}
	
	public function countryFlag($terrName, $countryID)
	{
		parent::countryFlag($terrName, $countryID);		
		// Add 2nd icons for the gateway:
		if (in_array($this->territoryNames[$terrName].' (2)' ,$this->territoryNames))
			parent::countryFlag(array_search($this->territoryNames[$terrName].' (2)',$this->territoryNames),$countryID);			
	}

	// All order arrows needs adjustment for the underworld-map and for the warparound
	public function drawMove($fromTerrID, $toTerrID, $success)
	{
		list($from, $to)=$this->adjustArrows($fromTerrID,$toTerrID);
		parent::drawMove($from, $to, $success);
	}
	
	public function drawRetreat($fromTerrID, $toTerrID, $success)
	{
		list($from, $to)=$this->adjustArrows($fromTerrID,$toTerrID);
		parent::drawRetreat($from, $to, $success);
	}
	
	public function drawSupportHold($fromTerrID, $toTerrID, $success)
	{
		list($from, $to)=$this->adjustArrows($fromTerrID,$toTerrID);
		parent::drawSupportHold($from, $to, $success);
	}
	
	public function drawSupportMove($terrID, $fromTerrID, $toTerrID, $success)
	{
		list($from, $to)=$this->adjustArrows($fromTerrID,$toTerrID,$terrID);
		parent::drawSupportMove($terrID, $from, $to, $success);
	}
	
	private function adjustArrows($fromID, $toID, $terrID=0)
	{
		$fromName = $this->territoryNames[$fromID];
		$toName   = $this->territoryNames[$toID];
		if ($terrID > 0)
			$terrName = $this->territoryNames[$terrID];
			
		// Special case support-move: move is not drawn in the underworld, but supporting unit is in the underworld, and vice versa
		if ($terrID != 0)
		{	
			if (( in_array($terrName, $this->tradeCS) && !in_array($fromName, $this->tradeCS) && in_array($toName,$this->tradeCS)) ||
				(!in_array($terrName, $this->tradeCS) && in_array($fromName, $this->tradeCS) && in_array($toName,$this->tradeCS)))
			
			{
				if (in_array($terrName, $this->tradeCS))
				{
					$toName .= ' (2)';
					$toID_new=array_search($toName,$this->territoryNames);
					if ($toID_new != 0)
						$toID = $toID_new;
					else
						return array ($fromID, $toID);
				}
				return array ($toID,$toID);
			}		
		}
			
		// Adjust the fromTerrID and toTerrID for the extra underworld map
		if (in_array($fromName, $this->tradeCS) && in_array($toName, $this->tradeCS))
		{
			if (in_array($fromName.' (2)' ,$this->territoryNames))
			{
				$fromName .= ' (2)';
				$fromID=array_search($fromName,$this->territoryNames);
			}
			if (in_array($toName.' (2)' ,$this->territoryNames))
			{
				$toName .= ' (2)';
				$toID=array_search($toName,$this->territoryNames);
			}
		}
		return array ($fromID,$toID);
		
	}
	
	protected $countryColors = array(
		0  => array(226, 198, 158), /* Neutral   */
		1  => array(168, 126, 159), /* Briton */
		2  => array(164, 196, 153), /* Roman */
		3  => array(196, 143, 133), /* Frysian */
		4  => array(239, 196, 228), /* Norse */
	);

	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map'     =>'variants/NorthSeaWars/resources/smallmap.png',
				'army'    =>'variants/NorthSeaWars/resources/army.png',
				'fleet'   =>'variants/NorthSeaWars/resources/fleet.png',
				'names'   =>'variants/NorthSeaWars/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map'     =>'variants/NorthSeaWars/resources/map.png',
				'army'    =>'contrib/army.png',
				'fleet'   =>'contrib/fleet.png',
				'names'   =>'variants/NorthSeaWars/resources/mapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
	}

}

?>