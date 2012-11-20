<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class ClassicCataclysmVariant_drawMap extends drawMap {

	protected $terr_owner=array(); // An array to store the owner of each territory

	public function colorTerritory($terrID, $countryID)	
	{
		if (in_array($this->territoryNames[$terrID],$GLOBALS['Variants'][VARIANTID]->seaTerrs) && $countryID != 0)
			$this->terr_owner[$terrID]=$countryID;
		elseif (!in_array($this->territoryNames[$terrID],$GLOBALS['Variants'][VARIANTID]->seaTerrs))
			parent::colorTerritory($terrID,$countryID);
	}

	public function countryFlag($terrID, $countryID)
	{
		if (in_array($this->territoryNames[$terrID],$GLOBALS['Variants'][VARIANTID]->seaTerrs))
			$this->terr_owner[$terrID]=$countryID;
		else
			parent::countryFlag($terrID, $countryID);
	}
	
	public function addUnit($terrID, $unitType)
	{
		if (isset($this->terr_owner[$terrID]))
		{
			parent::countryFlag($terrID, $this->terr_owner[$terrID]);
			$unitType='Fleet';
		}
		parent::addUnit($terrID, $unitType);
	}
	
	/**
	 * An array of colors for different countries, indexed by countryID
	 * @var array
	 */
	protected $countryColors = array(
		0 => array(226, 198, 158),
		1 => array(239, 196, 228),
		2 => array(121, 175, 198),
		3 => array(164, 196, 153),
		4 => array(160, 138, 117),
		5 => array(196, 143, 133),
		6 => array(234, 234, 175),
		7 => array(168, 126, 159)
		);

	/**
	 * Resources, all required except names, which will be drawn on by the computer if not supplied.
	 * @return array[$resourceName]=$resourceLocation
	 */
	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map'=>'variants/ClassicCataclysm/resources/smallmap.png',
				'army'=>'contrib/smallarmy.png',
				'fleet'=>'contrib/smallfleet.png',
				'names'=>'variants/ClassicCataclysm/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map'=>'variants/ClassicCataclysm/resources/map.png',
				'army'=>'contrib/army.png',
				'fleet'=>'contrib/fleet.png',
				'names'=>'variants/ClassicCataclysm/resources/mapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
	}
}

?>