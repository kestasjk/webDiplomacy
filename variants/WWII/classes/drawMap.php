<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class WWIIVariant_drawMap extends drawMap {

	protected $countryColors = array(
		0 => array(226, 198, 158), // Neutral
		1 => array(121, 175, 198), // France
		2 => array(160, 138, 117), // Germany
		3 => array(239, 196, 228), // Britain
		4 => array(164, 196, 153), // Italy
		5 => array(190, 55, 45), // Soviet Russia
	);
	
	public function __construct($smallmap)
	{
		// Map is too big, so up the memory-limit
		parent::__construct($smallmap);
		if ( !$this->smallmap )
			ini_set('memory_limit',"29M");
	}

	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map'=>'variants/WWII/resources/smallmap.png',
				'army'=>'contrib/smallarmy.png',
				'fleet'=>'contrib/smallfleet.png',
				'names'=>'variants/WWII/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map'=>'variants/WWII/resources/map.png',
				'army'=>'contrib/army.png',
				'fleet'=>'contrib/fleet.png',
				'names'=>'variants/WWII/resources/mapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
	}
}

?>